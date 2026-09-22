<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\BelongsToShop;
use Illuminate\Database\Eloquent\Model;

class VendorPayout extends Model
{
    use HasFactory, BelongsToShop;

    protected $fillable = [
        'shop_id',
        'amount',
        'status',
        'payment_method',
        'account_details',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    // The admin Payouts page calls this "vendor"; it is the shop. Mirrors VendorOrder::vendor().
    public function vendor()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }
}
