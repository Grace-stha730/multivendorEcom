<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'type',
        'value',
        'min_order_amount',
        'usage_limit',
        'used_count',
        'min_item_price',
        'vendor_id',
        'is_active',
        'starts_at',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'min_item_price' => 'decimal:2',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function collectors()
    {
        return $this->hasMany(CouponUser::class);
    }

    public function collectedByUsers()
    {
        return $this->belongsToMany(User::class, 'coupon_user')
            ->withPivot(['order_id', 'collected_at', 'used_at'])
            ->withTimestamps();
    }

    public function isAvailable()
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        return !$this->isLimitReached();
    }

    public function isLimitReached()
    {
        return $this->usage_limit > 0 && $this->used_count >= $this->usage_limit;
    }

    public function remainingUses()
    {
        if ($this->usage_limit <= 0) {
            return null;
        }

        return max(0, $this->usage_limit - $this->used_count);
    }

    public function isValidForAmount($subtotal)
    {
        return $this->isAvailable() && $subtotal >= $this->min_order_amount;
    }

    public function hasEligibleItem($cartItems)
    {
        if ($this->min_item_price <= 0) {
            return true;
        }

        return $cartItems->contains(fn ($item) => $item->price >= $this->min_item_price);
    }

    public function eligibleSubtotal($cartItems)
    {
        if ($this->min_item_price <= 0) {
            return $cartItems->sum('sub_total');
        }

        return $cartItems
            ->filter(fn ($item) => $item->price >= $this->min_item_price)
            ->sum('sub_total');
    }

    public function calculateDiscount($subtotal)
    {
        if (!$this->isAvailable()) {
            return 0;
        }

        if ($this->type === 'percent') {
            return ($subtotal * $this->value) / 100;
        }

        return min($this->value, $subtotal);
    }
}
