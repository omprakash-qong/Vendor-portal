<?php

namespace App\Services\Matching;

use App\Models\ProductVariant;
use App\Models\ProductCategory;
use Illuminate\Support\Collection;

class VendorMatchService
{
    // Weights must sum to 1.0
    private const WEIGHT_POWER    = 0.35;
    private const WEIGHT_SIZE     = 0.30;
    private const WEIGHT_SPEC     = 0.25;
    private const WEIGHT_TAGS     = 0.10;

    // Tolerance thresholds (fractional distance)
    private const TOLERANCE_EXACT   = 0.05;  // within 5%   → score 1.0
    private const TOLERANCE_CLOSE   = 0.20;  // within 20%  → score 0.7
    private const TOLERANCE_LOOSE   = 0.40;  // within 40%  → score 0.4

    public function match(array $instrument, int $topN = 5): array
    {
        $equipment   = $instrument['equipment_type'] ?? $instrument['type'] ?? null;
        $categoryIds = $this->resolveCategoryIds($equipment);

        // Base query — published variants in relevant categories
        $query = ProductVariant::published()
            ->with(['series', 'vendorProfile', 'category'])
            ->whereIn('category_id', $categoryIds);

        // Narrow by equipment type text if provided
        if ($equipment) {
            $query->where('equipment_type', 'like', '%' . $equipment . '%');
        }

        $candidates = $query->get();

        if ($candidates->isEmpty()) {
            // Fallback: search all published variants
            $candidates = ProductVariant::published()
                ->with(['series', 'vendorProfile', 'category'])
                ->get();
        }

        $scored = $candidates->map(function (ProductVariant $variant) use ($instrument) {
            $score     = $this->calculateScore($variant, $instrument);
            $breakdown = $this->scoreBreakdown($variant, $instrument);

            return [
                'variant'    => $variant,
                'score'      => $score,
                'breakdown'  => $breakdown,
            ];
        })->filter(fn($r) => $r['score'] > 0.05)
          ->sortByDesc('score')
          ->values()
          ->take($topN);

        return $this->formatResults($scored, $instrument);
    }

    private function calculateScore(ProductVariant $variant, array $instrument): float
    {
        $totalWeight = 0;
        $totalScore  = 0;

        // ─── Power score ─────────────────────────────────────────────
        if (isset($instrument['power_kw']) && $instrument['power_kw'] > 0 && $variant->power_kw > 0) {
            $totalWeight += self::WEIGHT_POWER;
            $totalScore  += self::WEIGHT_POWER * $this->proximityScore(
                (float) $variant->power_kw,
                (float) $instrument['power_kw']
            );
        }

        // ─── Size score ──────────────────────────────────────────────
        $reqSize = $instrument['size_inch'] ?? ($instrument['size_mm'] ? $instrument['size_mm'] / 25.4 : null);
        $varSize = $variant->size_inch ?? ($variant->size_mm ? $variant->size_mm / 25.4 : null);

        if ($reqSize && $varSize) {
            $totalWeight += self::WEIGHT_SIZE;
            $totalScore  += self::WEIGHT_SIZE * $this->proximityScore($varSize, $reqSize);
        }

        // ─── Spec score (poles, efficiency_class, pressure, etc.) ────
        $specScore = $this->specFieldScore($variant, $instrument);
        if ($specScore !== null) {
            $totalWeight += self::WEIGHT_SPEC;
            $totalScore  += self::WEIGHT_SPEC * $specScore;
        }

        // ─── Tag score ───────────────────────────────────────────────
        $tagScore = $this->tagOverlapScore($variant, $instrument);
        if ($tagScore !== null) {
            $totalWeight += self::WEIGHT_TAGS;
            $totalScore  += self::WEIGHT_TAGS * $tagScore;
        }

        // ─── Text match bonus (if nothing else scored) ───────────────
        if ($totalWeight < 0.1) {
            return $this->textOnlyScore($variant, $instrument);
        }

        return $totalWeight > 0 ? $totalScore / $totalWeight : 0;
    }

    private function proximityScore(float $actual, float $requested): float
    {
        if ($requested == 0) return 0;
        $diff = abs($actual - $requested) / $requested;

        if ($diff <= self::TOLERANCE_EXACT) return 1.0;
        if ($diff <= self::TOLERANCE_CLOSE) return 0.7;
        if ($diff <= self::TOLERANCE_LOOSE) return 0.4;
        return 0.0;
    }

