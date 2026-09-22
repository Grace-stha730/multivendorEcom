<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Order as modelOrder;

#[Layout('components.layouts.admin')]
#[Title('Order')]
class Order extends Component
{
    use \App\Livewire\Concerns\PaginatesList;
    public function render()
    {
        return view('livewire.admin.order',[
            'orders' => modelOrder::with(['user', 'deliveryProvince', 'deliveryDistrict', 'vendorOrders'])->latest()->paginate(15),
            // Total across every order, not just the current page — orders has no 'subtotal' column, price is the real total.
            'ordersTotal' => modelOrder::sum('price'),
        ]);
    }
}
