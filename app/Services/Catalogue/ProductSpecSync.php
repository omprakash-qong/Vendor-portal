<?php

namespace App\Services\Catalogue;

use App\Models\Product;
use App\Models\ProductAdditionalSpecification;
use App\Models\Specs\BlowerSpecification;
use App\Models\Specs\CompressorSpecification;
use App\Models\Specs\MotorSpecification;
use App\Models\Specs\PressureGaugeSpecification;
use App\Models\Specs\PressureTransmitterSpecification;
use App\Models\Specs\PumpSpecification;
use App\Models\Specs\TemperatureGaugeSpecification;
use App\Models\Specs\ValveSpecification;

/**
 * Mirrors a product's specifications JSON into the normalized per-category
 * spec table (searchable columns) and the product_additional_specifications
 * table (unknown / not-yet-promoted fields).
 *
 * JSON remains the source of truth and the flexible layer — vendors can add
 * any field there. This sync promotes the known canonical fields into SQL.
 */
class ProductSpecSync
{
    private const MODELS = [
        'Motors'                => MotorSpecification::class,
        'Pumps'                 => PumpSpecification::class,
        'Valves'                => ValveSpecification::class,
        'Blowers'               => BlowerSpecification::class,
        'Compressors'           => CompressorSpecification::class,
        'Pressure Transmitters' => PressureTransmitterSpecification::class,
        'Pressure Gauges'       => PressureGaugeSpecification::class,
        'Temperature Gauges'    => TemperatureGaugeSpecification::class,
    ];

    public function __construct(private CategorySpecMapper $mapper) {}

    public function sync(Product $product): void
    {
        $specs = $product->specifications ?? [];
        if (!is_array($specs)) $specs = [];

        $extra = (isset($specs['extra']) && is_array($specs['extra'])) ? $specs['extra'] : [];
        $flat  = array_filter(
            $specs,
            fn($v, $k) => $k !== 'extra' && is_scalar($v) && trim((string) $v) !== '',
            ARRAY_FILTER_USE_BOTH
        );

        $category = $this->mapper->resolveCategory($product->category);
        $modelClass = $category ? (self::MODELS[$category] ?? null) : null;

        if ($modelClass) {
            // Only columns that exist for this category.
            $columns = array_keys(config("category_fields.$category", []));
            $row = [];
            foreach ($columns as $col) {
                if (array_key_exists($col, $flat)) {
                    $row[$col] = $flat[$col];
                }
            }
            $modelClass::updateOrCreate(['product_id' => $product->id], $row);

            // Canonical fields that didn't fit a column fall through to "extra".
            foreach ($flat as $k => $v) {
                if (!in_array($k, $columns, true)) {
                    $extra[$k] = $v;
                }
            }
        } else {
            // No matching category table → everything goes to additional specs.
            foreach ($flat as $k => $v) {
                $extra[$k] = $v;
            }
        }

        // Rewrite the additional-specs rows for this product.
        ProductAdditionalSpecification::where('product_id', $product->id)->delete();
        foreach ($extra as $name => $value) {
            $name = trim((string) $name);
            if ($name === '') continue;
            ProductAdditionalSpecification::create([
                'product_id'  => $product->id,
                'field_name'  => $name,
                'field_value' => is_scalar($value) ? (string) $value : json_encode($value),
            ]);
        }
    }
}
