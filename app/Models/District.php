<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $fillable = ['province_id', 'name'];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function shops()
    {
        return $this->hasMany(Shop::class);
    }
}
