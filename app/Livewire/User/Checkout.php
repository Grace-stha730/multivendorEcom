<?php

namespace App\Livewire\User;

use App\Models\Cart;
use App\Models\Cart_items;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\CouponUser;
use App\Models\Order;
use App\Models\Order_item;
use App\Models\Product;
use App\Services\Cart\CartPricingService;
use App\Services\Coupon\CouponService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Checkout')]
#[Layout('components.layouts.user')]
class Checkout extends Component
{
    public array $items = [];

    public array $vendorCoupons = [];

    public ?string $variantItemKey = null;

    public array $variantQuantities = [];

    public $adminCouponId = '';

    public $paymentMethod = 'Cash';

    public $userName;

    public $userEmail;

    public $userProvince;

    public $userCity;

    public $userTole;

    public $userPhone;

    private bool $fromCart = true;

    public function mount($product = null, $quantity = 1, $variants = null): void
    {
        abort_unless(Auth::guard('web')->check(), 403);
        $user = Auth::guard('web')->user();
        foreach (['Name' => 'name', 'Email' => 'email', 'Province' => 'province', 'City' => 'city', 'Tole' => 'tole', 'Phone' => 'phone'] as $property => $column) {
            $this->{'user'.$property} = $user->{$column};
        }
        $this->fromCart = ! $product;
        if ($product) {
            $product = Product::with(['firstImage', 'variants'])->findOrFail($product);
            $selected = json_decode(base64_decode((string) $variants), true) ?: [];
            $this->items[$product->id] = $this->makeItem($product, (int) $quantity, $selected, null);
        } else {
            $cart = Cart::where('user_id', $user->id)->with('adminCoupon')->first();
            if ($cart?->adminCoupon) {
                $this->adminCouponId = $cart->admin_coupon_id;
            }
            Cart_items::where('cart_id', $cart?->id)->with(['product.firstImage', 'product.variants'])->get()
                ->each(function ($cartItem) {
                    $this->items[$cartItem->id] = $this->makeItem($cartItem->product, (int) $cartItem->quantity, $cartItem->selected_variants ?: [], $cartItem->id);
                    if ($cartItem->coupon_id) {
                        $this->vendorCoupons[$cartItem->id] = $cartItem->coupon_id;
                    }
                });
        }
        if (! $this->items) {
            redirect()->route('user.cart')->with('error', 'Your cart is empty.');
        }
    }

    private function makeItem(Product $product, int $quantity, array $variants, ?int $cartId): array
    {
        return ['key' => $cartId ?: 'direct-'.$product->id, 'cart_id' => $cartId, 'product_id' => $product->id, 'name' => $product->name, 'summary' => $product->summary, 'image' => $product->firstImage?->url, 'base_price' => (float) $product->price, 'quantity' => max(1, $quantity), 'selected_variants' => $variants, 'variants' => $product->variants->groupBy('attribute_name')->map(fn ($v) => $v->map(fn ($x) => ['id' => $x->id, 'value' => $x->attribute_value, 'price_extra' => (float) $x->price_extra, 'stock' => (int) $x->stock])->values())->all(), 'category_id' => $product->category_id, 'shop_id' => $product->shop_id];
    }

    public function updatedItems($value, $path): void
    {
        [$key, $field] = explode('.', $path, 2);
        if ($field === 'quantity') {
            $this->setQuantity($key, (int) $value);
        }
        if (str_starts_with($field, 'selected_variants.')) {
            $this->validateItemStock($key);
            $this->persistCartItem($key);
        }
    }

    public function setQuantity($key, int $quantity): void
    {
        $old = (int) $this->items[$key]['quantity'];
        $this->items[$key]['quantity'] = max(1, $quantity);
        if (! $this->validateItemStock($key)) {
            $this->items[$key]['quantity'] = $old;
        }
        $this->persistCartItem($key);
    }

    public function increment($key): void
    {
        $this->setQuantity($key, (int) $this->items[$key]['quantity'] + 1);
    }

    public function decrement($key): void
    {
        $this->setQuantity($key, max(1, (int) $this->items[$key]['quantity'] - 1));
    }

    private function validateItemStock($key): bool
    {
        $stock = $this->stockFor($this->items[$key]);
        if ($this->items[$key]['quantity'] > $stock) {
            $this->addError("items.$key.quantity", "Only $stock available for this selection.");

            return false;
        }

        return true;
    }

    private function discountedBasePrice(array $item): float
    {
        return app(CartPricingService::class)->discountedUnitPrice(Product::findOrFail($item['product_id']));
    }

    private function stockFor(array $item): int
    {
        $structured = $item['selected_variants']['variants'] ?? [];
        if ($structured) {
            return (int) collect($structured)->sum('quantity');
        }

        return (int) Product::find($item['product_id'])->stock;
    }

