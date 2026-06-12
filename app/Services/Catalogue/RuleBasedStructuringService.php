<?php

namespace App\Services\Catalogue;

/**
 * Pure PHP rule-based extractor.
 * No API keys. No external services.
 * Maps raw Excel/CSV/PDF content → structured product variants.
 */
class RuleBasedStructuringService
{
    // ─── Header → field mapping ────────────────────────────────────────
    private const HEADER_MAP = [
        'power_kw'          => ['/\bpower\b|\bkw\b|kilowatt/i',         false],
        'power_hp'          => ['/\bhp\b|horse.?power/i',               false],
        'size_mm'           => ['/\bdn\b|nominal.bore|nominal.dia|\bnb\b|bore.mm|dia.mm|size.mm/i', false],
        'size_inch'         => ['/size.*inch|inch.*size|bore.*inch|\bsize.*"\b|size.*in\b/i', false],
        'size_auto'         => ['/\bsize\b|\bbore\b|\bdiameter\b|\bdn\b/i', false],
        'pressure_bar'      => ['/pressure.*bar|bar.*pressure|\bbar\b/i', false],
        'pressure_psi'      => ['/\bpsi\b/i',                            false],
        'pressure_auto'     => ['/\bpressure\b|working.*press|max.*press/i', false],
        'flow_m3h'          => ['/\bflow\b|\bfad\b|capacity|m3.h|m³.h|m3.hr|m³.hr/i', false],
        'flow_lpm'          => ['/\blpm\b|l\/min/i',                    false],
        'poles'             => ['/\bpoles?\b/i',                         false],
        'rpm'               => ['/\brpm\b|rated.*speed|speed.*rpm/i',   false],
        'frame_size'        => ['/\bframe\b/i',                          false],
        'voltage_v'         => ['/\bvoltage\b|\bvolts?\b|supply.*v\b/i', false],
        'efficiency_class'  => ['/ie.*class|efficiency.*class|\bie[234]\b/i', false],
        'ip_rating'         => ['/\bip.?rating\b|\bprotection.*class\b|ingress/i', false],
        'variant_name'      => ['/\bmodel\b|\bpart.?no\b|\bproduct.?no\b|\bvariant\b|\btype\b|\bdesignation\b/i', false],
        'series_name'       => ['/\bseries\b|\bproduct.*series\b|\brange\b/i', false],
        'valve_type'        => ['/valve.*type|type.*valve/i',            false],
        'body_material'     => ['/body.*mat|material.*body/i',           false],
        'end_connection'    => ['/end.*conn|connection.*type/i',         false],
        'pressure_class'    => ['/pressure.*class|class.*pressure|\brating\b/i', false],
        'head_m'            => ['/\bhead\b|tdh|total.*head/i',           false],
        'pump_type'         => ['/pump.*type|type.*pump/i',              false],
        'datasheet_url'     => ['/datasheet|pdf.*link|document.*link/i', false],
    ];

    // ─── Public entry point ────────────────────────────────────────────
    public function structure(array $rawExtracted, string $vendorName = ''): array
    {
        if (isset($rawExtracted['sheets'])) {
            return $this->structureTabular($rawExtracted['sheets'], $vendorName);
        }

        if (isset($rawExtracted['full_text'])) {
            return $this->structurePdf($rawExtracted, $vendorName);
        }

        return ['vendor_series' => []];
    }

    // ─── Tabular (Excel / CSV) ─────────────────────────────────────────
    private function structureTabular(array $sheets, string $vendorName): array
    {
        $series = [];

        foreach ($sheets as $sheet) {
            $headers = array_values($sheet['headers'] ?? []);
            $rows    = $sheet['rows'] ?? [];

            if (empty($headers) || empty($rows)) continue;

            // Map each header to a standard field
            $fieldMap = $this->mapHeaders($headers);

            // Detect category & equipment type from all sheet content
            $allText  = $this->sheetText($headers, $rows, $sheet['sheet_name'] ?? '');
            $category = $this->detectCategory($allText);
            $eqType   = $this->detectEquipmentType($category, $allText);

            // Determine series name
            $seriesName = $this->extractSeriesName($fieldMap, $rows, $sheet['sheet_name'] ?? '', $vendorName);
            $brand      = $this->extractBrand($allText, $vendorName);

            // Extract tags once per sheet
            $tags = $this->detectTags($allText);

            // Build variants
            $variants = [];
            foreach ($rows as $row) {
                $v = $this->extractVariant($row, $fieldMap, $headers, $category, $eqType, $tags);
                if ($v) $variants[] = $v;
            }

            if (empty($variants)) continue;

            $series[] = [
                'series_name' => $seriesName,
                'brand'       => $brand,
                'category'    => $category,
                'variants'    => $variants,
            ];
        }

        return ['vendor_series' => $series];
    }

