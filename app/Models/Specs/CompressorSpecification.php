<?php

namespace App\Models\Specs;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

class CompressorSpecification extends Model
{
    protected $table = 'compressor_specifications';
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
