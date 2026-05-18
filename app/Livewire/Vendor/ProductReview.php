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
        $vendorId = Auth::guard('vendor')->user()->id;
        $productIds = Product::where('vendor_id', $vendorId)->pluck('id');

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
