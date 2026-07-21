<?php

namespace App\Livewire\User;

use App\Models\Cart;
use App\Models\Cart_items;
use App\Models\Category;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Product as modalProduct;
use Livewire\Attributes\Title;

#[Title(content: 'Product')]
#[Layout('components/layouts/user')]
class Product extends Component
{
    public $search = "", $category = "";

    public function AddToCart($id)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('user.login')
                ->with('error', 'Please login first to add product to cart.');
        }

        DB::beginTransaction();

        try {
            $product = modalProduct::find($id);
            $userId = Auth::guard('web')->user()->id;
            $cart = Cart::firstOrCreate(['user_id' => $userId]);

            $cartItem = Cart_items::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->first();

            $price = $product->discount
                ? $product->price - $product->discount_amount
                : $product->price;

            if ($cartItem) {
                DB::rollBack();
                return redirect()->route('user.product')->with('error', 'This product is already in cart');
            } else {
                Cart_items::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'price' => $price,
                    'sub_total' => $price,
                ]);

                DB::commit();
                return redirect()->route('user.product')->with('success', 'Product added to cart');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Something went wrong. Please try again.');
        }
    }

    public function toggleWishlist($id)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('user.login')
                ->with('error', 'Please login first to save items to wishlist.');
        }

        $userId = Auth::guard('web')->id();
        $wishlist = Wishlist::where('user_id', $userId)->where('product_id', $id)->first();

        if ($wishlist) {
            $wishlist->delete();
            session()->flash('success', 'Removed from wishlist');
        } else {
            Wishlist::create([
                'user_id' => $userId,
                'product_id' => $id,
            ]);
            session()->flash('success', 'Added to wishlist!');
        }
    }

    public function render()
    {
        $userWishlistProductIds = [];
        if (Auth::guard('web')->check()) {
            $userWishlistProductIds = Wishlist::where('user_id', Auth::guard('web')->id())
                ->pluck('product_id')
                ->toArray();
        }

        $products = modalProduct::where('name', 'like', '%' . $this->search . '%')
            ->when($this->category, function ($query) {
                $query->where('category_id', $this->category);
            })
            ->with(['vendor', 'firstImage'])
            ->latest()->get();

        $categories = Category::all();
        $collections = Auth::guard('web')->check()
            ? ProductCollection::where('user_id', Auth::guard('web')->id())->latest()->get()
            : collect();

        return view('livewire.user.product', [
            'products' => $products,
            'searchGroups' => $searchGroups,
            'categories' => $categories,
            'userWishlistProductIds' => $userWishlistProductIds,
        ]);
    }
}