    private function unitPrice(array $item): float
    {
        return $this->discountedBasePrice($item);
    }

    private function lineSubtotal(array $item): float
    {
        return app(CartPricingService::class)->lineSubtotal(Product::findOrFail($item['product_id']), $item['selected_variants'] ?: [], (int) $item['quantity']);
    }

    private function persistCartItem($key): void
    {
        $item = $this->items[$key];
        if (! $item['cart_id']) {
            return;
        } Cart_items::whereKey($item['cart_id'])->update(['quantity' => $item['quantity'], 'price' => $this->unitPrice($item), 'sub_total' => $this->lineSubtotal($item), 'selected_variants' => $item['selected_variants']]);
    }

    public function selectVendorCoupon($key, $couponId): void
    {
        $coupon = $couponId ? $this->vendorOptions($this->items[$key])->firstWhere('id', $couponId) : null;
        $this->vendorCoupons[$key] = $coupon?->id;
        if ($this->items[$key]['cart_id']) {
            Cart_items::whereKey($this->items[$key]['cart_id'])->update([
                'coupon_id' => $coupon?->id,
                'coupon_discount' => $coupon ? $this->discount($coupon, $this->lineSubtotal($this->items[$key])) : 0,
            ]);
        }
    }

    public function removeVendorCoupon($key): void
    {
        $this->selectVendorCoupon($key, null);
    }

    public function updatedAdminCouponId($couponId): void
    {
        if ($this->fromCart) {
            Cart::where('user_id', Auth::id())->update(['admin_coupon_id' => $couponId ?: null]);
        }
    }

    public function openVariantModal($key): void
    {
        $this->variantItemKey = (string) $key;
        $selected = collect($this->items[$key]['selected_variants']['variants'] ?? [])->keyBy('variant_id');
        $this->variantQuantities = [];
        foreach ($this->items[$key]['variants'] as $variants) {
            foreach ($variants as $variant) {
                $this->variantQuantities[$variant['id']] = (int) ($selected[$variant['id']]['quantity'] ?? 0);
            }
        }
    }

    public function closeVariantModal(): void
    {
        $this->variantItemKey = null;
        $this->variantQuantities = [];
    }

    public function applyVariantQuantities(): void
    {
        $key = $this->variantItemKey;
        if ($key === null || ! isset($this->items[$key])) {
            return;
        }
        $selected = [];
        foreach ($this->items[$key]['variants'] as $attributeName => $variants) {
            foreach ($variants as $variant) {
                $quantity = max(0, (int) ($this->variantQuantities[$variant['id']] ?? 0));
                if ($quantity > $variant['stock']) {
                    $this->addError("variantQuantities.{$variant['id']}", "Only {$variant['stock']} available.");

                    return;
                }
                if ($quantity) {
                    $selected[] = ['variant_id' => (int) $variant['id'], 'attribute_name' => $attributeName, 'attribute_value' => $variant['value'], 'quantity' => $quantity, 'price_extra' => (float) $variant['price_extra']];
                }
            }
        }
        if (! $selected) {
            $this->addError('variantQuantities', 'Select at least one variant.');

            return;
        }
        $this->items[$key]['selected_variants'] = ['variants' => $selected];
        $this->items[$key]['quantity'] = collect($selected)->sum('quantity');
        $this->persistCartItem($key);
        $this->closeVariantModal();
    }

    private function vendorOptions(array $item)
    {
        return app(CouponService::class)->eligibleVendorCouponsForProduct(Product::find($item['product_id']))->filter(fn ($coupon) => CouponUser::where('coupon_id', $coupon->id)->where('user_id', Auth::id())->exists())->values();
    }

    private function adminOptions()
    {
        return app(CouponService::class)->eligibleAdminCouponsForCategories(collect($this->items)->pluck('category_id'))->filter(fn ($coupon) => CouponUser::where('coupon_id', $coupon->id)->where('user_id', Auth::id())->exists())->values();
    }

    private function discount(Coupon $coupon, float $amount): float
    {
        return app(CouponService::class)->calculateDiscount($coupon, $amount, Auth::guard('web')->user())['discount'];
    }