    private function extractVariant(
        array  $row,
        array  $fieldMap,
        array  $headers,
        string $category,
        string $eqType,
        array  $tags
    ): ?array {
        $mapped = [];

        // Map each column value to its standard field
        foreach ($headers as $idx => $header) {
            $colKeys  = array_keys($row);
            $rawValue = isset($colKeys[$idx]) ? (string)($row[$colKeys[$idx]] ?? '') : '';
            if (trim($rawValue) === '') continue;

            $field = $fieldMap[$idx] ?? null;
            if (!$field) {
                // Store in specs by original header name if not mapped
                $mapped['specs'][$header] = $rawValue;
                continue;
            }

            $parsed = $this->parseValue(trim($rawValue), $field);
            if ($parsed !== null) {
                $mapped[$field] = $parsed;
            }
        }

        // Skip rows with no useful data at all
        $matchFields = ['power_kw', 'size_inch', 'size_mm', 'pressure_bar', 'flow_m3h', 'poles', 'variant_name'];
        if (count(array_intersect(array_keys($mapped), $matchFields)) === 0) return null;

        // Build spec block
        $specs = $mapped['specs'] ?? [];
        foreach (['rpm', 'frame_size', 'efficiency_class', 'ip_rating', 'valve_type', 'body_material',
                  'end_connection', 'pressure_class', 'head_m', 'pump_type'] as $sf) {
            if (isset($mapped[$sf])) $specs[$sf] = $mapped[$sf];
        }

        // Infer size_mm ↔ size_inch if only one provided
        if (isset($mapped['size_mm']) && !isset($mapped['size_inch'])) {
            $mapped['size_inch'] = round($mapped['size_mm'] / 25.4, 2);
        }
        if (isset($mapped['size_inch']) && !isset($mapped['size_mm'])) {
            $mapped['size_mm'] = round($mapped['size_inch'] * 25.4, 1);
        }

        // Variant name: explicit or auto-built
        $variantName = $mapped['variant_name']
            ?? $this->buildVariantName($mapped, $category);

        if (!$variantName) return null;

        // Merge row-level tags with sheet-level tags
        $rowText = strtolower(implode(' ', $mapped['specs'] ?? []));
        $rowTags = $this->detectTags($rowText);
        $capTags = array_values(array_unique(array_merge($tags['capability_tags'], $rowTags['capability_tags'])));
        $indTags = array_values(array_unique(array_merge($tags['industry_tags'],   $rowTags['industry_tags'])));

        return [
            'variant_name'    => $variantName,
            'equipment_type'  => $eqType,
            'power_kw'        => $mapped['power_kw']    ?? null,
            'size_inch'       => $mapped['size_inch']   ?? null,
            'size_mm'         => $mapped['size_mm']     ?? null,
            'pressure_bar'    => $mapped['pressure_bar'] ?? null,
            'flow_m3h'        => $mapped['flow_m3h']    ?? null,
            'voltage_v'       => $mapped['voltage_v']   ?? null,
            'poles'           => $mapped['poles']       ?? null,
            'industry_tags'   => $indTags,
            'capability_tags' => $capTags,
            'certifications'  => [],
            'specifications'  => $specs,
        ];
    }

