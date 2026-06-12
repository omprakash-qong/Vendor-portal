<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportJob extends Model
{
    protected $fillable = [
        'vendor_profile_id', 'source_type', 'website_url',
        'file_name', 'file_path', 'status',
        'pages_crawled', 'products_found', 'failed_pages',
        'started_at', 'completed_at', 'error_message',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function vendorProfile(): BelongsTo
    {
        return $this->belongsTo(VendorProfile::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ImportJobLog::class);
    }

    public function stagingProducts(): HasMany
    {
        return $this->hasMany(ProductStaging::class);
    }

    public function scopeVendorJobs($query)
    {
        $vendor = auth()->user()?->vendorProfile;
        return $query->where('vendor_profile_id', $vendor?->id);
    }

    public function isRunning(): bool
    {
        return in_array($this->status, ['queued', 'running']);
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, ['completed', 'failed']);
    }
}
