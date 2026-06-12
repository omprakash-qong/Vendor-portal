<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSeries extends Model
{
    protected $fillable = [
        'vendor_profile_id', 'category_id', 'extraction_job_id',
        'name', 'brand', 'description', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function extractionJob()
    {
        return $this->belongsTo(ExtractionJob::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'series_id');
    }

    public function publishedVariants()
    {
        return $this->hasMany(ProductVariant::class, 'series_id')
                    ->where('is_published', true)
                    ->where('is_active', true);
    }
}
