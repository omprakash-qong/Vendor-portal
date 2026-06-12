<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Datasheet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vendor_profile_id',
        'product_id',
        'name',
        'pdf_path',
    ];

    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
