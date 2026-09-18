<?php

namespace App\Livewire\Vendor;

use App\Models\VendorOrder;
use Livewire\Component;
use App\Models\Order as modalOrder;
use Livewire\Attributes\Title;

#[Title(content: 'Order')]
class Order extends Component
{
    public $orders;


    public function mount()
    {
        $shopId = auth('shop_user')->user()->shop_id;

       $this->orders = VendorOrder::where('shop_id', $shopId)->with('order','items')->latest()->get();
    }
    public function render()
    {
        return view('livewire.vendor.order',[
            'orders' => $this->orders,
        ]);
    }
}
