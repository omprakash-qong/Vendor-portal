<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rfq extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_profile_id',
        'rfq_number',
        'product_name',
        'description',
    ];

    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }

    public function quotation()
    {
        return $this->hasOne(Quotation::class);
    }
}