    public function placeOrder()
    {
        $this->validate(['userName' => 'required|max:120', 'userEmail' => 'required|email', 'userProvince' => 'required|max:120', 'userCity' => 'required|max:120', 'userTole' => 'required|max:120', 'userPhone' => 'required|max:30', 'paymentMethod' => 'required|in:Cash,E-Sewa']);
        foreach (array_keys($this->items) as $key) {
            if (! $this->validateItemStock($key)) {
                return;
            }
        }
        DB::transaction(function () {
            $lines = [];
            $totalDiscount = 0;
            $subtotal = 0;
            foreach ($this->items as $key => $item) {
                $amount = $this->lineSubtotal($item);
                $subtotal += $amount;
                $coupon = ! empty($this->vendorCoupons[$key]) ? Coupon::lockForUpdate()->find($this->vendorCoupons[$key]) : null;
                $discount = $coupon && $this->vendorOptions($item)->contains('id', $coupon->id) ? $this->discount($coupon, $amount) : 0;
                $lines[$key] = compact('item', 'amount', 'coupon', 'discount');
                $totalDiscount += $discount;
            }
            $admin = $this->adminCouponId ? Coupon::lockForUpdate()->find($this->adminCouponId) : null;
            $eligible = collect($lines)->filter(fn ($line) => $admin && $line['item']['category_id'] === $admin->category_id);
            $eligibleAmount = $eligible->sum('amount');
            $adminDiscount = $admin && $this->adminOptions()->contains('id', $admin->id) ? $this->discount($admin, $eligibleAmount) : 0;
            foreach ($eligible as $key => $line) {
                $share = $eligibleAmount ? round($adminDiscount * $line['amount'] / $eligibleAmount, 2) : 0;
                $lines[$key]['admin_coupon'] = $admin;
                $lines[$key]['discount'] += $share;
            } $totalDiscount += $adminDiscount;
            $order = Order::create(['user_id' => Auth::id(), 'order_number' => 'ORD-'.strtoupper(uniqid()), 'name' => $this->userName, 'email' => $this->userEmail, 'province' => $this->userProvince, 'city' => $this->userCity, 'tole' => $this->userTole, 'phone' => $this->userPhone, 'price' => max(0, $subtotal - $totalDiscount), 'quantity' => collect($this->items)->sum('quantity'), 'coupon_discount' => $totalDiscount, 'payment_status' => 'Pending', 'order_status' => 'Pending', 'payment_method' => $this->paymentMethod]);
            foreach ($shopLines as $line) {

                $item = $line['item'];

                $vendorCoupon = $line['coupon'] ?? null;
                $adminCoupon = $line['admin_coupon'] ?? null;

                $orderItem = Order_item::create([
                    'order_id' => $order->id,
                    'vendor_order_id' => $vendorOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $this->unitPrice($item),
                    'total' => $line['amount'] - $line['discount'],
                    'coupon_id' => $vendorCoupon?->id ?: $adminCoupon?->id,
                    'coupon_discount' => $line['discount'],
                    'selected_variants' => $item['selected_variants'],
                ]);

                foreach (array_filter([$vendorCoupon, $adminCoupon]) as $coupon) {

                    $discount = $coupon->id === ($vendorCoupon?->id ?? null)
                        ? $this->discount($coupon, $line['amount'])
                        : max(
                            0,
                            $line['discount'] -
                            $this->discount($vendorCoupon ?? $coupon, $line['amount'])
                        );

                    CouponRedemption::create([
                        'coupon_id' => $coupon->id,
                        'user_id' => Auth::id(),
                        'order_id' => $order->id,
                        'order_item_id' => $orderItem->id,
                        'discount_amount' => $discount,
                    ]);

                    $coupon->increment('used_count');
                }

                $product = Product::lockForUpdate()
                    ->with('variants')
                    ->find($item['product_id']);

                $structured = $item['selected_variants']['variants'] ?? [];

                if ($structured) {

                    foreach ($structured as $selection) {
                        $product->variants()
                            ->whereKey($selection['variant_id'])
                            ->decrement('stock', $selection['quantity']);
                    }

                } else {

                    $product->decrement('stock', $item['quantity']);

                }
            }
            if ($this->fromCart) {
                Cart::where('user_id', Auth::id())->get()->each->delete();
            }
        });

        return redirect()->route('user.order')->with('success', 'Order successfully placed.');
    }

    public function render()
    {
        $viewItems = [];
        foreach ($this->items as $key => $item) {
            $item['unit_price'] = $this->unitPrice($item);
            $item['line_subtotal'] = $this->lineSubtotal($item);
            $item['stock'] = $this->stockFor($item);
            $item['vendor_options'] = $this->vendorOptions($item);
            $coupon = ! empty($this->vendorCoupons[$key]) ? $item['vendor_options']->firstWhere('id', $this->vendorCoupons[$key]) : null;
            $item['coupon_discount'] = $coupon ? $this->discount($coupon, $item['line_subtotal']) : 0;
            $item['subtotal'] = max(0, $item['line_subtotal'] - $item['coupon_discount']);
            $viewItems[$key] = $item;
        }

        return view('livewire.user.checkout', ['viewItems' => $viewItems, 'adminOptions' => $this->adminOptions()]);
    }
}