    // ─── PDF (PyMuPDF + pdfplumber output) ────────────────────────────
    private function structurePdf(array $rawExtracted, string $vendorName): array
    {
        $fullText = $rawExtracted['full_text'] ?? '';
        $category = $rawExtracted['detected_category']
            ?? $this->detectCategory(strtolower($fullText));
        $eqType   = $this->detectEquipmentType($category, strtolower($fullText));
        $brand    = $this->extractBrand($fullText, $vendorName);
        $tags     = $this->detectTags($fullText);

        $allVariants = [];

        // ── Primary path: use pre-mapped variants from Python extractor ──
        foreach ($rawExtracted['tables'] ?? [] as $table) {
            foreach ($table['variants'] ?? [] as $pv) {
                $v = $this->normalizePythonVariant($pv, $eqType, $tags);
                if ($v) $allVariants[] = $v;
            }
        }

        // ── Fallback: regex over full text if no table variants found ──
        if (empty($allVariants) && $fullText) {
            $sections = $this->splitPdfSections($fullText);
            foreach ($sections as $section) {
                $allVariants = array_merge(
                    $allVariants,
                    $this->extractPdfVariants($section, $category, $eqType, $tags)
                );
            }
            if (empty($allVariants)) {
                $allVariants = $this->extractPdfVariants($fullText, $category, $eqType, $tags);
            }
        }

        if (empty($allVariants)) return ['vendor_series' => []];

        $seriesName = $this->guessPdfSeriesName($fullText, $vendorName);

        return [
            'vendor_series' => [[
                'series_name' => $seriesName,
                'brand'       => $brand,
                'category'    => $category,
                'variants'    => $allVariants,
            ]],
        ];
    }

    private function normalizePythonVariant(array $pv, string $eqType, array $tags): ?array
    {
        // Python already mapped and typed the fields; just plug into our schema
        $variantName = $pv['variant_name'] ?? null;
        if (!$variantName) return null;

        // Infer size cross-fields
        $sizeMm    = isset($pv['size_mm'])    ? (float)$pv['size_mm']    : null;
        $sizeInch  = isset($pv['size_inch'])  ? (float)$pv['size_inch']  : null;
        if ($sizeMm && !$sizeInch)  $sizeInch = round($sizeMm  / 25.4, 2);
        if ($sizeInch && !$sizeMm)  $sizeMm   = round($sizeInch * 25.4, 1);

        $specKeys = ['frame_size','rpm','efficiency_class','ip_rating','valve_type',
                     'body_material','trim_material','end_connection','pressure_class',
                     'head_m','pump_type','current_a'];
        $specs = $pv['specifications'] ?? [];
        foreach ($specKeys as $k) {
            if (isset($pv[$k])) $specs[$k] = $pv[$k];
        }

        $rowTags = $this->detectTags(implode(' ', array_map('strval', $pv)));
        return [
            'variant_name'    => $variantName,
            'equipment_type'  => $eqType,
            'power_kw'        => isset($pv['power_kw'])     ? (float)$pv['power_kw']     : null,
            'size_inch'       => $sizeInch,
            'size_mm'         => $sizeMm,
            'pressure_bar'    => isset($pv['pressure_bar']) ? (float)$pv['pressure_bar'] : null,
            'flow_m3h'        => isset($pv['flow_m3h'])     ? (float)$pv['flow_m3h']     : null,
            'voltage_v'       => isset($pv['voltage_v'])    ? (int)$pv['voltage_v']      : null,
            'poles'           => isset($pv['poles'])        ? (int)$pv['poles']          : null,
            'industry_tags'   => array_values(array_unique(array_merge($tags['industry_tags'],   $rowTags['industry_tags']))),
            'capability_tags' => array_values(array_unique(array_merge($tags['capability_tags'], $rowTags['capability_tags']))),
            'certifications'  => [],
            'specifications'  => $specs,
        ];
    }

    private function splitPdfSections(string $text): array
    {
        // Split on lines that look like section headers (ALL CAPS or short bold-like lines)
        $parts = preg_split('/\n(?=[A-Z][A-Z\s]{4,}[\n:]|\n\n)/', $text);
        return array_filter(array_map('trim', $parts), fn($p) => strlen($p) > 30);
    }

