<?php

namespace App\Models\Specs;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

class ValveSpecification extends Model
{
    protected $table = 'valve_specifications';
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
