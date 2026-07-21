<?php

namespace App\Livewire\User;

use App\Models\Cart;
use App\Models\Cart_items;
use App\Models\Product;
use App\Models\productRating;
use App\Models\Wishlist;
use DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Attributes\Title;

#[Title(content: 'Product Detail')]
#[Layout('components/layouts/user')]
class ProductDetail extends Component
{
    public $productId;
    public $product, $averateRate;
    public $mainImage;
    public $quantity = 1;

    // Variants properties
    public $availableVariants = [];
    public $selectedVariants = [];

    public function mount($id)
    {
        $this->productId = $id;
        $this->product = Product::with(['images', 'vendor', 'variants'])->findOrFail($id);
        $this->averateRate = round(productRating::where('product_id', $id)->avg('rating'), 1);

        // Set the first image as main image
        $this->mainImage = $this->product->images->first()->url ?? 'default/product.jpg';

        // Group variants by name
        $this->availableVariants = $this->product->variants->groupBy('attribute_name')->toArray();
        foreach ($this->availableVariants as $name => $values) {
            if (count($values) > 0) {
                $this->selectedVariants[$name] = $values[0]['attribute_value'];
            }
        }
    }

    public function addToCart()
    {
        $this->validate([
            'quantity' => "required|integer|min:1|max:" . $this->product->stock,
        ]);

        if (!Auth::guard('web')->check()) {
            return redirect()->route('user.login')
                ->with('error', 'Please login first to add product to cart.');
        }

        DB::beginTransaction();

        try {
            $userId = Auth::guard('web')->user()->id;
            $cart = Cart::firstOrCreate(['user_id' => $userId]);

            // Calculate variant-inclusive price
            $basePrice = $this->product->price;
            foreach ($this->selectedVariants as $name => $val) {
                if (isset($this->availableVariants[$name])) {
                    foreach ($this->availableVariants[$name] as $var) {
                        if ($var['attribute_value'] === $val) {
                            $basePrice += $var['price_extra'];
                        }
                    }
                }
            }
            $finalPrice = $this->product->discount
                ? $basePrice - ($basePrice * $this->product->discount / 100)
                : $basePrice;

            // Find cart item that has EXACTLY the same selected variants
            $cartItem = Cart_items::where('cart_id', $cart->id)
                ->where('product_id', $this->productId)
                ->get()
                ->first(function ($item) {
                    return $item->selected_variants === $this->selectedVariants;
                });

            if ($cartItem) {
                $cartItem->update([
                    'quantity' => $cartItem->quantity + $this->quantity,
                    'price' => $finalPrice,
                    'sub_total' => $finalPrice * ($cartItem->quantity + $this->quantity),
                ]);

                DB::commit();
                return redirect()->route('product.detail', ['id' => $this->productId])->with('success', 'Quantity updated in cart');
            } else {
                Cart_items::create([
                    'cart_id' => $cart->id,
                    'product_id' => $this->productId,
                    'quantity' => $this->quantity,
                    'price' => $finalPrice,
                    'sub_total' => $finalPrice * $this->quantity,
                    'selected_variants' => $this->selectedVariants,
                ]);

                DB::commit();
                return redirect()->route('product.detail', ['id' => $this->productId])->with('success', 'Product added to cart');
            }


        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Something went wrong. Please try again.');
        }
    }

    public function toggleWishlist()
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('user.login')
                ->with('error', 'Please login first to save items to wishlist.');
        }

        $userId = Auth::guard('web')->id();
        $wishlist = Wishlist::where('user_id', $userId)->where('product_id', $this->productId)->first();

        if ($wishlist) {
            $wishlist->delete();
            session()->flash('success', 'Removed from wishlist.');
        } else {
            Wishlist::create([
                'user_id' => $userId,
                'product_id' => $this->productId,
            ]);
            session()->flash('success', 'Added to wishlist!');
        }
    }

    public function askQuestion()
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('user.login')
                ->with('error', 'Please login first to message the vendor.');
        }

        $userId = Auth::guard('web')->id();

        $conversation = \App\Models\Conversation::firstOrCreate([
            'user_id' => $userId,
            'vendor_id' => $this->product->vendor_id,
            'product_id' => $this->product->id,
        ], [
            'last_message_at' => now(),
        ]);

        return redirect()->route('user.chat', ['c' => $conversation->id]);
    }


    public function render()
    {
        $inWishlist = false;
        if (Auth::guard('web')->check()) {
            $inWishlist = Wishlist::where('user_id', Auth::guard('web')->id())
                ->where('product_id', $this->productId)
                ->exists();
        }

        return view('livewire.user.product-detail', [
            'inWishlist' => $inWishlist,
        ]);
    }
}
