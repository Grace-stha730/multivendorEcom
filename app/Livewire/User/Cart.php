<?php

namespace App\Livewire\User;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Order_item;
use App\Models\VendorOrder;
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

    // Coupon Properties
    public $couponCode = '';
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

            $carts = ModalCart::where('user_id', $this->userId)
                ->with('cartItems.product')
                ->get();

            foreach ($carts as $cart) {
                foreach ($cart->cartItems as $item) {
                    $this->cartItems[$item->id] = $item->quantity;
                    $this->subTotal += $item->price * $item->quantity;
                }
            }
        } else {
            return redirect()->route('user.login')->with('error', 'Login to access this page');
        }
    }

    public function getVendorTotals()
    {
        $vendorTotals = [];
        $carts = ModalCart::where('user_id', $this->userId)
            ->with('cartItems.product')
            ->get();

        foreach ($carts as $cart) {
            foreach ($cart->cartItems as $item) {
                if ($item->product) {
                    $vendorId = $item->product->vendor_id;
                    $vendorTotals[$vendorId] = ($vendorTotals[$vendorId] ?? 0) + $item->sub_total;
                }
            }
        }
        return $vendorTotals;
    }

    public function applyCoupon()
    {
        if (empty(trim($this->couponCode))) {
            $this->addError('couponCode', 'Please enter a valid coupon code.');
            return;
        }

        $coupon = Coupon::where('code', strtoupper(trim($this->couponCode)))->first();

        if (!$coupon) {
            $this->addError('couponCode', 'Invalid coupon code.');
            return;
        }

        if (!$coupon->is_active) {
            $this->addError('couponCode', 'This coupon is inactive.');
            return;
        }

        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            $this->addError('couponCode', 'This coupon is not active yet.');
            return;
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            $this->addError('couponCode', 'This coupon has expired.');
            return;
        }

        $vendorTotals = $this->getVendorTotals();

        if ($coupon->vendor_id) {
            if (!isset($vendorTotals[$coupon->vendor_id])) {
                $this->addError('couponCode', 'This coupon is only valid for items from a specific store, which is not in your cart.');
                return;
            }

            $vendorSubtotal = $vendorTotals[$coupon->vendor_id];

            if ($vendorSubtotal < $coupon->min_order_amount) {
                $this->addError('couponCode', 'Minimum order amount from this store to apply the coupon is Rs. ' . number_format($coupon->min_order_amount));
                return;
            }

            $this->appliedCoupon = $coupon;
            $this->discountAmount = $coupon->calculateDiscount($vendorSubtotal);
        } else {
            if ($this->subTotal < $coupon->min_order_amount) {
                $this->addError('couponCode', 'Minimum order amount to apply the coupon is Rs. ' . number_format($coupon->min_order_amount));
                return;
            }

            $this->appliedCoupon = $coupon;
            $this->discountAmount = $coupon->calculateDiscount($this->subTotal);
        }

        session()->flash('success', 'Coupon "' . $coupon->code . '" applied successfully!');
    }

    public function removeCoupon()
    {
        $this->appliedCoupon = null;
        $this->discountAmount = 0;
        $this->couponCode = '';
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
        $vendorTotals = [];

        foreach ($carts as $cart) {
            foreach ($cart->cartItems as $item) {
                $this->subTotal += $item->sub_total;
                if ($item->product) {
                    $vendorId = $item->product->vendor_id;
                    $vendorTotals[$vendorId] = ($vendorTotals[$vendorId] ?? 0) + $item->sub_total;
                }
            }
        }

        if ($this->appliedCoupon) {
            $coupon = $this->appliedCoupon;

            // Re-validate coupon
            $isValid = true;
            if (!$coupon->is_active) $isValid = false;
            if ($coupon->starts_at && $coupon->starts_at->isFuture()) $isValid = false;
            if ($coupon->expires_at && $coupon->expires_at->isPast()) $isValid = false;

            if ($isValid) {
                if ($coupon->vendor_id) {
                    if (isset($vendorTotals[$coupon->vendor_id]) && $vendorTotals[$coupon->vendor_id] >= $coupon->min_order_amount) {
                        $this->discountAmount = $coupon->calculateDiscount($vendorTotals[$coupon->vendor_id]);
                    } else {
                        $this->appliedCoupon = null;
                        $this->discountAmount = 0;
                    }
                } else {
                    if ($this->subTotal >= $coupon->min_order_amount) {
                        $this->discountAmount = $coupon->calculateDiscount($this->subTotal);
                    } else {
                        $this->appliedCoupon = null;
                        $this->discountAmount = 0;
                    }
                }
            } else {
                $this->appliedCoupon = null;
                $this->discountAmount = 0;
            }
        }
    }

    public function checkoutSubmit()
    {
        $this->validate([
            'paymentMethod' => "required",
        ]);

        DB::beginTransaction();

        try {
            $cart = ModalCart::where('user_id', $this->userId)->first();
            $cart_items = Cart_items::where('cart_id', $cart->id)->get();

            if ($cart_items->isEmpty()) {
                return redirect()->back()->with('error', 'Your cart is empty!');
            }

            $finalTotal = max(0, $this->subTotal - $this->discountAmount);

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
        return view('livewire.user.cart', [
            'carts' => ModalCart::where('user_id', $this->userId)->with('cartItems.product')->get(),
        ]);
    }
}
