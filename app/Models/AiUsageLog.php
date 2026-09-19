<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiUsageLog extends Model
{
    public $timestamps = false;
    protected $fillable = ['feature', 'user_id', 'shop_user_id', 'tokens_used', 'created_at'];
}
