<?php

namespace App\Livewire\User;

use App\Models\Product;
use App\Models\productRating;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components/layouts/user')]
class ViewReview extends Component
{
    public $productId, $reviews, $averageRate;
    public function mount($productId){
        $this->productId = $productId;
        $this->reviews = productRating::where('product_id',$productId)->with('user','ratingImages')->get();
     
    }
    public function render()
    {
        return view('livewire.user.view-review',[
            'reviews' => $this->reviews,
        ]);
    }
}
