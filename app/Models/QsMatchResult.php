<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QsMatchResult extends Model
{
    protected $fillable = [
        'request_id', 'variant_id', 'vendor_profile_id',
        'instrument_tag', 'instrument_type',
        'match_score', 'score_breakdown', 'rank',
    ];

    protected $casts = [
        'score_breakdown' => 'array',
        'match_score'     => 'float',
        'rank'            => 'integer',
    ];

    public function request()
    {
        return $this->belongsTo(QsMatchRequest::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }
}