    private function specFieldScore(ProductVariant $variant, array $instrument): ?float
    {
        $specs    = $variant->specifications ?? [];
        $matched  = 0;
        $total    = 0;

        $checkFields = ['poles', 'efficiency_class', 'pressure_class', 'valve_type', 'pump_type', 'ip_rating'];

        foreach ($checkFields as $field) {
            if (!isset($instrument[$field])) continue;
            $total++;
            $varValue = $specs[$field] ?? null;

            if ($varValue === null) {
                // Also check direct columns (poles, pressure_bar)
                $varValue = $variant->$field ?? null;
            }

            if ($varValue !== null && strtolower((string)$varValue) === strtolower((string)$instrument[$field])) {
                $matched++;
            }
        }

        if ($total === 0) return null;
        return $matched / $total;
    }

    private function tagOverlapScore(ProductVariant $variant, array $instrument): ?float
    {
        $reqTags  = array_merge(
            (array)($instrument['industry_tags'] ?? []),
            (array)($instrument['capability_tags'] ?? [])
        );

        if (empty($reqTags)) return null;

        $varTags = array_merge(
            (array)($variant->industry_tags ?? []),
            (array)($variant->capability_tags ?? [])
        );

        if (empty($varTags)) return 0.0;

        $overlap = count(array_intersect(
            array_map('strtolower', $reqTags),
            array_map('strtolower', $varTags)
        ));

        return $overlap / count($reqTags);
    }

    private function textOnlyScore(ProductVariant $variant, array $instrument): float
    {
        $needle   = strtolower($instrument['equipment_type'] ?? '');
        $haystack = strtolower($variant->variant_name . ' ' . $variant->equipment_type);

        if ($needle && str_contains($haystack, $needle)) return 0.3;
        return 0.05;
    }

    private function scoreBreakdown(ProductVariant $variant, array $instrument): array
    {
        $reqSize = $instrument['size_inch'] ?? ($instrument['size_mm'] ? $instrument['size_mm'] / 25.4 : null);
        $varSize = $variant->size_inch ?? ($variant->size_mm ? $variant->size_mm / 25.4 : null);

        return [
            'power' => (isset($instrument['power_kw']) && $variant->power_kw)
                       ? $this->proximityScore((float)$variant->power_kw, (float)$instrument['power_kw'])
                       : null,
            'size'  => ($reqSize && $varSize)
                       ? $this->proximityScore($varSize, $reqSize)
                       : null,
            'spec'  => $this->specFieldScore($variant, $instrument),
            'tags'  => $this->tagOverlapScore($variant, $instrument),
        ];
    }

    private function resolveCategoryIds(string|null $equipmentType): array
    {
        if (!$equipmentType) return [];

        $map = [
            'motor'       => ['motors'],
            'valve'       => ['valves'],
            'pump'        => ['pumps'],
            'compressor'  => ['compressors'],
            'blower'      => ['blowers'],
            'vacuum'      => ['vacuum equipment'],
            'pneumatic'   => ['pneumatics'],
            'instrument'  => ['instruments'],
        ];

        $lower   = strtolower($equipmentType);
        $slugs   = [];

        foreach ($map as $keyword => $categoryNames) {
            if (str_contains($lower, $keyword)) {
                $slugs = array_merge($slugs, $categoryNames);
            }
        }

        if (empty($slugs)) return [];

        return ProductCategory::whereIn(
            'name',
            array_map('ucfirst', $slugs)
        )->pluck('id')->toArray();
    }

    private function formatResults(Collection $scored, array $instrument): array
    {
        return $scored->map(function ($result, $rank) use ($instrument) {
            $v       = $result['variant'];
            $profile = $v->vendorProfile;

            return [
                'rank'         => $rank + 1,
                'match_score'  => round($result['score'], 4),
                'score_pct'    => round($result['score'] * 100, 1),
                'score_breakdown' => $result['breakdown'],
                'vendor' => [
                    'id'          => $profile->id,
                    'name'        => $profile->legal_company_name,
                    'trade_name'  => $profile->trade_name,
                    'type'        => implode(', ', (array)($profile->vendor_category ?? [])),
                    'email'       => $profile->primary_email,
                    'phone'       => $profile->primary_phone,
                    'website'     => $profile->company_website,
                    'city'        => $profile->op_city ?? $profile->reg_city,
                ],
                'product' => [
                    'variant_id'     => $v->id,
                    'variant_name'   => $v->variant_name,
                    'equipment_type' => $v->equipment_type,
                    'series'         => $v->series->name ?? null,
                    'brand'          => $v->series->brand ?? null,
                    'category'       => $v->category->name ?? null,
                    'power_kw'       => $v->power_kw,
                    'size_inch'      => $v->size_inch,
                    'size_mm'        => $v->size_mm,
                    'pressure_bar'   => $v->pressure_bar,
                    'flow_m3h'       => $v->flow_m3h,
                    'specifications' => $v->specifications,
                    'industry_tags'  => $v->industry_tags,
                    'capability_tags' => $v->capability_tags,
                    'certifications' => $v->certifications,
                    'datasheet_url'  => $v->datasheet_url,
                ],
            ];
        })->values()->all();
    }
}
