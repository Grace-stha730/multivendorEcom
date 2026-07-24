<?php

namespace App\Livewire\User;

use App\Models\Coupon;
use App\Models\CouponUser;
use App\Models\Order;
use App\Models\Order_item;
use App\Models\VendorOrder;
use App\Services\Wallet\PointsWalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Cart as ModalCart;
use App\Models\Cart_items;
use App\Models\Product;
use Livewire\Attributes\Title;

#[Title(content: 'Cart')]
#[Layout('components/layouts/user')]
class Cart extends Component
{
    public $userId, $userName, $userEmail, $userProvince, $userCity, $userTole, $userPhone;
    public $cartItems = [];
    public $subTotal = 0;
    public $cartItem;
    public $paymentMethod;
    public $walletBalance = 0;
    public $redeemPoints = 0;
    public $walletDiscount = 0;

    // Coupon Properties
    public $selectedCouponId = '';
    public $appliedCoupon = null;
    public $discountAmount = 0;

    public function mount()
    {
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            $this->userId = $user->id;
            $this->userName = $user->name;
            $this->userEmail = $user->email;
            $this->userProvince = $user->province;
            $this->userCity = $user->city;
            $this->userTole = $user->tole;
            $this->userPhone = $user->phone;
            $this->walletBalance = app(PointsWalletService::class)->balance($user);

            $carts = ModalCart::where('user_id', $this->userId)
                ->with('cartItems.product')
                ->get();

            foreach ($carts as $cart) {
                foreach ($cart->cartItems as $item) {
                    $this->cartItems[$item->id] = $item->quantity;
                    $this->subTotal += $item->price * $item->quantity;
                }
            }

            $this->calculateWalletDiscount();
        } else {
            return redirect()->route('user.login')->with('error', 'Login to access this page');
        }
    }

    public function getCartItems()
    {
        return Cart_items::whereHas('cart', fn ($query) => $query->where('user_id', $this->userId))
            ->with('product')
            ->get();
    }

    public function applyCoupon()
    {
        if (!$this->selectedCouponId) {
            $this->addError('selectedCouponId', 'Please select a collected coupon.');
            return;
        }

        $couponUser = CouponUser::where('user_id', $this->userId)
            ->where('coupon_id', $this->selectedCouponId)
            ->whereNull('used_at')
            ->with('coupon')
            ->first();

        if (!$couponUser || !$couponUser->coupon) {
            $this->addError('selectedCouponId', 'Please collect this coupon before using it.');
            return;
        }

        $coupon = $couponUser->coupon;
        $cartItems = $this->getCartItems();

        if (!$coupon->isAvailable()) {
            $this->addError('selectedCouponId', 'This coupon is not available anymore.');
            return;
        }

        if ($this->subTotal < $coupon->min_order_amount) {
            $this->addError('selectedCouponId', 'Minimum order amount to apply this coupon is Rs. ' . number_format($coupon->min_order_amount));
            return;
        }

        if (!$coupon->hasEligibleItem($cartItems)) {
            $this->addError('selectedCouponId', 'Your cart needs at least one item priced Rs. ' . number_format($coupon->min_item_price) . ' or more.');
            return;
        }

        $this->appliedCoupon = $coupon;
        $this->discountAmount = $coupon->calculateDiscount($coupon->eligibleSubtotal($cartItems));
        session()->flash('success', 'Coupon "' . $coupon->code . '" applied successfully!');
    }

    public function removeCoupon()
    {
        $this->appliedCoupon = null;
        $this->discountAmount = 0;
        $this->selectedCouponId = '';
        session()->flash('info', 'Coupon removed.');
    }

    public function removePopup($id)
    {
        $this->cartItem = Cart_items::with('product')->find($id);
    }

    public function deleteItem()
    {
        $this->cartItem->delete();
        return redirect()->route('user.cart')->with('error', 'Product successfully removed from cart');
    }

    public function updatedCartItems($value, $key)
    {
        $itemId = $key;
        $newQuantity = (int) $value;

        if ($newQuantity < 1) {
            $newQuantity = 1;
        }

        $cartItem = Cart_items::with('cart')->find($itemId);
        if (!$cartItem || (int) $cartItem->cart->user_id !== (int) $this->userId) {
            return;
        }

        $product = Product::find($cartItem->product_id);
        if (!$product) {
            return;
        }

        if ($newQuantity > $product->stock) {
            $this->cartItems[$itemId] = $product->stock;
            $this->addError('cartItems.' . $itemId, 'Only ' . $product->stock . ' items available in stock.');
            return;
        }

        $cartItem->quantity = $newQuantity;
        $cartItem->sub_total = $cartItem->price * $newQuantity;
        $cartItem->save();

        $this->cartItems[$itemId] = $newQuantity;
        $this->calculateSubTotal();
    }

    public function incrementQuantity($itemId)
    {
        $cartItem = Cart_items::with('cart')->find($itemId);
        if (!$cartItem || (int) $cartItem->cart->user_id !== (int) $this->userId) {
            return;
        }

        $product = Product::find($cartItem->product_id);
        if (!$product) {
            return;
        }

        $newQuantity = ((int) ($this->cartItems[$itemId] ?? $cartItem->quantity)) + 1;

        if ($newQuantity > $product->stock) {
            $this->addError('cartItems.' . $itemId, 'Only ' . $product->stock . ' items available in stock.');
            $this->cartItems[$itemId] = $product->stock;
            return;
        }

        $cartItem->quantity = $newQuantity;
        $cartItem->sub_total = $cartItem->price * $newQuantity;
        $cartItem->save();

        $this->cartItems[$itemId] = $newQuantity;
        $this->calculateSubTotal();
    }

    public function decrementQuantity($itemId)
    {
        $cartItem = Cart_items::with('cart')->find($itemId);
        if (!$cartItem || (int) $cartItem->cart->user_id !== (int) $this->userId) {
            return;
        }

        $current = (int) ($this->cartItems[$itemId] ?? $cartItem->quantity);
        $newQuantity = max(1, $current - 1);
        $cartItem->quantity = $newQuantity;
        $cartItem->sub_total = $cartItem->price * $newQuantity;
        $cartItem->save();
        $this->cartItems[$itemId] = $newQuantity;
        $this->calculateSubTotal();
    }

    public function calculateSubTotal()
    {
        $carts = ModalCart::where('user_id', $this->userId)
            ->with('cartItems.product')
            ->get();

        $this->subTotal = 0;
        $cartItems = collect();

        foreach ($carts as $cart) {
            foreach ($cart->cartItems as $item) {
                $this->subTotal += $item->sub_total;
                $cartItems->push($item);
            }
        }

        if ($this->appliedCoupon) {
            $coupon = Coupon::find($this->appliedCoupon->id);

            if ($coupon && $coupon->isAvailable() && $this->subTotal >= $coupon->min_order_amount && $coupon->hasEligibleItem($cartItems)) {
                $this->appliedCoupon = $coupon;
                $this->discountAmount = $coupon->calculateDiscount($coupon->eligibleSubtotal($cartItems));
            } else {
                $this->appliedCoupon = null;
                $this->discountAmount = 0;
                $this->selectedCouponId = '';
            }
        }
    }

    public function updatedRedeemPoints()
    {
        $this->calculateWalletDiscount();
    }

    public function calculateWalletDiscount()
    {
        if (!Auth::guard('web')->check()) {
            $this->walletDiscount = 0;
            return;
        }

        $wallet = app(PointsWalletService::class);
        $maxPoints = $wallet->maxRedeemablePoints(Auth::guard('web')->user(), (float) $this->subTotal);
        $this->redeemPoints = min(max((int) $this->redeemPoints, 0), $maxPoints);
        $this->walletDiscount = $wallet->discountForPoints($this->redeemPoints, (float) $this->subTotal);
    }

    public function checkoutSubmit()
    {
        $this->validate([
            'paymentMethod' => "required",
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::guard('web')->user();
            $wallet = app(PointsWalletService::class);
            $cart = ModalCart::where('user_id', $this->userId)->first();
            $cart_items = Cart_items::where('cart_id', $cart->id)->get();

            if ($cart_items->isEmpty()) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Your cart is empty!');
            }

            $coupon = null;
            $couponUsage = null;
            $couponDiscount = 0;

            if ($this->appliedCoupon) {
                $coupon = Coupon::where('id', $this->appliedCoupon->id)->lockForUpdate()->first();
                $couponUsage = CouponUser::where('coupon_id', $this->appliedCoupon->id)
                    ->where('user_id', $this->userId)
                    ->whereNull('used_at')
                    ->lockForUpdate()
                    ->first();

                if (!$coupon || !$couponUsage || !$coupon->isAvailable() || $this->subTotal < $coupon->min_order_amount || !$coupon->hasEligibleItem($cart_items)) {
                    DB::rollBack();
                    return redirect()->route('user.cart')->with('error', 'Selected coupon is no longer valid.');
                }

                $couponDiscount = $coupon->calculateDiscount($coupon->eligibleSubtotal($cart_items));
            }

            $finalTotal = max(0, $this->subTotal - $couponDiscount);

            // Create Main Order
            $order = Order::create([
                'user_id' => $this->userId,
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'name' => $this->userName,
                'email' => $this->userEmail,
                'province' => $this->userProvince,
                'city' => $this->userCity,
                'tole' => $this->userTole,
                'phone' => $this->userPhone,
                'price' => $finalTotal,
                'coupon_id' => $coupon?->id,
                'coupon_discount' => $couponDiscount,
                'payment_status' => 'Pending',
                'order_status' => 'Pending',
                'payment_method' => $this->paymentMethod,
                'quantity' => $cart_items->sum('quantity'),
            ]);

            // Group items by vendor
            $groupedByVendor = $cart_items->groupBy(function ($item) {
                return $item->product->vendor_id;
            });

            // Create vendor orders and order items
            foreach ($groupedByVendor as $vendorId => $items) {
                $vendorSubtotal = $items->sum(fn($i) => $i->price * $i->quantity);
                $quantity = $items->sum('quantity');

                $vendorOrder = VendorOrder::create([
                    'order_id' => $order->id,
                    'vendor_id' => $vendorId,
                    'subtotal' => $vendorSubtotal,
                    'status' => 'Pending',
                    'quantity' => $quantity,
                ]);

                foreach ($items as $item) {
                    Order_item::create([
                        'order_id' => $order->id,
                        'vendor_order_id' => $vendorOrder->id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'total' => $item->price * $item->quantity,
                    ]);

                    // Reduce stock
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->stock -= $item->quantity;
                        $product->save();
                    }
                }
            }

            if ($coupon && $couponUsage) {
                $couponUsage->update([
                    'order_id' => $order->id,
                    'used_at' => now(),
                ]);
                $coupon->increment('used_count');
            }

            // Clear cart
            $cart_items->each->delete();
            $cart->delete();

            DB::commit();

            return redirect()->route('user.cart')->with('success', 'Order Successfully Placed!');

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('user.cart')->with('error', 'Something went wrong: ' . $th->getMessage());
        }
    }

    public function render()
    {
        $cartItems = $this->getCartItems();
        $availableCoupons = CouponUser::where('user_id', $this->userId)
            ->whereNull('used_at')
            ->with('coupon')
            ->get()
            ->pluck('coupon')
            ->filter(fn ($coupon) => $coupon
                && $coupon->isAvailable()
                && $this->subTotal >= $coupon->min_order_amount
                && $coupon->hasEligibleItem($cartItems))
            ->values();

        return view('livewire.user.cart', [
            'carts' => ModalCart::where('user_id', $this->userId)->with('cartItems.product')->get(),
            'availableCoupons' => $availableCoupons,
        ]);
    }
}
