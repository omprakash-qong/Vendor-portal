<?php

namespace App\Services\Import;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSeries;
use App\Models\ProductVariant;
use App\Models\ProductStaging;

class StagingPublishService
{
    public function publish(ProductStaging $staging): Product
    {
        $product = Product::create([
            'vendor_profile_id' => $staging->vendor_profile_id,
            'name'              => $staging->product_name,
            'model_number'      => $staging->model_number,
            'brand'             => $staging->brand,
            'sku'               => $staging->sku,
            'category'          => $staging->category ?? 'Other',
            'description'       => $staging->short_description,
            'short_description' => $staging->short_description,
            'long_description'  => $staging->long_description,
            'specifications'    => $staging->specifications_json,
            'datasheet_url'     => $staging->datasheet_url,
            'import_source'     => $staging->importJob?->source_type ?? 'manual',
            'status'            => 'published',
        ]);

        $this->pushToVariants($product, $staging);

        $staging->update(['status' => 'approved']);

        return $product;
    }

    private function pushToVariants(Product $product, ProductStaging $staging): void
    {
        $specs  = $staging->specifications_json ?? [];
        $catId  = $this->resolveCategoryId($staging->category);

        $series = ProductSeries::firstOrCreate(
            [
                'vendor_profile_id' => $product->vendor_profile_id,
                'name'              => $staging->brand ?? $product->name,
            ],
            ['category_id' => $catId, 'is_active' => true]
        );

        ProductVariant::create([
            'vendor_profile_id' => $product->vendor_profile_id,
            'series_id'         => $series->id,
            'category_id'       => $catId ?? $series->category_id,
            'variant_name'      => $product->name,
            'equipment_type'    => $staging->category ?? 'Equipment',
            'power_kw'          => $specs['power_kw']     ?? null,
            'size_mm'           => $specs['size_mm']      ?? null,
            'size_inch'         => $specs['size_inch']    ?? null,
            'pressure_bar'      => $specs['pressure_bar'] ?? null,
            'flow_m3h'          => $specs['flow_m3h']     ?? null,
            'voltage_v'         => $specs['voltage_v']    ?? null,
            'poles'             => $specs['poles']        ?? null,
            'specifications'    => $specs,
            'is_published'      => true,
            'published_at'      => now(),
        ]);
    }

    private function resolveCategoryId(?string $categoryName): ?int
    {
        if (!$categoryName) return null;
        return ProductCategory::where('name', $categoryName)->value('id');
    }
}
