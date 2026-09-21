<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected function casts(): array
    {
        return ['paid_at' => 'datetime'];
    }

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
        'wallet_discount',
        'redeemed_points',
        'coupon_discount',
        'is_shipped',
        'payment_method',
        'user_address_id',
        'receiver_name',
        'province_id',
        'district_id',
        'address_type',
        'office_start_time',
        'office_end_time',
        'payment_uuid',
        'payment_reference',
        'paid_at',
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

    // The orders table already has a text column named `province`, so these relations use other names.
    public function deliveryProvince()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function deliveryDistrict()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function userAddress()
    {
        return $this->belongsTo(UserAddress::class);
    }

    /** Orders placed before saved addresses only have the old text columns. */
    public function hasAddressSnapshot(): bool
    {
        return $this->receiver_name !== null;
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
