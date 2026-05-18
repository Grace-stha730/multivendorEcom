<?php

namespace App\Livewire\User;

use App\Models\Cart;
use App\Models\Cart_items;
use App\Models\Product;
use App\Models\productRating;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.user')]
class VendorInfo extends Component
{
    public $vendorId, $vendor, $averageRate;
    public function mount($id){
        $this->vendorId = $id;
        $this->vendor = Vendor::with('products')->findOrFail($id);
        $productIds = Product::where('vendor_id',$id)->pluck('id');
        $this->averageRate = round(productRating::whereIn('product_id',$productIds)->avg('rating'),1);
        
        
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

            if ($cartItem) {
                DB::rollBack();
                return redirect()->route('user.product')->with('error', 'This is product is already in cart');
            } else {
                Cart_items::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'price' => $price,
                    'sub_total' => $price,
                ]);

                DB::commit();
                return redirect()->route('user.vendorInfo',['id' => $this->vendorId])->with('success', 'Product added to cart');
            }


        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('user.vendorInfo',['id' => $this->vendorId])->with('error','Something went worng'. $e->getMessage());
        }
    }
    public function render()
    {
        return view('livewire.user.vendor-info');
    }
}
