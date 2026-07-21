<?php

namespace App\Livewire\User;

use App\Models\Cart;
use App\Models\Cart_items;
use App\Models\Wishlist as WishlistModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title(content: 'My Wishlist')]
#[Layout('components/layouts/user')]
class Wishlist extends Component
{
    public function removeFromWishlist($wishlistId)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('user.login');
        }

        WishlistModel::where('id', $wishlistId)
            ->where('user_id', Auth::guard('web')->id())
            ->delete();

        session()->flash('success', 'Item removed from wishlist');
    }

    public function moveToCart($wishlistId)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('user.login')->with('error', 'Please login to add product to cart');
        }

        DB::beginTransaction();

        try {
            $wishlistItem = WishlistModel::with('product')
                ->where('id', $wishlistId)
                ->where('user_id', Auth::guard('web')->id())
                ->first();

            if (!$wishlistItem || !$wishlistItem->product) {
                session()->flash('error', 'Product not found');
                return;
            }

            $product = $wishlistItem->product;
            $userId = Auth::guard('web')->id();

            $cart = Cart::firstOrCreate(['user_id' => $userId]);

            $existingCartItem = Cart_items::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->first();

            $price = $product->discount
                ? $product->price - $product->discount_amount
                : $product->price;

            if ($existingCartItem) {
                session()->flash('error', 'This product is already in your cart');
                DB::rollBack();
                return;
            } else {
                Cart_items::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'price' => $price,
                    'sub_total' => $price,
                ]);

                $wishlistItem->delete();

                DB::commit();
                session()->flash('success', 'Product moved to cart!');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Something went wrong. Please try again.');
        }
    }

    public function clearAll()
    {
        if (Auth::guard('web')->check()) {
            WishlistModel::where('user_id', Auth::guard('web')->id())->delete();
            session()->flash('success', 'Wishlist cleared successfully');
        }
    }

    public function render()
    {
        $wishlistItems = collect();

        if (Auth::guard('web')->check()) {
            $wishlistItems = WishlistModel::with(['product', 'product.firstImage', 'product.vendor'])
                ->where('user_id', Auth::guard('web')->id())
                ->latest()
                ->get();
        }

        return view('livewire.user.wishlist', [
            'wishlistItems' => $wishlistItems,
        ]);
    }
}
