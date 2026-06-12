<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfq_id',
        'vendor_profile_id',
        'customer_name',
        'subject',
        'price',
        'lead_time',
        'remarks',
        'attachment_path',
        'original_filename',
        'status',
    ];

    public function rfq()
    {
        return $this->belongsTo(Rfq::class);
    }

    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }
}
