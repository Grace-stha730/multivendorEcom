<?php

namespace App\Livewire\User;

use App\Models\Cart;
use App\Models\Cart_items;
use App\Models\Product;
use App\Models\productRating;
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
    public $product,$averateRate;
    public $mainImage;
    public $quantity = 1;


    public function mount($id)
    {
        $this->productId = $id;
        $this->product = Product::with('images','vendor')->findOrFail($id);
        $this->averateRate = round(productRating::where('product_id', $id)->avg('rating'), 1);

        // Set the first image as main image
        $this->mainImage = $this->product->images->first()->url ?? 'default/product.jpg';
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

            $cartItem = Cart_items::where('cart_id', $cart->id)
                ->where('product_id', $this->productId)
                ->first();

            $price = $this->product->discount
                ? $this->product->price - $this->product->discount_amount
                : $this->product->price;

            if ($cartItem) {
                $cartItem->update([
                    'quantity' => $this->quantity,
                    'price' => $price,
                    'sub_total' => $price * $this->quantity,
                ]);

                DB::commit();
                return redirect()->route('product.detail', ['id' => $this->productId])->with('success', 'Quantity update Successfully');
            } else {
                Cart_items::create([
                    'cart_id' => $cart->id,
                    'product_id' => $this->productId,
                    'quantity' => $this->quantity,
                    'price' => $price,
                    'sub_total' => $price * $this->quantity,
                ]);

                DB::commit();
                return redirect()->route('product.detail', ['id' => $this->productId])->with('success', 'Product added to cart');
            }


        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Something went wrong. Please try again.');
        }
    }


    public function render()
    {
        
        return view('livewire.user.product-detail');
    }
}
