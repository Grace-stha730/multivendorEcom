<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class ShopUser extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $fillable = [
        'name',
        'username',
        'personal_email',
        'address',
        'password',
        'contact',
        'image',
        'pan_number',
        'shop_id',
    ];

    public function products(){
        return $this->hasMany(Product::class, 'shop_user_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
