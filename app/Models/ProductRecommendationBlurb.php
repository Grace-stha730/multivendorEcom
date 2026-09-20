<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductRecommendationBlurb extends Model
{
    public $timestamps = false;
    protected $fillable = ['product_id', 'related_product_id', 'blurb_text', 'generated_at'];
}
