<?php

namespace App\Models\Specs;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

class TemperatureGaugeSpecification extends Model
{
    protected $table = 'temperature_gauge_specifications';
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
