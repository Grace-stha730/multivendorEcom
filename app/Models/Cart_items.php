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
        'selected_variants',
        'coupon_id',
        'coupon_discount',
    ];

    protected $casts = [
        'selected_variants' => 'array',
        'coupon_discount' => 'decimal:2',
    ];

    public function cart(){
        return $this->belongsTo(Cart::class);
    }

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function coupon(){
        return $this->belongsTo(Coupon::class);
    }
}
