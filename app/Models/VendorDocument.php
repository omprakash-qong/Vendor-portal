<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorDocument extends Model
{
    protected $fillable = [
        'vendor_profile_id', 'file_name', 'file_path', 'mime_type',
        'file_size_bytes', 'file_type', 'upload_date',
    ];

    protected $casts = [
        'upload_date' => 'datetime',
        'file_size_bytes' => 'integer',
    ];

    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }

    public function extractionJobs()
    {
        return $this->hasMany(ExtractionJob::class);
    }

    public function fileSizeHuman(): string
    {
        $bytes = $this->file_size_bytes;
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 0) . ' KB';
        return $bytes . ' B';
    }
}
