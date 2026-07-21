<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollectionStar extends Model
{
    protected $fillable = [
        'product_collection_id',
        'user_id',
    ];

    public function collection()
    {
        return $this->belongsTo(ProductCollection::class, 'product_collection_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
