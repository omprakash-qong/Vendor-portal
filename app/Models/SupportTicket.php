<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_profile_id',
        'subject',
        'description',
        'status',
    ];

    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }
}