    private function extractPdfVariants(string $text, string $category, string $eqType, array $tags): array
    {
        $variants  = [];

        // Extract all numeric values with units from the section
        $powerValues    = $this->regexExtractAll('/(\d+(?:\.\d+)?)\s*(?:kW|KW|kw)/i', $text);
        $hpValues       = $this->regexExtractAll('/(\d+(?:\.\d+)?)\s*(?:HP|hp|H\.P)/i', $text);
        $dnValues       = $this->regexExtractAll('/DN\s*(\d+(?:\.\d+)?)/i', $text);
        $inchValues     = $this->regexExtractAll('/(\d+(?:\.\d+)?)\s*(?:inch|Inch|"|\'\')\b/i', $text);
        $poleValues     = $this->regexExtractAll('/(\d+)\s*(?:Pole|pole|P\b)/i', $text);
        $frameValues    = $this->regexExtractAll('/Frame\s*(\d+[A-Za-z]*)/i', $text);
        $pressureValues = $this->regexExtractAll('/(\d+(?:\.\d+)?)\s*(?:bar|Bar|BAR)\b/i', $text);
        $flowValues     = $this->regexExtractAll('/(\d+(?:\.\d+)?)\s*(?:m3\/h|m³\/h|m3\/hr|m³\/hr)/i', $text);

        // Convert HP to kW
        $allKw = array_merge($powerValues, array_map(fn($hp) => round($hp * 0.746, 2), $hpValues));

        // Build variants from parallel arrays
        $count = max(count($allKw), count($dnValues), count($inchValues), 1);

        for ($i = 0; $i < $count; $i++) {
            $kw       = $allKw[$i]        ?? null;
            $dn       = $dnValues[$i]     ?? null;
            $inch     = $inchValues[$i]   ?? null;
            $pole     = $poleValues[$i]   ?? $poleValues[0] ?? null;
            $frame    = $frameValues[$i]  ?? null;
            $pressure = $pressureValues[$i] ?? null;
            $flow     = $flowValues[$i]   ?? null;

            // Infer size conversions
            if ($dn && !$inch) $inch = round($dn / 25.4, 2);
            if ($inch && !$dn) $dn   = round($inch * 25.4, 1);

            // Skip rows with nothing useful
            if (!$kw && !$dn && !$inch && !$pressure) continue;

            $specs = [];
            if ($frame) $specs['frame_size'] = $frame;

            $variantName = $this->buildVariantName([
                'power_kw'  => $kw,
                'poles'     => $pole,
                'frame_size' => $frame,
                'size_inch' => $inch,
                'size_mm'   => $dn,
                'pressure_bar' => $pressure,
                'flow_m3h'  => $flow,
            ], $category);

            $variants[] = [
                'variant_name'    => $variantName,
                'equipment_type'  => $eqType,
                'power_kw'        => $kw,
                'size_inch'       => $inch,
                'size_mm'         => $dn,
                'pressure_bar'    => $pressure,
                'flow_m3h'        => $flow,
                'voltage_v'       => null,
                'poles'           => $pole ? (int)$pole : null,
                'industry_tags'   => $tags['industry_tags'],
                'capability_tags' => $tags['capability_tags'],
                'certifications'  => [],
                'specifications'  => $specs,
            ];
        }

        return $variants;
    }

    // ─── Header mapping ────────────────────────────────────────────────
    private function mapHeaders(array $headers): array
    {
        $map = [];
        foreach ($headers as $idx => $header) {
            $map[$idx] = $this->resolveHeaderField((string)$header);
        }
        return $map;
    }

