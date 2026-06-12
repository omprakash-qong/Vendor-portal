<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_profile_id',
        'customer_name',
        'subject',
        'remarks',
        'attachment_path',
        'original_filename',
        'status',
    ];

    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }
}
