<?php

namespace App\Livewire\User;

use Livewire\Component;
use App\Models\Category as modalCategoery;

class Category extends Component
{
    public function catProduct($id){
        dd($id);
    }
    public function render()
    {
        return view('livewire.user.category',[
            'categories' => modalCategoery::latest()->get()
        ]);
    }
}