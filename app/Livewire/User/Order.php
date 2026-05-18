<?php

namespace App\Livewire\User;

use App\Models\Order_item;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Order as ModalOrder;
use DB;

#[Layout('components/layouts/user')]
class Order extends Component
{
    public $orders, $orderItem;
    public $status = 'All'; // Default filter status

    public function mount()
    {
        $this->loadOrders();
    }

    // Load orders based on selected status
    public function loadOrders()
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('user.login')->with('error', 'Login first');
        }

        $userid = Auth::guard('web')->user()->id;

        $query = ModalOrder::where('user_id', $userid)->with('orderItems', 'orderItems.product', 'user', 'vendorOrders');

        if ($this->status !== 'All') {
            $query->where('order_status', $this->status);
        } 

        $this->orders = $query->latest()->get();
    }

    // Filter orders by status
    public function setStatus($status)
    {
        $this->status = $status;
        $this->loadOrders();
    }

    public function popDeleteOrder($id)
    {
        $this->orderItem = ModalOrder::with('orderItems.product', 'user')->find($id);
    }

    public function deleteOrder()
    {
        DB::beginTransaction();
        try {
            $order = ModalOrder::find($this->orderItem->id);
            $orderItems = Order_item::where('order_id', $order->id)->get();

            if ($order && !$order->is_shipped) {
                foreach ($orderItems as $item) {
                    $product = Product::find($item->product->id);
                    $product->stock += $item->quantity;
                    $product->save();
                }

                $order->orderItems()->delete();
                $order->delete();
                DB::commit();
                session()->flash('success', 'Order Deleted Successfully');
            } else {
                DB::rollBack();
                session()->flash('error', 'Oops! Your order is on its way.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Something went wrong. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.user.order', [
            'orders' => $this->orders,
        ]);
    }
}
