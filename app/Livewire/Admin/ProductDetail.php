<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Product Detail')]
class ProductDetail extends Component
{
    public $productId;
    public function mount($id){
        $this->productId = Product::with('vendor','images','category','firstImage')->findOrFail($id);
    }
    public function render()
    {
        return view('livewire.admin.product-detail',[
            'product' => $this->productId,
        ]);
    }
}
