<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QsMatchRequest extends Model
{
    protected $fillable = [
        'qs_job_id', 'qs_project_name', 'qs_user_email',
        'instruments', 'status', 'processing_time_ms', 'ip_address',
    ];

    protected $casts = [
        'instruments' => 'array',
    ];

    public function results()
    {
        return $this->hasMany(QsMatchResult::class, 'request_id');
    }
}
