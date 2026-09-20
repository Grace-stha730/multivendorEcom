<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use \App\Models\Concerns\BelongsToShop;

    protected $fillable = [
        'name',
        'shop_id',
        'admin_id',
        'description'
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function products(){
        return $this->hasMany(Product::class);
    }

    public function admin(){
        return $this->belongsTo(Admin::class);
    }
}
