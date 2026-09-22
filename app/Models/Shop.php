<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    protected $fillable = [
        'name', 'owner', 'image', 'contact_number', 'pan_number', 'province_id',
        'district_id', 'city', 'tole', 'email', 'status',
        'ai_auto_reply_enabled',
    ];

    // Older screens read shop_name / phone; the shops table calls them name / contact_number.
    public function getShopNameAttribute(): ?string
    {
        return $this->name;
    }

    public function getPhoneAttribute(): ?string
    {
        return $this->contact_number;
    }

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

    public function ratings()
    {
        return $this->hasManyThrough(productRating::class, Product::class, 'shop_id', 'product_id');
    }
}
