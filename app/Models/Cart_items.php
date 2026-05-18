<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart_items extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'price',
        'sub_total',
    ];

    public function cart(){
        return $this->belongsTo(Cart::class);
    }

    public function product(){
        return $this->belongsTo(Product::class);
    }
}
