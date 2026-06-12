<?php

namespace App\Services\Catalogue;

/**
 * Maps a raw scraped specification table (label => value) onto the canonical
 * per-category predefined fields (config/category_fields.php).
 *
 * Deterministic / rule-based only — NO AI. Anything that doesn't map to a
 * known field is preserved under "extra" for admin review.
 *
 * Result: flat JSON-friendly array of canonical field keys (matching the
 * config) + an "extra" bucket of unmapped labels — ready for products.specifications.
 */
class CategorySpecMapper
{
    /** Scraped-label aliases → canonical single-value field, per category. */
    private const ALIASES = [
        'Motors' => [
            'synchronous_rpm'   => ['synchronous rpm', 'rpm', 'speed', 'synchronous speed', 'rated speed'],
            'mounting_type'     => ['mounting', 'mounting type', 'construction', 'mounting arrangement'],
            'frame_size'        => ['frame', 'frame size'],
            'protection_rating' => ['protection', 'ip rating', 'degree of protection', 'enclosure', 'protection class', 'ip'],
            'insulation_class'  => ['insulation', 'insulation class', 'class of insulation'],
            'voltage'           => ['voltage', 'rated voltage', 'supply voltage'],
            'frequency'         => ['frequency', 'freq', 'hz'],
            'duty_type'         => ['duty', 'duty type', 'duty cycle'],
            'phase'             => ['phase', 'phases', 'no of phase', 'number of phases'],
            'efficiency_class'  => ['efficiency class', 'efficiency', 'ie class', 'ie'],
            'cooling_type'      => ['cooling', 'cooling type', 'method of cooling'],
        ],
        'Pumps' => [
            'pump_type'         => ['pump type', 'type'],
            'power_kw'          => ['power', 'motor rating', 'kw'],
            'rpm'               => ['rpm', 'speed'],
            'efficiency'        => ['efficiency'],
            'suction_size'      => ['suction', 'suction size', 'suction dia'],
            'discharge_size'    => ['discharge size', 'delivery size'],
            'casing_material'   => ['casing material', 'casing'],
            'impeller_material' => ['impeller material', 'impeller'],
            'seal_type'         => ['seal', 'seal type', 'sealing'],
            'temperature_range' => ['temperature', 'temp', 'temperature range', 'liquid temperature'],
        ],
        'Valves' => [
            'certifications'          => ['certifications', 'certification', 'certificates', 'approvals'],
            'critical_service'        => ['critical service', 'service', 'service type'],
            'flow_characteristics'    => ['flow characteristics', 'flow characteristic', 'characteristic', 'flow'],
            'material'                => ['material', 'materials', 'body material', 'construction material'],
            'operating_temperature'   => ['operating temperature', 'temperature', 'temperature range', 'temp', 'service temperature'],
            'pressure_class'          => ['pressure class', 'class', 'rating', 'pressure rating', 'pn'],
            'process_connection_type' => ['process connection type', 'process connection', 'connection type', 'connection', 'end connection', 'ends'],
            'shutoff_class'           => ['shutoff class', 'shut off class', 'shut-off class', 'leakage class', 'seat leakage'],
            'valve_size'              => ['valve size', 'size', 'nominal size', 'dn', 'bore', 'line size'],
            'valve_size_standard'     => ['valve size standard', 'size standard', 'sizing standard'],
        ],
        'Blowers' => [
            'blower_type'       => ['blower type', 'type'],
            'air_flow_rate'     => ['air flow rate', 'air flow', 'flow', 'capacity', 'volume'],
            'static_pressure'   => ['static pressure', 'pressure', 'head'],
            'power_kw'          => ['power', 'motor rating', 'kw'],
            'rpm'               => ['rpm', 'speed'],
            'efficiency'        => ['efficiency'],
            'impeller_type'     => ['impeller type', 'impeller'],
            'motor_type'        => ['motor type', 'motor', 'drive'],
            'voltage'           => ['voltage'],
            'frequency'         => ['frequency', 'hz'],
            'noise_level'       => ['noise', 'noise level', 'sound level', 'db'],
            'temperature_range' => ['temperature', 'temp', 'temperature range'],
        ],
        'Compressors' => [
            'compressor_type'       => ['compressor type', 'type'],
            'flow_rate'             => ['flow rate', 'flow', 'fad', 'capacity', 'free air delivery'],
            'pressure_rating'       => ['pressure rating', 'pressure', 'working pressure', 'max pressure', 'discharge pressure'],
            'power_kw'              => ['power', 'motor rating', 'kw'],
            'rpm'                   => ['rpm', 'speed'],
            'voltage'               => ['voltage'],
            'frequency'             => ['frequency', 'hz'],
            'cooling_method'        => ['cooling', 'cooling method', 'cooling type'],
            'lubrication_type'      => ['lubrication', 'lubrication type', 'oil'],
            'discharge_temperature' => ['discharge temperature', 'discharge temp'],
            'air_receiver_capacity' => ['air receiver', 'receiver capacity', 'tank capacity', 'receiver'],
            'efficiency'            => ['efficiency'],
        ],
        'Pressure Transmitters' => [
            'measurement_unit'             => ['measurement unit', 'unit'],
            'accuracy'                     => ['accuracy'],
            'turndown_ratio'               => ['turndown', 'turndown ratio'],
            'output_signal'                => ['output signal', 'output', 'signal'],
            'communication_protocol'       => ['communication protocol', 'communication', 'protocol'],
            'process_connection'           => ['process connection', 'connection'],
            'wetted_material'              => ['wetted material', 'wetted parts'],
            'diaphragm_material'           => ['diaphragm material', 'diaphragm'],
            'power_supply'                 => ['power supply', 'supply', 'power'],
            'enclosure_rating'             => ['enclosure rating', 'enclosure', 'ip', 'protection'],
            'hazardous_area_certification' => ['hazardous area certification', 'hazardous', 'certification', 'area classification', 'approval', 'ex'],
            'response_time'                => ['response time', 'response'],
            'operating_temperature'        => ['operating temperature', 'temperature', 'ambient temperature', 'temp'],
        ],
        'Pressure Gauges' => [
            'measurement_unit'      => ['measurement unit', 'unit'],
            'dial_size'             => ['dial size', 'dial'],
            'connection_size'       => ['connection size'],
            'connection_type'       => ['connection type', 'connection'],
            'case_material'         => ['case material', 'case'],
            'wetted_material'       => ['wetted material', 'wetted parts'],
            'accuracy'              => ['accuracy'],
            'mounting_type'         => ['mounting', 'mounting type'],
            'pressure_element_type' => ['pressure element type', 'element', 'pressure element', 'bourdon'],
            'enclosure_rating'      => ['enclosure rating', 'enclosure', 'ip', 'protection'],
        ],
        'Temperature Gauges' => [
            'measurement_unit'  => ['measurement unit', 'unit'],
            'dial_size'         => ['dial size', 'dial'],
            'stem_length'       => ['stem length'],
            'stem_diameter'     => ['stem diameter', 'stem dia'],
            'connection_type'   => ['connection type', 'connection'],
            'accuracy'          => ['accuracy'],
            'case_material'     => ['case material', 'case'],
            'mounting_type'     => ['mounting', 'mounting type'],
            'sensor_type'       => ['sensor type', 'sensor', 'element'],
        ],
    ];

