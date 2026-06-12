<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vendor_profile_id',
        'name',
        'model_number',
        'brand',
        'category',
        'description',
        'specifications',
        'image_path',
        'catalogue_url',   // internal: resume-dedup of already-imported product URLs
        'import_source',   // internal: which website the import came from
        'status',
    ];

    protected $casts = [
        'specifications' => 'array',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }
}
