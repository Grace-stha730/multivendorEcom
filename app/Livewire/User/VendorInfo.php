<?php

namespace App\Livewire\User;

use App\Models\Cart;
use App\Models\Cart_items;
use App\Models\Product;
use App\Models\productRating;
use App\Models\Shop;
use App\Services\Catalog\WeightedRatingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.user')]
class VendorInfo extends Component
{
    public $shopId, $shop;
    public function mount($id){
        $this->shopId = $id;
        $this->shop = Shop::with('products')->findOrFail($id);
        
        
    }

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

            if ($product->stock < 1) {
                DB::rollBack();
                return redirect()->route('user.shop', ['id' => $this->shopId])->with('error', 'This product is out of stock');
            } elseif ($cartItem) {
                DB::rollBack();
                return redirect()->route('user.shop', ['id' => $this->shopId])->with('error', 'This product is already in cart');
            } else {
                Cart_items::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'price' => $price,
                    'sub_total' => $price,
                ]);

                DB::commit();
                return redirect()->route('user.shop',['id' => $this->shopId])->with('success', 'Product added to cart');
            }


        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('user.shop',['id' => $this->shopId])->with('error','Something went wrong'. $e->getMessage());
        }
    }
    public function startChat()
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('user.login')->with('error', 'Please login first to chat with the vendor.');
        }

        $userId = Auth::guard('web')->id();

        $conversation = \App\Models\Conversation::firstOrCreate([
            'user_id' => $userId,
            'shop_user_id' => $this->shop->shopUsers()->value('id'),
        ], [
            'shop_id' => $this->shop->id,
            'last_message_at' => now(),
        ]);

        return redirect()->route('user.chat', ['c' => $conversation->id]);
    }

    public function render(WeightedRatingService $ratingService)
    {
        return view('livewire.user.vendor-info', [
            'averageRate' => $ratingService->shopRating($this->shop),
        ]);
    }
}
