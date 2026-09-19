<?php

namespace App\Livewire\User;

use App\Models\Product;
use App\Models\productRating;
use App\Services\Catalog\WeightedRatingService;
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
        $this->product = Product::with('images', 'shop')->findOrFail($productId);
        $this->averageRate = app(WeightedRatingService::class)->shopRating($this->product->shop);
    }
    public function render()
    {
        return view('livewire.user.vendor');
    }
}
