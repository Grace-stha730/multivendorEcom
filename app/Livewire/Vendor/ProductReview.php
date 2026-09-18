<?php

namespace App\Livewire\Vendor;

use App\Livewire\User\Review;
use App\Models\Product;
use App\Models\productRating;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProductReview extends Component
{

    public function render()
    {
        $shopId = Auth::guard('shop_user')->user()->shop_id;
        $productIds = Product::where('shop_id', $shopId)->pluck('id');

        // Get all reviews for those products
        $productRatings = ProductRating::with(['product', 'user', 'ratingImages'])
            ->whereIn('product_id', $productIds)
            ->latest()
            ->get();

        return view('livewire.vendor.product-review', [
            'productRatings' => $productRatings
        ]);
    }
}
