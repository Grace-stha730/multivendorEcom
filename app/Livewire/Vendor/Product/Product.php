<?php

namespace App\Livewire\Vendor\Product;

use App\Models\Category;
use Livewire\Component;
use Livewire\Attributes\Title;

#[Title(content: 'Product')]
class Product extends Component
{
    public function render()
    {
        return view('livewire.vendor.product.product', );
    }
}
