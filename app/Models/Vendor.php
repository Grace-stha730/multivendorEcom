<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Vendor extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $fillable = [
        'shop_name',
        'shop_province',
        'owner_name',
        'shop_city',
        'shop_tole',
        'shop_image',
        'shop_phone',
        'shop_email',
        'password',
        'token',
        'status',
    ];

    public function products(){
        return $this->hasMany(Product::class);
    }
}