    private function resolveHeaderField(string $header): ?string
    {
        $h = strtolower(trim($header));

        // Variant name (check first to avoid overlap)
        if (preg_match('/\bmodel\b|\bpart.?no\b|\bitem.?no\b|\bvariant\b|\bdesignation\b/i', $h)) return 'variant_name';
        if (preg_match('/\bseries\b/i', $h)) return 'series_name';

        // Power
        if (preg_match('/horse.?power|\bhp\b/i', $h)) return 'power_hp';
        if (preg_match('/\bkw\b|kilowatt|\bpower\b/i', $h) && !preg_match('/hp/i', $h)) return 'power_kw';

        // Size
        if (preg_match('/\bdn\b|nominal.bore|nominal.dia|\bnb\b/i', $h)) return 'size_mm';
        if (preg_match('/inch|size.*in\b/i', $h)) return 'size_inch';
        if (preg_match('/size.mm|bore.mm|dia.mm/i', $h)) return 'size_mm';
        if (preg_match('/\bsize\b|\bbore\b|\bdiameter\b/i', $h)) return 'size_auto';

        // Pressure
        if (preg_match('/\bpsi\b/i', $h)) return 'pressure_psi';
        if (preg_match('/\bbar\b/i', $h)) return 'pressure_bar';
        if (preg_match('/\bpressure\b/i', $h)) return 'pressure_auto';

        // Flow
        if (preg_match('/\blpm\b|l\/min/i', $h)) return 'flow_lpm';
        if (preg_match('/\bfad\b|\bflow\b|m3|m³/i', $h)) return 'flow_m3h';

        // Motor fields
        if (preg_match('/\bpoles?\b/i', $h)) return 'poles';
        if (preg_match('/\brpm\b|rated.*speed/i', $h)) return 'rpm';
        if (preg_match('/\bframe\b/i', $h)) return 'frame_size';
        if (preg_match('/\bvoltage\b|\bvolts?\b/i', $h)) return 'voltage_v';
        if (preg_match('/ie.*class|efficiency.*class/i', $h)) return 'efficiency_class';
        if (preg_match('/\bip.?rating\b|protection.*class/i', $h)) return 'ip_rating';

        // Valve fields
        if (preg_match('/valve.*type/i', $h)) return 'valve_type';
        if (preg_match('/body.*mat/i', $h)) return 'body_material';
        if (preg_match('/end.*conn|connection/i', $h)) return 'end_connection';
        if (preg_match('/pressure.*class|class.*pressure/i', $h)) return 'pressure_class';

        // Pump fields
        if (preg_match('/\bhead\b|tdh/i', $h)) return 'head_m';
        if (preg_match('/pump.*type/i', $h)) return 'pump_type';

        // Datasheet
        if (preg_match('/datasheet|pdf.*link|doc.*link/i', $h)) return 'datasheet_url';

        return null;
    }

    // ─── Value parsing & unit conversion ──────────────────────────────
    private function parseValue(string $raw, string $field): mixed
    {
        $v = trim($raw);
        if ($v === '' || strtolower($v) === 'n/a' || strtolower($v) === '-') return null;

        return match($field) {
            'power_kw'       => $this->parseFloat($v),
            'power_hp'       => ($hp = $this->parseFloat($v)) ? round($hp * 0.746, 2) : null,
            'size_mm'        => $this->parseSizeMm($v),
            'size_inch'      => $this->parseSizeInch($v),
            'size_auto'      => $this->parseSizeAuto($v),
            'pressure_bar'   => $this->parseFloat($v),
            'pressure_psi'   => ($psi = $this->parseFloat($v)) ? round($psi * 0.0689476, 2) : null,
            'pressure_auto'  => $this->parsePressureAuto($v),
            'flow_m3h'       => $this->parseFloat($v),
            'flow_lpm'       => ($lpm = $this->parseFloat($v)) ? round($lpm / 16.667, 3) : null,
            'poles'          => $this->parsePoles($v),
            'rpm', 'voltage_v' => $this->parseInt($v),
            'frame_size', 'efficiency_class', 'ip_rating',
            'valve_type', 'body_material', 'end_connection',
            'pressure_class', 'pump_type', 'variant_name',
            'series_name', 'datasheet_url' => $v,
            'head_m'         => $this->parseFloat($v),
            default          => $v,
        };
    }

    private function parseSizeMm(string $v): ?float
    {
        // Handle "DN 300", "300 mm", "Ø300"
        $clean = preg_replace('/DN\s*|Ø|mm|\s/i', '', $v);
        $n = $this->parseFloat($clean);
        // If value looks like inches (small number), convert
        if ($n && $n <= 60) return round($n * 25.4, 1); // likely inches
        return $n;
    }

    private function parseSizeInch(string $v): ?float
    {
        $clean = preg_replace('/["\']|inches?|in\b|\s/i', '', $v);
        return $this->parseFloat($clean);
    }

