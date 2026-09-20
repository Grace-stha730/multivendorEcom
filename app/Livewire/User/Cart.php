<?php

namespace App\Livewire\User;

use App\Rules\PhoneNumber;
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
use App\Models\ProductVariant;
use App\Services\Coupon\CouponService;
use App\Services\Cart\CartPricingService;
use Livewire\Attributes\Title;

#[Title(content: 'Cart')]
#[Layout('components/layouts/user')]
class Cart extends Component
{
    public $userId, $userName, $userEmail, $userProvince, $userCity, $userTole, $userPhone;
    public $cartItems = [];
    public $subTotal = 0;
    public $cartItem;
    public $variantCartItem;
    public array $variantQuantities = [];
    public $couponCartItem;
    public array $vendorCouponSelections = [];
    public array $couponMessages = [];
    public array $vendorCouponDiscounts = [];
    public $paymentMethod;
    public int $checkoutStep = 1;
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
                ->with(['adminCoupon', 'cartItems.product'])
                ->get();

            foreach ($carts as $cart) {
                if ($cart->adminCoupon) {
                    $this->appliedCoupon = $cart->adminCoupon;
                    $this->selectedCouponId = $cart->admin_coupon_id;
                }
                foreach ($cart->cartItems as $item) {
                    $this->cartItems[$item->id] = $item->quantity;
                    $this->subTotal += $this->cartItemSubtotal($item, $item->product);
                    if ($item->coupon_id) {
                        $this->vendorCouponSelections[$item->id] = $item->coupon_id;
                        $this->vendorCouponDiscounts[$item->id] = (float) $item->coupon_discount;
                    }
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
            ->with('product.variants')
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
            ->with('coupon')
            ->first();

        if (!$couponUser || !$couponUser->coupon) {
            $this->addError('selectedCouponId', 'Please collect this coupon before using it.');
            return;
        }

        $coupon = $couponUser->coupon;
        $cartItems = $this->getCartItems();

        if (! $this->adminCouponsForCart($cartItems)->contains('id', $coupon->id)) {
            $this->addError('selectedCouponId', 'This platform coupon is not eligible for an item in your cart.');
            return;
        }

        $eligibleItems = $this->adminEligibleCartItems($coupon, $cartItems);
        $eligibleSubtotal = $coupon->eligibleSubtotal($eligibleItems);

        if ($eligibleSubtotal < $coupon->min_order_amount) {
            $this->addError('selectedCouponId', 'Minimum order amount to apply this coupon is Rs. ' . number_format($coupon->min_order_amount));
            return;
        }

        if (!$coupon->hasEligibleItem($eligibleItems)) {
            $this->addError('selectedCouponId', 'Your cart needs at least one item priced Rs. ' . number_format($coupon->min_item_price) . ' or more.');
            return;
        }

        $this->appliedCoupon = $coupon;
        $this->discountAmount = $coupon->calculateDiscount($eligibleSubtotal);
        ModalCart::where('user_id', $this->userId)->update(['admin_coupon_id' => $coupon->id]);
        session()->flash('success', 'Coupon "' . $coupon->code . '" applied successfully!');
    }

    public function removeCoupon()
    {
        $this->appliedCoupon = null;
        $this->discountAmount = 0;
        $this->selectedCouponId = '';
        ModalCart::where('user_id', $this->userId)->update(['admin_coupon_id' => null]);
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

        $product = Product::with('variants')->find($cartItem->product_id);
        if (!$product) {
            return;
        }

        $stock = $this->variantStock($cartItem, $product);
        if ($newQuantity > $stock) {
            $this->cartItems[$itemId] = $stock;
            $this->addError('cartItems.' . $itemId, 'Only ' . $stock . ' items available in stock.');
            return;
        }

        $cartItem->quantity = $newQuantity;
        if ($this->hasMultipleVariants($cartItem)) { $this->cartItems[$itemId] = $cartItem->quantity; $this->addError("cartItems.$itemId", 'Change quantities per variant using Change variants.'); return; }
        $cartItem->sub_total = $this->cartItemSubtotal($cartItem, $product, $newQuantity);
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

        $product = Product::with('variants')->find($cartItem->product_id);
        if (!$product) {
            return;
        }

        $newQuantity = ((int) ($this->cartItems[$itemId] ?? $cartItem->quantity)) + 1;

        $stock = $this->variantStock($cartItem, $product);
        if ($newQuantity > $stock) {
            $this->addError('cartItems.' . $itemId, 'Only ' . $stock . ' items available in stock.');
            $this->cartItems[$itemId] = $stock;
            return;
        }

        $cartItem->quantity = $newQuantity;
        if ($this->hasMultipleVariants($cartItem)) { $this->addError("cartItems.$itemId", 'Change quantities per variant using Change variants.'); return; }
        $cartItem->sub_total = $this->cartItemSubtotal($cartItem, $product, $newQuantity);
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
        if ($this->hasMultipleVariants($cartItem)) { return; }
        $product = Product::with('variants')->find($cartItem->product_id);
        $cartItem->sub_total = $this->cartItemSubtotal($cartItem, $product, $newQuantity);
        $cartItem->save();
        $this->cartItems[$itemId] = $newQuantity;
        $this->calculateSubTotal();
    }

    public function openVariantModal($itemId)
    {
        $item = Cart_items::with(['cart', 'product.variants'])->findOrFail($itemId);
        abort_unless((int) $item->cart->user_id === (int) $this->userId, 403);
        $this->variantCartItem = $item;
        $existingItems = Cart_items::where('cart_id', $item->cart_id)->where('product_id', $item->product_id)->get();
        $this->variantQuantities = [];
        foreach ($item->product->variants->where('stock', '>', 0) as $variant) {
            $quantity = $existingItems->sum(function ($cartItem) use ($variant) {
                $variants = $cartItem->selected_variants['variants'] ?? [];
                return collect($variants)->where('variant_id', $variant->id)->sum('quantity');
            });
            $this->variantQuantities[$variant->id] = $quantity;
        }
    }

    public function closeVariantModal(): void
    {
        $this->variantCartItem = null;
        $this->variantQuantities = [];
    }

    public function applyVariantQuantities(): void
    {
        $item = Cart_items::with(['cart', 'product.variants'])->findOrFail($this->variantCartItem->id);
        abort_unless((int) $item->cart->user_id === (int) $this->userId, 403);
        $variants = $item->product->variants->where('stock', '>', 0);
        foreach ($variants as $variant) {
            $quantity = max(0, (int) ($this->variantQuantities[$variant->id] ?? 0));
            if ($quantity > $variant->stock) {
                $this->addError("variantQuantities.{$variant->id}", "Only {$variant->stock} units are available.");
                return;
            }
        }

        DB::transaction(function () use ($item, $variants) {
            $selected = [];
            foreach ($variants as $variant) {
                $quantity = max(0, (int) ($this->variantQuantities[$variant->id] ?? 0));
                if ($quantity > 0) $selected[] = ['variant_id'=>(int)$variant->id, 'attribute_name'=>$variant->attribute_name, 'attribute_value'=>$variant->attribute_value, 'quantity'=>$quantity, 'price_extra'=>(float)$variant->price_extra];
            }
            $totalQuantity = collect($selected)->sum('quantity');
            if ($totalQuantity === 0) return;
            $basePrice = $this->discountedBasePrice($item->product);
            $subtotal = app(CartPricingService::class)->lineSubtotal($item->product, ['variants' => $selected], $totalQuantity);
            Cart_items::where('cart_id', $item->cart_id)->where('product_id', $item->product_id)->where('id', '!=', $item->id)->delete();
            $item->update(['quantity'=>$totalQuantity, 'price'=>$basePrice, 'sub_total'=>$subtotal, 'selected_variants'=>['variants'=>$selected]]);
            $this->cartItems[$item->id] = $totalQuantity;
        });
        $this->variantCartItem = null;
        $this->variantQuantities = [];
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
                $this->revalidateVendorCoupon($item);
            }
        }

