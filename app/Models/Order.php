<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'email',
        'name',
        'province',
        'city',
        'tole',
        'phone',
        'price',
        'payment_status',
        'order_status',
        'is_shipped',
        'payment_method',
        'admin_id',
        'quantity'
    ];

    public function admin(){
        return $this->belongsTo(Admin::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(Order_item::class);
    }

    public function vendorOrders()
    {
        return $this->hasMany(VendorOrder::class);
    }
}