    private function parseSizeAuto(string $v): mixed
    {
        // Try to detect unit from value string
        if (preg_match('/DN|mm|\bΦ/i', $v)) return ['field' => 'size_mm', 'value' => $this->parseSizeMm($v)];
        if (preg_match('/inch|"|\'/i', $v)) return ['field' => 'size_inch', 'value' => $this->parseSizeInch($v)];
        // Bare number: if > 25, likely mm; if <= 24, likely inch
        $n = $this->parseFloat(preg_replace('/[^\d.]/', '', $v));
        if (!$n) return null;
        return $n > 25 ? ['field' => 'size_mm', 'value' => $n] : ['field' => 'size_inch', 'value' => $n];
    }

    private function parsePressureAuto(string $v): ?float
    {
        if (preg_match('/(\d+(?:\.\d+)?)\s*psi/i', $v, $m)) return round((float)$m[1] * 0.0689476, 2);
        if (preg_match('/(\d+(?:\.\d+)?)\s*kg/i',  $v, $m)) return round((float)$m[1] * 0.0981, 2); // kgf/cm² → bar
        return $this->parseFloat($v);
    }

    private function parsePoles(string $v): ?int
    {
        // "4P", "4 Pole", "4 Poles", "4-pole", bare "4"
        if (preg_match('/(\d+)\s*[Pp](?:ole)?s?\b/', $v, $m)) return (int)$m[1];
        $n = $this->parseInt($v);
        // Only valid pole counts
        return in_array($n, [2, 4, 6, 8, 10, 12]) ? $n : null;
    }

    private function parseFloat(string $v): ?float
    {
        // Take first number from strings like "7.5 kW"
        if (preg_match('/(\d+(?:\.\d+)?)/', preg_replace('/,(?=\d{3})/', '', $v), $m)) {
            return (float)$m[1];
        }
        return null;
    }

    private function parseInt(string $v): ?int
    {
        if (preg_match('/(\d+)/', $v, $m)) return (int)$m[1];
        return null;
    }

    // ─── Category & equipment type detection ──────────────────────────
    private function detectCategory(string $text): string
    {
        $t = strtolower($text);
        if (preg_match('/\b(ie[234]|flame.?proof|atex.*motor|motor.*kw)\b/', $t))  return 'Motors';
        if (preg_match('/\bmotors?\b/', $t) && preg_match('/\b(kw|pole|rpm|frame)\b/', $t)) return 'Motors';
        if (preg_match('/\b(gate|globe|ball|butterfly|check|solenoid|angle.seat)\s*valves?\b/', $t)) return 'Valves';
        if (preg_match('/\bvalves?\b/', $t)) return 'Valves';
        if (preg_match('/\b(centrifugal|submersible|gear|diaphragm)\s*pumps?\b/', $t)) return 'Pumps';
        if (preg_match('/\bpumps?\b/', $t) && preg_match('/\b(head|flow|m3)\b/', $t)) return 'Pumps';
        if (preg_match('/\b(screw|piston|centrifugal)\s*compressors?\b|\bfad\b/', $t)) return 'Compressors';
        if (preg_match('/\bcompressors?\b/', $t)) return 'Compressors';
        if (preg_match('/\bvacuum\b|\brotary.*vane\b|\bliquid.*ring\b/', $t)) return 'Vacuum Equipment';
        if (preg_match('/\bblowers?\b|\bside.*channel\b|\broots\b/', $t)) return 'Blowers';
        if (preg_match('/\b(pneumatic|cylinders?|actuators?)\b/', $t) && preg_match('/\b(bore|stroke|bar)\b/', $t)) return 'Pneumatics';
        if (preg_match('/\b(transmitters?|flow.*meters?|level.*sensor|pressure.*gauge)\b/', $t)) return 'Instruments';
        return 'Other';
    }

