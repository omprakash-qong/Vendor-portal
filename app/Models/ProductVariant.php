<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vendor_profile_id', 'series_id', 'category_id', 'extraction_job_id',
        'variant_name', 'equipment_type',
        'power_kw', 'size_inch', 'size_mm', 'pressure_bar', 'flow_m3h', 'voltage_v', 'poles',
        'industry_tags', 'capability_tags', 'certifications',
        'specifications', 'datasheet_url',
        'is_active', 'is_published', 'published_at',
    ];

    protected $casts = [
        'industry_tags'  => 'array',
        'capability_tags' => 'array',
        'certifications' => 'array',
        'specifications' => 'array',
        'power_kw'       => 'float',
        'size_inch'      => 'float',
        'size_mm'        => 'float',
        'pressure_bar'   => 'float',
        'flow_m3h'       => 'float',
        'is_active'      => 'boolean',
        'is_published'   => 'boolean',
        'published_at'   => 'datetime',
    ];

    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }

    public function series()
    {
        return $this->belongsTo(ProductSeries::class);
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function extractionJob()
    {
        return $this->belongsTo(ExtractionJob::class);
    }

    public function matchResults()
    {
        return $this->hasMany(QsMatchResult::class, 'variant_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->where('is_active', true)->whereNull('deleted_at');
    }
}
