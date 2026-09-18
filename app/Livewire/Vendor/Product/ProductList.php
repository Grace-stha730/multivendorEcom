<?php

namespace App\Livewire\Vendor\Product;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Livewire;

class ProductList extends Component
{
    public $productIds;
    public function productDetail($id)
    {
        $this->dispatch('getProductId', productId: $id)->to('vendor.product.update-product');
    }

    public function popupFunc($id)
    {
        $this->productIds = $id;
    }

    public function deleteProduct(){
        $product = Product::find( $this->productIds)->delete();
        return redirect()->route('shop-user.product')->with('success','Product deleted successfully');
    }
    public function render()
    {
        return view('livewire.vendor.product.product-list', [
            'products' => Product::where('shop_id', Auth::guard('shop_user')->user()->shop_id)->with('category', 'images')->latest()->paginate(20),
        ]);
    }
}
