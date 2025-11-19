<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
   protected $fillable = [
        'name',
        'description',
        'price',
        'stock_quantity',
        'image',
    ];

    protected $casts = [
        'price' => 'integer',
        'stock_quantity' => 'integer',
    ];

    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }

}
