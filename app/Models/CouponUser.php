<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponUser extends Model
{
    protected $table = 'coupon_user';

    protected $fillable = [
        'coupon_id',
        'user_id',
        'collected_at',
    ];

    protected $casts = [
        'collected_at' => 'datetime',
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
