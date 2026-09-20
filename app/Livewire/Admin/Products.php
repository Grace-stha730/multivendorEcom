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
    use \App\Livewire\Concerns\AuthorizesPermissions;
    public function viewDetail($id){
        $this->authorizeAdmin('product-view');
        
    }
    public function render()
    {
        return view('livewire.admin.products',[
            'products' => Product::with('shop', 'firstImage', 'category')->paginate(30),
        ]);
    }
}
