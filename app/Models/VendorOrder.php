<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorOrder extends Model
{
    protected $fillable = [
        'order_id',
        'vendor_id',
        'subtotal',
        'status',
        'is_received',
        'quantity',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items()
    {
        return $this->hasMany(Order_item::class);
    }
}
