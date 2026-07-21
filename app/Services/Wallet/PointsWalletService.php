<?php

namespace App\Services\Wallet;

use App\Models\Order;
use App\Models\User;
use App\Models\WalletTransaction;

class PointsWalletService
{
    public const POINTS_PER_RUPEE = 10;
    public const EARN_RATE = 0.02;

    public function balance(User $user): int
    {
        return (int) WalletTransaction::where('user_id', $user->id)->sum('points');
    }

    public function discountForPoints(int $points, float $cartTotal): float
    {
        $discount = floor(max(0, $points) / self::POINTS_PER_RUPEE);

        return min($discount, $cartTotal);
    }

    public function maxRedeemablePoints(User $user, float $cartTotal): int
    {
        $balance = $this->balance($user);
        $cartCap = (int) floor($cartTotal * self::POINTS_PER_RUPEE);

        return min($balance, $cartCap);
    }

    public function redeem(User $user, Order $order, int $points, float $cartTotal): float
    {
        $points = min($points, $this->maxRedeemablePoints($user, $cartTotal));
        $discount = $this->discountForPoints($points, $cartTotal);

        if ($points <= 0 || $discount <= 0) {
            return 0.0;
        }

        WalletTransaction::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'points' => -$points,
            'amount' => $discount,
            'type' => WalletTransaction::TYPE_REDEEM,
            'description' => 'Redeemed points at checkout',
        ]);

        return $discount;
    }

    public function awardForOrder(User $user, Order $order, float $paidAmount): int
    {
        $points = (int) floor($paidAmount * self::EARN_RATE * self::POINTS_PER_RUPEE);

        if ($points <= 0) {
            return 0;
        }

        WalletTransaction::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'points' => $points,
            'amount' => 0,
            'type' => WalletTransaction::TYPE_EARN,
            'description' => 'Earned from order',
        ]);

        return $points;
    }
}
