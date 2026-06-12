<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QsApiKey extends Model
{
    protected $fillable = ['name', 'key_hash', 'key_prefix', 'is_active', 'last_used_at'];

    protected $casts = [
        'is_active'    => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public static function generate(string $name): array
    {
        $rawKey = 'qvp_' . Str::random(48);
        $record = static::create([
            'name'       => $name,
            'key_hash'   => hash('sha256', $rawKey),
            'key_prefix' => substr($rawKey, 0, 8),
            'is_active'  => true,
        ]);

        return ['record' => $record, 'raw_key' => $rawKey];
    }

    public static function findByRawKey(string $rawKey): ?static
    {
        return static::where('key_hash', hash('sha256', $rawKey))
                     ->where('is_active', true)
                     ->first();
    }
}
