<?php

namespace App\Livewire\User;

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
#[Layout('components.layouts.user')]
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

    public function removePopup($id)
    {
        $this->cartItem = Cart_items::with('product')->find($id);
    }

    public function deleteItem()
    {
        $this->cartItem->delete();
        return redirect()->route('user.cart')->with('error', 'Product successfully removed from card');
    }


    public function updatedCartItems($value, $key)
    {
        $itemId = $key; // cartItem ID
        $newQuantity = (int) $value;

        // Prevent invalid quantity
        if ($newQuantity < 1) {
            $newQuantity = 1;
        }

        $cartItem = Cart_items::with('cart')->find($itemId);
        if (!$cartItem) {
            return;
        }

        // Ensure this cart belongs to the authenticated user
        if ((int) $cartItem->cart->user_id !== (int) $this->userId) {
            return;
        }

        // Get the related product
        $product = Product::find($cartItem->product_id);

        if (!$product) {
            return;
        }

        // 🧠 Check stock availability
        if ($newQuantity > $product->stock) {
            // Too many items — reset to max available
            $this->cartItems[$itemId] = $product->stock;

            // Add an error message to show on the page
            $this->addError('cartItems.' . $itemId, 'Only ' . $product->stock . ' items available in stock.');

            return;
        }

        // ✅ Update quantity if valid
        $cartItem->quantity = $newQuantity;
        $cartItem->sub_total = $cartItem->price * $newQuantity;
        $cartItem->save();

        // Keep local state in sync
        $this->cartItems[$itemId] = $newQuantity;
        $this->calculateSubTotal();
    }


    public function incrementQuantity($itemId)
    {
        $cartItem = Cart_items::with('cart')->find($itemId);
        if (!$cartItem) {
            return;
        }

        // Ensure this cart belongs to the authenticated user
        if ((int) $cartItem->cart->user_id !== (int) $this->userId) {
            return;
        }

        // Get product
        $product = Product::find($cartItem->product_id);
        if (!$product) {
            return;
        }

        // Calculate new quantity
        $newQuantity = ((int) ($this->cartItems[$itemId] ?? $cartItem->quantity)) + 1;

        // 🧠 Check if exceeding available stock
        if ($newQuantity > $product->stock) {
            // Prevent going beyond stock
            $this->addError('cartItems.' . $itemId, 'Only ' . $product->stock . ' items available in stock.');
            $this->cartItems[$itemId] = $product->stock;
            return;
        }

        // ✅ Update quantity in DB
        $cartItem->quantity = $newQuantity;
        $cartItem->sub_total = $cartItem->price * $newQuantity;
        $cartItem->save();

        // Keep local state synced
        $this->cartItems[$itemId] = $newQuantity;
        $this->calculateSubTotal();
    }


    public function decrementQuantity($itemId)
    {
        $cartItem = Cart_items::with('cart')->find($itemId);
        if (!$cartItem) {
            return;
        }
        if ((int) $cartItem->cart->user_id !== (int) $this->userId) {
            return;
        }

        $current = (int) ($this->cartItems[$itemId] ?? $cartItem->quantity);
        $newQuantity = max(1, $current - 1);
        $cartItem->quantity = $newQuantity;
        $cartItem->quantity = $newQuantity;
        $cartItem->sub_total = $cartItem->price * $newQuantity;
        $cartItem->save();
        $this->cartItems[$itemId] = $newQuantity;
        $this->calculateSubTotal();
    }

    public function calculateSubTotal()
    {
        // Fetch all cart items for the authenticated user
        $carts = ModalCart::where('user_id', $this->userId)
            ->with('cartItems')
            ->get();

        $this->subTotal = $carts->flatMap->cartItems->sum('sub_total');
        $this->calculateWalletDiscount();

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
            'paymentMethod'=>"required",
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

            $this->calculateSubTotal();
            $pointsToRedeem = min(
                max((int) $this->redeemPoints, 0),
                $wallet->maxRedeemablePoints($user, (float) $this->subTotal)
            );
            $walletDiscount = $wallet->discountForPoints($pointsToRedeem, (float) $this->subTotal);
            $payableTotal = max(0, $this->subTotal - $walletDiscount);

            // ✅ 1. Create Main Order
            $order = Order::create([
                'user_id' => $this->userId,
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'name' => $this->userName,
                'email' => $this->userEmail,
                'province' => $this->userProvince,
                'city' => $this->userCity,
                'tole' => $this->userTole,
                'phone' => $this->userPhone,
                'price' => $payableTotal,
                'wallet_discount' => $walletDiscount,
                'redeemed_points' => $pointsToRedeem,
                'payment_status' => 'Pending',
                'order_status' => 'Pending',
                'payment_method' => $this->paymentMethod,
                'quantity' => $cart_items->sum('quantity'),
            ]);

            // ✅ 2. Group items by vendor
            $groupedByVendor = $cart_items->groupBy(function ($item) {
                return $item->product->vendor_id; // assuming relation or you can query
            });

            // ✅ 3. Create vendor orders and order items
            foreach ($groupedByVendor as $vendorId => $items) {
                $subtotal = $items->sum(fn($i) => $i->price * $i->quantity);
                $quantity = $items->sum('quantity');

                $vendorOrder = VendorOrder::create([
                    'order_id' => $order->id,
                    'vendor_id' => $vendorId,
                    'subtotal' => $subtotal,
                    'status' => 'Pending',
                    'quantity' => $quantity,
                ]);

                foreach ($items as $item) {
                    Order_item::create([
                        'order_id' => $order->id,
                        'vendor_order_id' => $vendorOrder->id, // 🔥 link vendor order
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

            $wallet->redeem($user, $order, $pointsToRedeem, (float) $this->subTotal);
            $wallet->awardForOrder($user, $order, (float) $payableTotal);

            // ✅ 4. Clear cart
            $cart_items->each->delete();
            $cart->delete();

            DB::commit();

            return redirect()->route('user.cart')->with('success', 'Order Successfully Placed');

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('user.cart')->with('error', 'Something went wrong: ' . $th->getMessage());
        }
    }



    public function render()
    {
        return view('livewire.user.cart', [
            'carts' => ModalCart::where('user_id', $this->userId)->with('cartItems.product') // ✅ include product info
                ->get(),
        ]);
    }
}
