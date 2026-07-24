<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponUser extends Model
{
    protected $table = 'coupon_user';

    protected $fillable = [
        'coupon_id',
        'user_id',
        'order_id',
        'collected_at',
        'used_at',
    ];

    protected $casts = [
        'collected_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
