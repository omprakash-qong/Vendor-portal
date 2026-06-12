<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtractionJob extends Model
{
    protected $fillable = [
        'vendor_profile_id', 'vendor_document_id', 'status',
        'raw_extracted', 'ai_structured', 'error_message',
        'processing_started_at', 'processing_completed_at', 'approved_at',
    ];

    protected $casts = [
        'raw_extracted'           => 'array',
        'ai_structured'           => 'array',
        'processing_started_at'   => 'datetime',
        'processing_completed_at' => 'datetime',
        'approved_at'             => 'datetime',
    ];

    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }

    public function document()
    {
        return $this->belongsTo(VendorDocument::class, 'vendor_document_id');
    }

    public function productSeries()
    {
        return $this->hasMany(ProductSeries::class);
    }

    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function isEditable(): bool
    {
        return $this->status === 'preview_ready';
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'pending'       => 'Queued',
            'processing'    => 'Extracting...',
            'preview_ready' => 'Ready to Review',
            'approved'      => 'Published',
            'failed'        => 'Failed',
            'rejected'      => 'Rejected',
            default         => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'approved'       => 'success',
            'preview_ready'  => 'warning',
            'failed'         => 'error',
            'rejected'       => 'error',
            default          => 'muted',
        };
    }
}
