<?php

namespace App\Services\Catalogue;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiStructuringService
{
    private const MODEL   = 'claude-sonnet-4-6';
    private const API_URL = 'https://api.anthropic.com/v1/messages';

    private string $systemPrompt = <<<'PROMPT'
You are an industrial equipment product data extractor for a vendor discovery platform.

Your job: Given raw content extracted from a vendor product catalogue (Excel, CSV, or PDF), output a structured JSON of all product variants found.

NORMALIZATION RULES — always apply:
- Power: any unit → power_kw (HP × 0.746 = kW; W ÷ 1000 = kW)
- Size in inches → size_inch; also compute size_mm (× 25.4)
- Size in mm → size_mm; also compute size_inch (÷ 25.4)
- "4 Pole" or "4P" → poles: 4
- RPM → rpm (integer)
- "IE4" / "IE3" / "IE2" → efficiency_class
- "IP55" / "IP65" etc → ip_rating
- Pressure in bar → pressure_bar; PSI × 0.0689 = bar
- Flow in m³/hr or m³/h → flow_m3h; LPM ÷ 16.667 = m³/h; GPM × 0.2271 = m³/h
- Frame size (132M, 160L, etc.) → frame_size
- Voltage in V → voltage_v

EQUIPMENT TYPE DETECTION (use exactly these strings):
Motor | Gate Valve | Globe Valve | Ball Valve | Butterfly Valve | Check Valve | Control Valve |
Angle Seat Valve | Solenoid Valve | Centrifugal Pump | Submersible Pump | Gear Pump | Diaphragm Pump |
Screw Compressor | Piston Compressor | Centrifugal Compressor | Rotary Vane Vacuum Pump |
Side Channel Blower | Pneumatic Cylinder | Pressure Transmitter | Flow Meter | Level Transmitter |
Temperature Transmitter | Other

CATEGORY DETECTION (use exactly these strings):
Motors | Valves | Pumps | Compressors | Vacuum Equipment | Blowers | Pneumatics | Instruments | Other

OUTPUT FORMAT — return ONLY valid JSON, no text before or after:
{
  "vendor_series": [
    {
      "series_name": "IE4 Premium Motors",
      "brand": "LHP",
      "category": "Motors",
      "variants": [
        {
          "variant_name": "7.5kW 4P 132M",
          "equipment_type": "Motor",
          "power_kw": 7.5,
          "size_inch": null,
          "size_mm": null,
          "pressure_bar": null,
          "flow_m3h": null,
          "voltage_v": 415,
          "poles": 4,
          "industry_tags": ["general_industry"],
          "capability_tags": ["IE4"],
          "certifications": [],
          "specifications": {
            "frame_size": "132M",
            "rpm": 1500,
            "efficiency_class": "IE4",
            "ip_rating": "IP55"
          }
        }
      ]
    }
  ]
}

INDUSTRY TAGS (pick all that apply): oil_gas, pharma, food_beverage, chemical, water_treatment, marine, power, general_industry, mining, cement, textile
CAPABILITY TAGS (pick all that apply): ATEX, flame_proof, IE4, IE3, IE2, hygienic, high_temp, cryogenic, explosion_proof, stainless, food_grade, corrosion_resistant

If a field is unknown or not provided, use null (not empty string).
Group variants by product series. One series = same model family / same spec sheet section.
PROMPT;

    public function structure(array $rawExtracted, string $vendorName = ''): array
    {
        $apiKey = config('services.anthropic.api_key');

        if (!$apiKey) {
            Log::warning('Anthropic API key not configured — returning raw extracted data as-is.');
            return $this->fallbackStructure($rawExtracted);
        }

        $content = $this->formatContentForAi($rawExtracted, $vendorName);

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(90)->post(self::API_URL, [
                'model'      => self::MODEL,
                'max_tokens' => 8192,
                'system'     => $this->systemPrompt,
                'messages'   => [
                    ['role' => 'user', 'content' => $content],
                ],
            ]);

            if (!$response->successful()) {
                Log::error('Anthropic API error', ['status' => $response->status(), 'body' => $response->body()]);
                return $this->fallbackStructure($rawExtracted);
            }

            $text = $response->json('content.0.text', '');
            return $this->parseAiResponse($text);

        } catch (\Throwable $e) {
            Log::error('AI structuring failed', ['error' => $e->getMessage()]);
            return $this->fallbackStructure($rawExtracted);
        }
    }

    private function formatContentForAi(array $rawExtracted, string $vendorName): string
    {
        $vendor = $vendorName ? "Vendor: $vendorName\n\n" : '';

        if (isset($rawExtracted['sheets'])) {
            // Excel/CSV
            $lines = $vendor . "Source: Excel/CSV Catalogue\n\n";
            foreach ($rawExtracted['sheets'] as $sheet) {
                $lines .= "=== Sheet: {$sheet['sheet_name']} ===\n";
                $lines .= "Headers: " . implode(' | ', $sheet['headers']) . "\n";
                foreach (array_slice($sheet['rows'], 0, 200) as $row) {
                    $lines .= implode(' | ', array_values($row)) . "\n";
                }
                $lines .= "\n";
            }
            return $lines;
        }

        if (isset($rawExtracted['full_text'])) {
            // PDF
            $text = $vendor . "Source: Digital PDF Catalogue\n\n";
            // Limit to first 12,000 chars to stay within token limits
            $text .= substr($rawExtracted['full_text'], 0, 12000);
            return $text;
        }

        return $vendor . json_encode($rawExtracted, JSON_PRETTY_PRINT);
    }

    private function parseAiResponse(string $text): array
    {
        // Strip any markdown code blocks
        $text = preg_replace('/^```(?:json)?\s*/m', '', $text);
        $text = preg_replace('/\s*```$/m', '', $text);
        $text = trim($text);

        $data = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['vendor_series'])) {
            Log::warning('AI returned invalid JSON', ['text' => substr($text, 0, 500)]);
            return ['vendor_series' => []];
        }

        return $data;
    }

    private function fallbackStructure(array $rawExtracted): array
    {
        // When AI is unavailable, return empty structure so vendor can fill manually
        return ['vendor_series' => []];
    }
}
