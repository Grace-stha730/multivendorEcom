<?php

namespace App\Livewire\User;

use App\Models\Product;
use App\Models\productRating;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\ThirdPartyUpgradeNotice;

#[Layout('components/layouts/user')]
class Vendor extends Component
{
    public $productId, $product, $averageRate;
    public function mount($productId)
    {
        $this->productId = $productId;
        $this->product = Product::with('images', 'vendor')->findOrFail($productId);
        // If you also want vendor's average rating across all products:
        $vendorId = $this->product->vendor->id;
        $productIds = Product::where('vendor_id', $vendorId)->pluck('id');
        $this->averageRate = round(
            ProductRating::whereIn('product_id', $productIds)->avg('rating'),
            1
        );
    }
    public function render()
    {
        return view('livewire.user.vendor');
    }
}
