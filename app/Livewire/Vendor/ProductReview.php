<?php

namespace App\Livewire\Vendor;

use App\Livewire\User\Review;
use App\Models\Product;
use App\Models\productRating;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class ProductReview extends Component
{
    use \App\Livewire\Concerns\PaginatesList;

    public function render()
    {
        $shopId = Auth::guard('shop_user')->user()->shop_id;
        $productIds = Product::where('shop_id', $shopId)->pluck('id');

        // Get all reviews for those products
        $reviewedProducts = ProductRating::whereIn('product_id', $productIds)
            ->select('product_id')->distinct()->orderByDesc('product_id')->paginate(5);

        $productRatings = ProductRating::with(['product', 'user', 'ratingImages'])
            ->whereIn('product_id', $reviewedProducts->pluck('product_id'))
            ->latest()
            ->get();

        return view('livewire.vendor.product-review', [
            'productRatings' => $productRatings,
            'reviewedProducts' => $reviewedProducts,
        ]);
    }
}
