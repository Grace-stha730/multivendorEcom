<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    protected $fillable = [
        'name', 'owner', 'image', 'contact_number', 'pan_number', 'province_id',
        'district_id', 'city', 'tole', 'email', 'phone', 'status',
    ];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function shopUsers()
    {
        return $this->hasMany(ShopUser::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
