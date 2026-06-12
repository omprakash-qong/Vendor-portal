<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStaging extends Model
{
    protected $table = 'products_staging';

    protected $fillable = [
        'vendor_profile_id', 'import_job_id',
        'product_name', 'model_number', 'brand', 'category', 'sku',
        'short_description', 'long_description',
        'source_url', 'datasheet_url', 'image_url',
        'raw_data_json', 'specifications_json',
        'status', 'rejection_reason',
    ];

    protected $casts = [
        'raw_data_json'       => 'array',
        'specifications_json' => 'array',
    ];

    public function vendorProfile(): BelongsTo
    {
        return $this->belongsTo(VendorProfile::class);
    }

    public function importJob(): BelongsTo
    {
        return $this->belongsTo(ImportJob::class);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeForVendor($query, int $vendorProfileId)
    {
        return $query->where('vendor_profile_id', $vendorProfileId);
    }
}
