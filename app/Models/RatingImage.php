<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatingImage extends Model
{
    protected $fillable = [
        'product_rating_id',
        'images'
    ];

    public function productRating(){
        return $this->belongsTo(productRating::class);
    }
}
