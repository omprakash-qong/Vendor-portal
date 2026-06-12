<?php

namespace App\Models\Specs;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

class BlowerSpecification extends Model
{
    protected $table = 'blower_specifications';
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
