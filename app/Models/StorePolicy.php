<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorePolicy extends Model
{
    public $timestamps = false;
    protected $fillable = ['key', 'value', 'updated_at'];
}
