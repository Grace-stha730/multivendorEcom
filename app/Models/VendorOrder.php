<?php

namespace App\Models;

use App\Models\Concerns\BelongsToShop;
use Illuminate\Database\Eloquent\Model;

class VendorOrder extends Model
{
    use BelongsToShop;

    protected $fillable = [
        'order_id',
        'shop_id',
        'subtotal',
        'status',
        'is_received',
        'quantity',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    // The admin order screen, the invoices and "Cancelled by ..." all call this "vendor"; it is the shop.
    public function vendor()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function items()
    {
        return $this->hasMany(Order_item::class);
    }
}
