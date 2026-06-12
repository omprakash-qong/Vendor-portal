<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportJobLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['import_job_id', 'level', 'url', 'message', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];

    public function importJob()
    {
        return $this->belongsTo(ImportJob::class);
    }
}
