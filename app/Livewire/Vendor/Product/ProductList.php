<?php

namespace App\Livewire\Vendor\Product;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Livewire;

class ProductList extends Component
{
    use \App\Livewire\Concerns\AuthorizesPermissions;
    use \App\Livewire\Concerns\PaginatesList;
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
        $this->authorizeShop('product-delete');
        $product = Product::forCurrentShop()->findOrFail($this->productIds)->delete();
        return redirect()->route('shop-user.product')->with('success','Product deleted successfully');
    }
    public function render()
    {
        return view('livewire.vendor.product.product-list', [
            'products' => Product::forCurrentShop()->with('category', 'images')->latest()->paginate(15),
        ]);
    }
}
