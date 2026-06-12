<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $fillable = [
        'parent_id', 'level', 'name', 'slug', 'equipment_type',
        'spec_template', 'match_fields', 'is_active',
    ];

    protected $casts = [
        'spec_template' => 'array',
        'match_fields'  => 'array',
        'is_active'     => 'boolean',
        'level'         => 'integer',
    ];

    public function parent()
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ProductCategory::class, 'parent_id');
    }

    public function series()
    {
        return $this->hasMany(ProductSeries::class, 'category_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'category_id');
    }

    public static function roots()
    {
        return static::whereNull('parent_id')->where('is_active', true)->orderBy('name')->get();
    }
}
