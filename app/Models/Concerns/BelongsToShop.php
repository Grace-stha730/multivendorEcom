<?php

namespace App\Models\Concerns;

use App\Models\Shop;
use Illuminate\Database\Eloquent\Builder;

/**
 * Opt-in shop scoping for models that carry shop_id.
 * Permissions say what a user may do; this says whose rows they may do it to.
 */
trait BelongsToShop
{
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function scopeForShop(Builder $query, int $shopId): Builder
    {
        return $query->where($this->getTable() . '.shop_id', $shopId);
    }

    /** Fails closed: with no logged-in shop_user this matches nothing rather than everything. */
    public function scopeForCurrentShop(Builder $query): Builder
    {
        $shopId = currentShopId();

        return $shopId ? $this->scopeForShop($query, $shopId) : $query->whereRaw('1 = 0');
    }
}
