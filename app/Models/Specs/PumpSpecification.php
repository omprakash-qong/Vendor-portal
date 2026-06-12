<?php

namespace App\Models\Specs;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

class PumpSpecification extends Model
{
    protected $table = 'pump_specifications';
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
