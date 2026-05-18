<?php

namespace App\Livewire\User;

use App\Models\Cart;
use App\Models\Cart_items;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Attributes\Title;

#[Title(content: 'Home')]
#[Layout('components.layouts.user')]
class Home extends Component
{
    public function AddToCart($id)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('user.login')
                ->with('error', 'Please login first to add product to cart.');
        }

        DB::beginTransaction();

        try {
            $product = Product::find($id);
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
                return redirect()->route('home')->with('error', 'This is product is already in cart');
            } else {
                Cart_items::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'price' => $price,
                    'sub_total' => $price,
                ]);

                DB::commit();
                return redirect()->route('home')->with('success', 'Product added to cart');
            }


        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Something went wrong. Please try again.');
        }
    }
    public function render()
    {
        $products = Product::latest()->limit(10)->get();
        return view('livewire.user.home', [
            'products' => $products,
        ]);
    }
}
