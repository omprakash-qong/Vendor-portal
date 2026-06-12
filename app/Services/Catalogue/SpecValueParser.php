<?php

namespace App\Services\Catalogue;

/**
 * Extracts comparable numbers from specification value strings.
 *
 * Vendors and scraped pages write values like "110kV to 400kV", "Up to
 * 200 kW" or "415 V". Those strings can't be compared ("110kV" < "90kV"
 * lexically!) — so alongside the display string we derive a numeric index:
 *
 *   parse("110kV to 400kV") → ['min' => 110.0, 'max' => 400.0, 'unit' => 'kV']
 *   parse("Up to 200 kW")   → ['min' => null,  'max' => 200.0, 'unit' => 'kW']
 *   parse("415 V")          → ['min' => 415.0, 'max' => 415.0, 'unit' => 'V']
 *   parse("Carbon Steel")   → null   (nothing numeric to compare)
 *
 * Deterministic / rule-based only — NO AI. Multi-value lists ("NPS 8,
 * NPS 10, NPS 12") are NOT collapsed to a number; they stay display-only.
 */
class SpecValueParser
{
    /** Canonical casing for common engineering units (lookup key lowercased). */
    private const UNITS = [
        'kv' => 'kV', 'v' => 'V', 'mv' => 'MV',
        'kw' => 'kW', 'w' => 'W', 'mw' => 'MW', 'hp' => 'HP',
        'hz' => 'Hz', 'khz' => 'kHz', 'rpm' => 'RPM',
        'bar' => 'bar', 'mbar' => 'mbar', 'psi' => 'psi', 'kpa' => 'kPa', 'mpa' => 'MPa', 'pa' => 'Pa',
        'kg/cm2' => 'kg/cm²', 'kg/cm²' => 'kg/cm²',
        '°c' => '°C', 'c' => '°C', 'degc' => '°C', 'deg c' => '°C', '°f' => '°F', 'f' => '°F', 'degf' => '°F',
        'mm' => 'mm', 'cm' => 'cm', 'm' => 'm', 'in' => 'in', 'inch' => 'in', '"' => 'in',
        'm3/h' => 'm³/h', 'm³/h' => 'm³/h', 'm3/hr' => 'm³/h', 'lpm' => 'LPM', 'l/min' => 'L/min',
        'l/s' => 'L/s', 'cfm' => 'CFM', 'gpm' => 'GPM',
        'db' => 'dB', 'db(a)' => 'dB(A)', 'dba' => 'dB(A)',
        '%' => '%', 'a' => 'A', 'ma' => 'mA', 'nm' => 'Nm', 'l' => 'L', 'litres' => 'L', 'liters' => 'L',
        's' => 's', 'ms' => 'ms', 'sec' => 's',
    ];

    /**
     * Parse one display value into a comparable {min, max, unit} triple.
     * Returns null when the value has no single comparable number
     * (pure text, codes, or multi-value lists).
     *
     * @return array{min: float|null, max: float|null, unit: string|null}|null
     */
    public function parse(mixed $value): ?array
    {
        if (!is_scalar($value)) return null;
        $v = trim((string) $value);
        if ($v === '' || !preg_match('/\d/', $v)) return null;

        // Fold thousands separators ("1,000 kW") so the list check below
        // only sees real enumeration commas.
        $v = preg_replace('/(\d),(?=\d{3}(\D|$))/', '$1', $v);

        // Multi-value lists ("NPS 8, NPS 10", "230/400 V", "1 or 2") are
        // enumerations, not ranges — leave them display-only.
        if (preg_match('/,|;|\bor\b|\//', $v) && !preg_match('#m3/h|m³/h|l/min|l/s|kg/cm#i', $v)) {
            return null;
        }

        // "Up to 200 kW" / "max 200 kW" / "≤200kW"  → max only
        if (preg_match('/^(?:up\s*to|upto|max(?:imum)?\.?|≤|<=?)\s*(.+)$/iu', $v, $m)) {
            $side = $this->side($m[1]);
            return $side ? ['min' => null, 'max' => $side['num'], 'unit' => $side['unit']] : null;
        }
        // "From 5 kW" / "min 5 kW" / "≥5kW" / "5 kW and above" → min only
        if (preg_match('/^(?:from|min(?:imum)?\.?|starting(?:\s+from)?|≥|>=?)\s*(.+)$/iu', $v, $m)
            || preg_match('/^(.+?)\s*(?:and\s+above|&\s*above|onwards|\+)$/iu', $v, $m)) {
            $side = $this->side($m[1]);
            return $side ? ['min' => $side['num'], 'max' => null, 'unit' => $side['unit']] : null;
        }

        // "A to B" ranges. Split on "to", en/em dash, "~", "..", or a hyphen
        // that sits between digit(+unit) and digit — never the leading minus
        // of a negative number ("-40°C to 200°C").
        $parts = preg_split('/\s*(?:\bto\b|–|—|~|\.{2,})\s*/iu', $v, 2);
        if (count($parts) !== 2) {
            $parts = preg_split('/(?<=[\d%a-zA-Z²³°])\s*-\s*(?=[+\d])/u', $v, 2);
        }
        if (is_array($parts) && count($parts) === 2) {
            $a = $this->side($parts[0]);
            $b = $this->side($parts[1]);
            if ($a && $b) {
                $unit = $b['unit'] ?? $a['unit'];   // "110 to 400 kV" — unit usually trails
                [$lo, $hi] = $a['num'] <= $b['num'] ? [$a['num'], $b['num']] : [$b['num'], $a['num']];
                return ['min' => $lo, 'max' => $hi, 'unit' => $unit];
            }
        }

        // Single value: "415 V", "2900 RPM", "IP55" is text+number → only
        // accept when the number LEADS (optionally signed), unit trails.
        $side = $this->side($v);
        return $side ? ['min' => $side['num'], 'max' => $side['num'], 'unit' => $side['unit']] : null;
    }

    /**
     * Derive the comparable-number index for a whole specifications map.
     * Skips the 'extra' bucket and any previous '_numeric' key.
     *
     * @param  array<string,mixed> $specs
     * @return array<string,array{min: float|null, max: float|null, unit: string|null}>
     */
    public function derive(array $specs): array
    {
        $out = [];
        foreach ($specs as $key => $value) {
            if ($key === 'extra' || $key === '_numeric') continue;
            $parsed = $this->parse($value);
            if ($parsed !== null) $out[$key] = $parsed;
        }
        return $out;
    }

    /** Parse one side of a range: leading signed number + optional short unit. */
    private function side(string $s): ?array
    {
        $s = trim($s);
        if (!preg_match('/^([+-]?\d{1,3}(?:,\d{3})+|[+-]?\d+(?:\.\d+)?)\s*(.{0,12})$/u', $s, $m)) {
            return null;
        }
        $num  = (float) str_replace(',', '', $m[1]);
        $unit = trim($m[2]);

        if ($unit !== '') {
            // Unit must look like a unit, not trailing prose.
            if (!preg_match('#^[a-zA-Zµ°%²³"/().]+$#u', $unit)) return null;
            $unit = self::UNITS[strtolower($unit)] ?? $unit;
        }
        return ['num' => $num, 'unit' => $unit !== '' ? $unit : null];
    }
}