    private function detectEquipmentType(string $category, string $text): string
    {
        $t = strtolower($text);
        return match($category) {
            'Motors' => match(true) {
                (bool)preg_match('/flame.?proof|flameproof|ex[\s-]?[de]|atex/i', $t) => 'Flame-proof Motor',
                (bool)preg_match('/servo/i', $t) => 'Servo Motor',
                default => 'Motor',
            },
            'Valves' => match(true) {
                (bool)preg_match('/gate/i', $t)          => 'Gate Valve',
                (bool)preg_match('/globe/i', $t)         => 'Globe Valve',
                (bool)preg_match('/ball/i', $t)          => 'Ball Valve',
                (bool)preg_match('/butterfly/i', $t)     => 'Butterfly Valve',
                (bool)preg_match('/check/i', $t)         => 'Check Valve',
                (bool)preg_match('/solenoid/i', $t)      => 'Solenoid Valve',
                (bool)preg_match('/angle.*seat/i', $t)   => 'Angle Seat Valve',
                (bool)preg_match('/control/i', $t)       => 'Control Valve',
                default => 'Valve',
            },
            'Pumps' => match(true) {
                (bool)preg_match('/centrifugal/i', $t)   => 'Centrifugal Pump',
                (bool)preg_match('/submersible/i', $t)   => 'Submersible Pump',
                (bool)preg_match('/gear/i', $t)          => 'Gear Pump',
                (bool)preg_match('/diaphragm/i', $t)     => 'Diaphragm Pump',
                default => 'Pump',
            },
            'Compressors' => match(true) {
                (bool)preg_match('/screw/i', $t)          => 'Screw Compressor',
                (bool)preg_match('/piston|reciprocating/i', $t) => 'Piston Compressor',
                (bool)preg_match('/centrifugal/i', $t)    => 'Centrifugal Compressor',
                default => 'Compressor',
            },
            'Vacuum Equipment' => match(true) {
                (bool)preg_match('/rotary.*vane|vane/i', $t) => 'Rotary Vane Vacuum Pump',
                (bool)preg_match('/liquid.*ring/i', $t)      => 'Liquid Ring Vacuum Pump',
                (bool)preg_match('/dry.*screw/i', $t)        => 'Dry Screw Vacuum Pump',
                default => 'Vacuum Pump',
            },
            'Blowers' => match(true) {
                (bool)preg_match('/side.*channel/i', $t) => 'Side Channel Blower',
                (bool)preg_match('/roots/i', $t)         => 'Roots Blower',
                default => 'Blower',
            },
            'Pneumatics' => match(true) {
                (bool)preg_match('/cylinder/i', $t) => 'Pneumatic Cylinder',
                default => 'Pneumatic Component',
            },
            'Instruments' => match(true) {
                (bool)preg_match('/pressure.*transmitter/i', $t) => 'Pressure Transmitter',
                (bool)preg_match('/flow.*meter/i', $t)            => 'Flow Meter',
                (bool)preg_match('/level/i', $t)                  => 'Level Transmitter',
                (bool)preg_match('/temperature/i', $t)            => 'Temperature Transmitter',
                default => 'Instrument',
            },
            default => 'Equipment',
        };
    }

    // ─── Tag detection ─────────────────────────────────────────────────
    private function detectTags(string $text): array
    {
        $cap = [];
        $ind = [];

        if (preg_match('/\batex\b|ex[-\s]?[de]|iecex|flameproof|flame.proof/i', $text)) {
            $cap[] = 'ATEX'; $cap[] = 'flame_proof';
        }
        if (preg_match('/\bie4\b/i',   $text)) $cap[] = 'IE4';
        if (preg_match('/\bie3\b/i',   $text)) $cap[] = 'IE3';
        if (preg_match('/\bie2\b/i',   $text)) $cap[] = 'IE2';
        if (preg_match('/stainless|ss316|ss304/i', $text)) $cap[] = 'stainless';
        if (preg_match('/hygienic|sanitary|food.grade/i', $text)) { $cap[] = 'hygienic'; $cap[] = 'food_grade'; }
        if (preg_match('/high.temp/i', $text))  $cap[] = 'high_temp';
        if (preg_match('/explosion.proof/i', $text)) { $cap[] = 'explosion_proof'; $cap[] = 'ATEX'; }
        if (preg_match('/ip66|ip65|weatherproof/i', $text)) $cap[] = 'weatherproof';
        if (preg_match('/corrosion.resistant|hastelloy|duplex/i', $text)) $cap[] = 'corrosion_resistant';

        if (preg_match('/oil.*gas|petroleum|refinery|offshore/i', $text)) $ind[] = 'oil_gas';
        if (preg_match('/pharma|pharmaceutical|gmp|fda/i', $text)) $ind[] = 'pharma';
        if (preg_match('/food|beverage|dairy/i', $text)) $ind[] = 'food_beverage';
        if (preg_match('/chemical|acid|alkali|solvent/i', $text)) $ind[] = 'chemical';
        if (preg_match('/water.*treatment|sewage|wastewater/i', $text)) $ind[] = 'water_treatment';
        if (preg_match('/marine|ship|offshore|vessel/i', $text)) $ind[] = 'marine';
        if (preg_match('/power.*plant|power.*station/i', $text)) $ind[] = 'power';
        if (preg_match('/\bmining\b|\bquarry\b/i', $text)) $ind[] = 'mining';

        return [
            'capability_tags' => array_values(array_unique($cap)),
            'industry_tags'   => array_values(array_unique($ind)),
        ];
    }

