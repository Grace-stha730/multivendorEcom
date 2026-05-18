<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'vendor_id',
        'admin_id',
        'description'
    ];

    public function products(){
        return $this->hasMany(Product::class);
    }

    public function vendor(){
        return $this->belongsTo(Vendor::class);
    }
    public function admin(){
        return $this->belongsTo(Admin::class);
    }
}
