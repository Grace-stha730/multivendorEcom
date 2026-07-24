<?php

namespace App\Livewire\User;

use App\Models\Coupon;
use App\Models\CouponUser;
use App\Models\productRating;
use App\Models\WalletTransaction;
use App\Models\Wishlist;
use App\Services\Wallet\PointsWalletService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Coupons')]
#[Layout('components/layouts/user')]
class Coupons extends Component
{
    public int $walletBalance = 0;
    public int $reviewCount = 0;
    public int $wishlistCount = 0;
    public int $earnedPoints = 0;
    public array $collectedCouponIds = [];

    public function mount()
    {
        if (! Auth::guard('web')->check()) {
            return;
        }

        $user = Auth::guard('web')->user();
        $this->walletBalance = app(PointsWalletService::class)->balance($user);
        $this->reviewCount = productRating::where('user_id', $user->id)->count();
        $this->wishlistCount = Wishlist::where('user_id', $user->id)->count();
        $this->earnedPoints = WalletTransaction::where('user_id', $user->id)
            ->where('type', WalletTransaction::TYPE_EARN)
            ->sum('points');
        $this->loadCollectedCoupons();
    }

    public function collectCoupon($couponId)
    {
        if (! Auth::guard('web')->check()) {
            return redirect()->route('user.login')->with('error', 'Please login to collect coupons.');
        }

        $coupon = Coupon::findOrFail($couponId);

        if (! $coupon->isAvailable()) {
            session()->flash('error', 'This coupon is not available anymore.');
            return;
        }

        CouponUser::firstOrCreate([
            'coupon_id' => $coupon->id,
            'user_id' => Auth::guard('web')->id(),
        ], [
            'collected_at' => now(),
        ]);

        $this->loadCollectedCoupons();
        session()->flash('success', 'Coupon collected successfully.');
    }

    private function loadCollectedCoupons()
    {
        if (! Auth::guard('web')->check()) {
            $this->collectedCouponIds = [];
            return;
        }

        $this->collectedCouponIds = CouponUser::where('user_id', Auth::guard('web')->id())
            ->pluck('coupon_id')
            ->all();
    }

    public function render()
    {
        return view('livewire.user.coupons', [
            'coupons' => Coupon::with('vendor')
                ->where('is_active', true)
                ->whereColumn('used_count', '<', 'usage_limit')
                ->where(function ($query) {
                    $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                })
                ->latest()
                ->get(),
        ]);
    }
}
