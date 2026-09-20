<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\BelongsToShop;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, BelongsToShop;
    protected $fillable = [
        'name',
        'price',
        'stock',
        'summary',
        'description',
        'discount',
        'discount_amount',
        'shop_id',
        'shop_user_id',
        'category_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function firstImage()
    {
        return $this->hasOne(Image::class)->oldestOfMany();
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function shopUser()
    {
        return $this->belongsTo(ShopUser::class);
    }

    public function cartItem()
    {
        return $this->hasOne(Cart_items::class);
    }

    public function orderItem()
    {
        return $this->hasOne(Order_item::class);
    }

    public function productRating(){
        return $this->hasOne(productRating::class);
    }

    public function reviews()
    {
        return $this->hasMany(productRating::class);
    }

    public function ratings()
    {
        return $this->reviews();
    }
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }

    public function getImageUrlAttribute()
{
    return $this->firstImage?->url
        ? asset('storage/' . $this->firstImage->url)
        : asset('images/2.png');
}
}
