<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'admin_coupon_id',
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function cartItems(){
        return $this->hasMany(Cart_items::class);
    }

    public function adminCoupon(){
        return $this->belongsTo(Coupon::class, 'admin_coupon_id');
    }
}