        if ($this->appliedCoupon) {
            $coupon = Coupon::find($this->appliedCoupon->id);
            $eligibleItems = $coupon ? $this->adminEligibleCartItems($coupon, $cartItems) : collect();
            $eligibleSubtotal = $coupon ? $coupon->eligibleSubtotal($eligibleItems) : 0;

            if ($coupon && $this->adminCouponsForCart($cartItems)->contains('id', $coupon->id) && $eligibleSubtotal >= $coupon->min_order_amount && $coupon->hasEligibleItem($eligibleItems)) {
                $this->appliedCoupon = $coupon;
                $this->discountAmount = $coupon->calculateDiscount($eligibleSubtotal);
            } else {
                $this->appliedCoupon = null;
                $this->discountAmount = 0;
                $this->selectedCouponId = '';
            }
        }
    }

    public function openCouponModal($itemId): void
    {
        $item = Cart_items::with(['cart', 'product'])->findOrFail($itemId);
        abort_unless((int) $item->cart->user_id === (int) $this->userId, 403);
        $this->couponCartItem = $item;
    }

    public function closeCouponModal(): void { $this->couponCartItem = null; }

    public function collectVendorCoupon($couponId): void
    {
        $coupon = $this->vendorCouponsFor($this->couponCartItem->product)->firstWhere('id', $couponId);
        if (! $coupon) { $this->addError('couponModal', 'This coupon is not eligible for this product.'); return; }
        CouponUser::firstOrCreate(['coupon_id' => $coupon->id, 'user_id' => $this->userId], ['collected_at' => now()]);
    }

    public function applyVendorCoupon($couponId): void
    {
        $item = Cart_items::with(['cart', 'product'])->findOrFail($this->couponCartItem->id);
        abort_unless((int) $item->cart->user_id === (int) $this->userId, 403);
        $coupon = $this->vendorCouponsFor($item->product)->firstWhere('id', $couponId);
        $collected = $coupon && CouponUser::where('coupon_id', $coupon->id)->where('user_id', $this->userId)->exists();
        if (! $coupon || ! $collected) { $this->addError('couponModal', 'Collect this eligible coupon before applying it.'); return; }
        $result = app(CouponService::class)->calculateDiscount($coupon, (float) $item->sub_total, Auth::guard('web')->user());
        if (! $result['applied']) { $this->couponMessages[$item->id] = $result['reason']; return; }
        $this->vendorCouponSelections[$item->id] = $coupon->id;
        $this->vendorCouponDiscounts[$item->id] = $result['discount'];
        $item->update(['coupon_id' => $coupon->id, 'coupon_discount' => $result['discount']]);
        $this->couponMessages[$item->id] = null;
        $this->couponCartItem = null;
    }

    public function removeVendorCoupon($itemId): void
    {
        unset($this->vendorCouponSelections[$itemId], $this->vendorCouponDiscounts[$itemId], $this->couponMessages[$itemId]);
        Cart_items::whereKey($itemId)->whereHas('cart', fn ($query) => $query->where('user_id', $this->userId))
            ->update(['coupon_id' => null, 'coupon_discount' => 0]);
    }

    public function vendorCouponsFor(Product $product)
    {
        return app(CouponService::class)->eligibleVendorCouponsForProduct($product);
    }

    private function adminCouponsForCart($cartItems)
    {
        $categoryIds = $cartItems->pluck('product.category_id');

        return app(CouponService::class)
            ->eligibleAdminCouponsForCategories($categoryIds)
            ->filter(fn (Coupon $coupon) => CouponUser::where('coupon_id', $coupon->id)
                ->where('user_id', $this->userId)
                ->exists())
            ->values();
    }

    private function adminEligibleCartItems(Coupon $coupon, $cartItems)
    {
        return $cartItems->filter(fn (Cart_items $item) => (int) $item->product->category_id === (int) $coupon->category_id);
    }

    private function revalidateVendorCoupon(Cart_items $item): void
    {
        $couponId = $this->vendorCouponSelections[$item->id] ?? null;
        if (! $couponId) return;
        $coupon = $this->vendorCouponsFor($item->product)->firstWhere('id', $couponId);
        $result = $coupon ? app(CouponService::class)->calculateDiscount($coupon, (float) $item->sub_total, Auth::guard('web')->user()) : ['applied' => false, 'reason' => 'Coupon is no longer eligible.'];
        if (! $result['applied']) { unset($this->vendorCouponSelections[$item->id], $this->vendorCouponDiscounts[$item->id]); $item->update(['coupon_id' => null, 'coupon_discount' => 0]); $this->couponMessages[$item->id] = $result['reason']; return; }
        $this->vendorCouponDiscounts[$item->id] = $result['discount'];
        $item->update(['coupon_discount' => $result['discount']]);
    }

    private function variantStock(Cart_items $item, Product $product): int
    {
        $selected = $item->selected_variants ?: [];
        if (!empty($selected['variant_id'])) {
            return (int) ($product->variants->firstWhere('id', $selected['variant_id'])?->stock ?? 0);
        }
        foreach ($selected as $name => $value) {
            $variant = $product->variants->first(fn ($v) => $v->attribute_name === $name && $v->attribute_value === $value);
            if ($variant) return (int) $variant->stock;
        }
        return (int) $product->stock;
    }

    private function discountedBasePrice(Product $product): float
    {
        return app(CartPricingService::class)->discountedUnitPrice($product);
    }

    private function cartItemSubtotal(Cart_items $item, Product $product, ?int $quantity = null): float
    {
        return app(CartPricingService::class)->lineSubtotal($product, $item->selected_variants ?: [], $quantity ?? (int) $item->quantity);
    }

    private function hasMultipleVariants(Cart_items $item): bool
    {
        return !empty($item->selected_variants['variants']);
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

    public function updatedPaymentMethod()
    {
        $this->checkoutStep = 1;
    }

    public function proceedToReview()
    {
        $this->validateCheckoutDetails();
        $this->checkoutStep = 2;
    }

    public function returnToCheckoutDetails()
    {
        $this->checkoutStep = 1;
    }

    public function resetCheckout()
    {
        $this->checkoutStep = 1;
    }

    public function checkoutSubmit()
    {
        $this->validateCheckoutDetails();

        if ($this->checkoutStep !== 2) {
            $this->checkoutStep = 2;
            return;
        }

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
                    'coupon_discount' => $couponDiscount,
                'payment_status' => 'Pending',
                'order_status' => 'Pending',
                'payment_method' => $this->paymentMethod,
                'quantity' => $cart_items->sum('quantity'),
            ]);

            // Group items by shop
            $groupedByShop = $cart_items->groupBy(function ($item) {
                return $item->product->shop_id;
            });

            // Create shop orders and order items
            foreach ($groupedByShop as $shopId => $items) {
                $vendorSubtotal = $items->sum(fn($i) => $i->price * $i->quantity);
                $quantity = $items->sum('quantity');

                $vendorOrder = VendorOrder::create([
                    'order_id' => $order->id,
                    'shop_id' => $shopId,
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
                        'total' => $item->price * $item->quantity, 'coupon_id' => $coupon?->id, 'coupon_discount' => $couponDiscount,
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
                $coupon->increment('used_count');
            }

            // Clear cart
            $cart_items->each->delete();
            $cart->delete();

            DB::commit();

            $this->checkoutStep = 1;

            if ($order->payment_method === 'E-Sewa') {
                return redirect()->route('user.payment.esewa', $order->id);
            }

            return redirect()->route('user.cart')->with('success', 'Order Successfully Placed!');

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('user.cart')->with('error', 'Something went wrong: ' . $th->getMessage());
        }
    }

    private function validateCheckoutDetails(): void
    {
        $rules = [
            'userName' => 'required|string|max:120',
            'userEmail' => 'required|email|max:255',
            'userPhone' => ['required', new PhoneNumber()],
            'userProvince' => 'required|string|max:120',
            'userCity' => 'required|string|max:120',
            'userTole' => 'required|string|max:120',
            'paymentMethod' => 'required|in:E-Sewa,Cash',
        ];

        $this->validate($rules);
    }

    public function render()
    {
        $cartItems = $this->getCartItems();
        $availableCoupons = $this->adminCouponsForCart($cartItems)
            ->filter(function (Coupon $coupon) use ($cartItems) {
                $eligibleItems = $this->adminEligibleCartItems($coupon, $cartItems);

                return $coupon->eligibleSubtotal($eligibleItems) >= $coupon->min_order_amount
                    && $coupon->hasEligibleItem($eligibleItems);
            })
            ->values();

        return view('livewire.user.cart', [
            'carts' => ModalCart::where('user_id', $this->userId)->with('cartItems.product.variants', 'cartItems.product.images')->get(),
            'availableCoupons' => $availableCoupons,
        ]);
    }
}