    /** Range labels that fill a min/max pair: aliases + target keys. */
    private const RANGES = [
        'Motors' => [
            ['aliases' => ['range', 'power', 'power range', 'output', 'rating', 'power output'], 'min' => 'power_min_kw', 'max' => 'power_max_kw'],
        ],
        'Pumps' => [
            ['aliases' => ['flow', 'flow rate', 'capacity', 'q'], 'min' => 'flow_rate_min', 'max' => 'flow_rate_max'],
            ['aliases' => ['head', 'total head', 'h'],            'min' => 'head_min',      'max' => 'head_max'],
        ],
        'Pressure Transmitters' => [
            ['aliases' => ['measurement range', 'range', 'measuring range', 'span'], 'min' => 'measurement_min', 'max' => 'measurement_max'],
        ],
        'Pressure Gauges' => [
            ['aliases' => ['measurement range', 'range', 'dial range'], 'min' => 'measurement_min', 'max' => 'measurement_max'],
        ],
        'Temperature Gauges' => [
            ['aliases' => ['temperature range', 'range', 'measurement range', 'temperature'], 'min' => 'temperature_min', 'max' => 'temperature_max'],
        ],
    ];

    /** Map "Motors", "IE4 Motors", "Centrifugal Pumps" → a config category key, or null. */
    public function resolveCategory(?string $category): ?string
    {
        if (!$category) return null;
        $c = strtolower(trim($category));

        foreach (array_keys(config('category_fields', [])) as $key) {
            $k = strtolower($key);
            $singular = rtrim($k, 's');
            if ($c === $k || $c === $singular || rtrim($c, 's') === $singular) return $key;
            if ($singular !== '' && str_contains($c, $singular)) return $key;
        }
        return null;
    }

