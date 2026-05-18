<?php 

namespace App\Livewire\Admin;

use App\Models\VendorOrder;
use Livewire\Attributes\Layout;
use App\Models\Order;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Order Detail')]
class OrderDetail extends Component
{

    public function recievedOrder($id){
       $vendorOrder = VendorOrder::findOrFail($id);
        $vendorOrder->update(['is_received' => 1]);
        return redirect()->route('admin.order-detail',['id'=>$vendorOrder->order_id])->with('success','Order Received from vendor');

    }

    public function shipOrder($id){
        $order = Order::find($id);
        $order->update(['order_status' => "Shipped"]);
        return redirect()->route('admin.order-detail',['id'=>$order->id])->with('success','Order Shipped Successfully');
    }

    public function DelivereOrder($id){
        $order = Order::find($id);
        $order->update(['order_status' => 'Delivered']);
        return redirect()->route('admin.order-detail',['id' => $order->id])->with('success','Order delivered to customer');
    }
    public $order;
    public function mount($id){
        $this->order = Order::with('vendorOrders')->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.admin.order-detail',[
            'order' => $this->order,
        ]);
    }
}
