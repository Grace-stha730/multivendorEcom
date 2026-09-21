<?php

namespace App\Livewire\Vendor;

use App\Models\VendorOrder;
use Livewire\Component;
use App\Models\Order as modalOrder;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
#[Title(content: 'Order')]
class Order extends Component
{
    use \App\Livewire\Concerns\PaginatesList;
    public function render()
    {
        return view('livewire.vendor.order',[
            'orders' => VendorOrder::forCurrentShop()->with('order','items')->latest()->paginate(15),
            'ordersTotal' => VendorOrder::forCurrentShop()->sum('subtotal'),
        ]);
    }
}
