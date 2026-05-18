<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Product')]
class Products extends Component
{
    public function viewDetail($id){
        
    }
    public function render()
    {
        return view('livewire.admin.products',[
            'products' => Product::with('vendor','firstImage','category')->paginate(30),
        ]);
    }
}
