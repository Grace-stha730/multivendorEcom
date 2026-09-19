<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVector extends Model
{
    protected $fillable = ['product_id', 'vector', 'text_hash'];

    protected function casts(): array
    {
        return ['vector' => 'array'];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