    /**
     * Normalise a raw spec map to canonical category fields.
     *
     * @param  array<string,mixed> $rawSpecs   label => value pairs from the page
     * @param  bool                $keepExtras when false (website imports) unmapped
     *                                         labels are DISCARDED — scraped pages
     *                                         carry junk rows (documents/downloads
     *                                         tables, standards lists) that must not
     *                                         pollute the planned category fields.
     *                                         Vendor-typed extras stay on true.
     * @return array  canonical fields (+ 'extra' bucket only when kept)
     */
    public function normalize(?string $category, array $rawSpecs, bool $keepExtras = true): array
    {
        $cat = $this->resolveCategory($category);
        if (!$cat) {
            return ($rawSpecs && $keepExtras) ? ['extra' => $this->scalarsOnly($rawSpecs)] : [];
        }

        $aliasIndex = $this->buildAliasIndex($cat);
        $rangeDefs  = self::RANGES[$cat] ?? [];

        $result = [];
        $extra  = [];

        foreach ($rawSpecs as $label => $value) {
            $value = is_array($value) ? implode(', ', array_filter($value, 'is_scalar')) : trim((string) $value);
            if ($value === '') continue;

            $n = $this->norm((string) $label);
            if ($n === '') continue;

            // 1) range labels → split into min/max
            $range = $this->matchRange($n, $rangeDefs);
            if ($range) {
                [$min, $max] = $this->splitRange($value);
                if ($min !== null && !isset($result[$range['min']])) $result[$range['min']] = $min;
                if ($max !== null && !isset($result[$range['max']])) $result[$range['max']] = $max;
                continue;
            }

            // 2) single-value fields
            $canonical = $this->matchAlias($n, $aliasIndex);
            if ($canonical && !isset($result[$canonical])) {
                $result[$canonical] = $value;
            } elseif (!$canonical && $keepExtras) {
                $extra[trim((string) $label)] = $value;
            }
        }

        if ($extra) $result['extra'] = $extra;
        return $result;
    }

    // ── helpers ─────────────────────────────────────────────────────────

    private function buildAliasIndex(string $cat): array
    {
        $index  = [];
        $fields = config("category_fields.$cat", []);

        // canonical key (spaced) is always a valid alias
        foreach ($fields as $key => $label) {
            $index[$this->norm(str_replace('_', ' ', $key))] = $key;
            $index[$this->norm($label)] = $key;
        }
        // explicit aliases
        foreach (self::ALIASES[$cat] ?? [] as $key => $aliases) {
            foreach ($aliases as $a) $index[$this->norm($a)] = $key;
        }
        return $index;
    }

    private function matchRange(string $n, array $rangeDefs): ?array
    {
        foreach ($rangeDefs as $def) {
            foreach ($def['aliases'] as $a) {
                $a = $this->norm($a);
                if ($n === $a || str_starts_with($n, $a . ' ') || str_ends_with($n, ' ' . $a) || str_contains($n, ' ' . $a . ' ')) {
                    return $def;
                }
            }
        }
        return null;
    }

    private function matchAlias(string $n, array $aliasIndex): ?string
    {
        if (isset($aliasIndex[$n])) return $aliasIndex[$n];

        $aliases = array_keys($aliasIndex);
        usort($aliases, fn($a, $b) => strlen($b) <=> strlen($a));
        foreach ($aliases as $a) {
            if ($a !== '' && ($n === $a || str_starts_with($n, $a . ' ') || str_ends_with($n, ' ' . $a) || str_contains($n, ' ' . $a . ' '))) {
                return $aliasIndex[$a];
            }
        }
        return null;
    }

    /**
     * "0.09 kW to 1000 kW" → ["0.09 kW","1000 kW"]; "Up to 200 kW" → [null,"200 kW"];
     * "From 5 kW" / "5 kW and above" → ["5 kW", null]; single value → [value, null].
     */
    private function splitRange(string $value): array
    {
        $v = trim($value);

        if (preg_match('/^(?:up\s*to|upto|max(?:imum)?|≤|<=?)\s*(.+)$/i', $v, $m)) {
            return [null, trim($m[1])];               // max only
        }
        if (preg_match('/^(?:from|min(?:imum)?|starting(?:\s+from)?|≥|>=?)\s*(.+)$/i', $v, $m)) {
            return [trim($m[1]), null];               // min only
        }
        if (preg_match('/^(.+?)\s*(?:and\s+above|&\s*above|onwards|\+)$/i', $v, $m)) {
            return [trim($m[1]), null];               // min only
        }

        // "A to B" / "A - B" — only when BOTH sides contain a number.
        $parts = preg_split('/\s*(?:\bto\b|–|—|~|\.{2,}|\s-\s|-)\s*/i', $v, 2);
        if (is_array($parts) && count($parts) === 2) {
            $a = trim($parts[0]); $b = trim($parts[1]);
            if ($a !== '' && $b !== '' && preg_match('/\d/', $a) && preg_match('/\d/', $b)) {
                return [$a, $b];
            }
        }
        return [$v, null];
    }

    private function norm(string $s): string
    {
        $s = strtolower($s);
        $s = preg_replace('/\([^)]*\)/', ' ', $s);
        $s = preg_replace('/[^a-z0-9]+/', ' ', $s);
        return trim(preg_replace('/\s+/', ' ', $s));
    }

    private function scalarsOnly(array $arr): array
    {
        $out = [];
        foreach ($arr as $k => $v) {
            if (is_scalar($v) && trim((string) $v) !== '') $out[trim((string) $k)] = trim((string) $v);
        }
        return $out;
    }
}
