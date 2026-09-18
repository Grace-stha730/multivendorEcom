<?php

namespace App\Livewire\User;

use Livewire\Component;
use App\Models\Category as modalCategoery;

class Category extends Component
{
    public function catProduct($id)
    {
        return redirect()->route('user.product', ['category' => $id]);
    }
    public function render()
    {
        return view('livewire.user.category',[
            'categories' => modalCategoery::latest()->get()
        ]);
    }
}