    // ─── Helpers ──────────────────────────────────────────────────────
    private function buildVariantName(array $mapped, string $category): string
    {
        $parts = [];
        if (!empty($mapped['power_kw']))      $parts[] = $mapped['power_kw'] . 'kW';
        if (!empty($mapped['poles']))         $parts[] = $mapped['poles'] . 'P';
        if (!empty($mapped['frame_size']))    $parts[] = $mapped['frame_size'];
        if (!empty($mapped['size_mm']))       $parts[] = 'DN' . $mapped['size_mm'];
        elseif (!empty($mapped['size_inch'])) $parts[] = $mapped['size_inch'] . '"';
        if (!empty($mapped['pressure_bar']))  $parts[] = $mapped['pressure_bar'] . 'bar';
        if (!empty($mapped['pressure_class'])) $parts[] = $mapped['pressure_class'];
        if (!empty($mapped['flow_m3h']))      $parts[] = $mapped['flow_m3h'] . 'm³/h';
        return implode(' ', $parts) ?: 'Variant';
    }

    private function extractSeriesName(array $fieldMap, array $rows, string $sheetName, string $vendorName): string
    {
        // Look for a "series" column
        $seriesIdx = array_search('series_name', $fieldMap);
        if ($seriesIdx !== false) {
            foreach ($rows as $row) {
                $vals = array_values($row);
                $v    = trim((string)($vals[$seriesIdx] ?? ''));
                if ($v) return $v;
            }
        }
        // Fallback to sheet name, then vendor name + generic
        if ($sheetName && strtolower($sheetName) !== 'sheet1' && strtolower($sheetName) !== 'data') {
            return $sheetName;
        }
        return $vendorName ? "$vendorName Products" : 'Product Series';
    }

    private function extractBrand(string $text, string $vendorName): string
    {
        // Look for known brand patterns
        $brands = ['CompAir', 'Gardner Denver', 'Elmo Rietschle', 'Robuschi', 'Janatics', 'LHP', 'ABB', 'Siemens', 'WEG', 'Kirloskar', 'KSB', 'Grundfos'];
        foreach ($brands as $brand) {
            if (stripos($text, $brand) !== false) return $brand;
        }
        return $vendorName ?: '';
    }

    private function guessPdfSeriesName(string $text, string $vendorName): string
    {
        // Take first short line that looks like a product heading
        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            $line = trim($line);
            if (strlen($line) > 5 && strlen($line) < 60 && preg_match('/[A-Z]/', $line) && !preg_match('/^\d/', $line)) {
                return $line;
            }
        }
        return $vendorName ? "$vendorName Series" : 'Product Series';
    }

    private function sheetText(array $headers, array $rows, string $sheetName = ''): string
    {
        return strtolower(
            $sheetName . ' ' .
            implode(' ', $headers) . ' ' .
            implode(' ', array_map(fn($r) => implode(' ', array_values($r)), array_slice($rows, 0, 10)))
        );
    }

    private function regexExtractAll(string $pattern, string $text): array
    {
        preg_match_all($pattern, $text, $matches);
        return array_map('floatval', $matches[1] ?? []);
    }
}
