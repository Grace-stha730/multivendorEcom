<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductEmbedding extends Model
{
    protected $fillable = [
        'product_id',
        'embedding',
        'text_hash',
        'cluster_algorithm',
        'cluster_label',
    ];

    protected function casts(): array
    {
        return [
            'embedding' => 'array',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
