<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryField extends Model
{
    protected $fillable = [
        'category_slug', 'field_name', 'field_label', 'field_type',
        'unit', 'options_json', 'is_required', 'is_matching_field', 'is_filter', 'sort_order',
    ];

    protected $casts = [
        'options_json'      => 'array',
        'is_required'       => 'boolean',
        'is_matching_field' => 'boolean',
        'is_filter'         => 'boolean',
    ];

    public static function forCategory(string $category): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('category_slug', strtolower($category))
            ->orderBy('sort_order')
            ->get();
    }

    public static function filtersForCategory(string $category): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('category_slug', strtolower($category))
            ->where('is_filter', true)
            ->orderBy('sort_order')
            ->get();
    }
}
