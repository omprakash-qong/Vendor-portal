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
        'sku',
        'category',
        'description',
        'short_description',
        'long_description',
        'specifications',
        'image_path',
        'datasheet_url',
        'catalogue_url',
        'import_source',
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

    public function datasheets()
    {
        return $this->hasMany(Datasheet::class);
    }

    public function additionalSpecifications()
    {
        return $this->hasMany(ProductAdditionalSpecification::class);
    }
}
