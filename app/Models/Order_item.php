<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order_item extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'vendor_order_id',
        'quantity',
        'price',
        'total',
        'is_rate',
        'selected_variants',
        'coupon_id',
        'coupon_discount',
    ];

    protected $casts = [
        'selected_variants' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function vendorOrder()
    {
        return $this->belongsTo(VendorOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function coupon() { return $this->belongsTo(Coupon::class); }
}
