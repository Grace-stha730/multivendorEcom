<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'price',
        'stock',
        'summary',
        'description',
        'discount',
        'discount_amount',
        'vendor_id',
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

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
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

    public function embedding()
    {
        return $this->hasOne(ProductEmbedding::class);
    }

    public function collections()
    {
        return $this->belongsToMany(ProductCollection::class, 'collection_products')
            ->withTimestamps();
    }

    public function getImageUrlAttribute()
{
    return $this->firstImage?->url
        ? asset('storage/' . $this->firstImage->url)
        : asset('images/2.png');
}
}
