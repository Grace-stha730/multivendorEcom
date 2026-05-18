<?php

namespace App\Livewire\Vendor;

use App\Models\Order;
use App\Models\VendorOrder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Title;

#[Title(content: 'Order Detail')]
class OrderDetail extends Component
{
    public $vendorOrder, $vendorId;
    public function mount($id)
    {
        $this->vendorId = $id;
        $this->vendorOrder = VendorOrder::with('order', 'items')->findOrFail($id);
        // dd($this->vendorOrder);
    }

    public function receivedOrder()
    {
        DB::beginTransaction();
        try {
            // update the current vendor order record
            $order = Order::find($this->vendorOrder->order_id);
            if ($this->vendorOrder->status == 'Pending') {
                $this->vendorOrder->update([
                    'status' => 'Processing',
                ]);
                $order->update(['order_status' => 'Processing']);
                DB::commit();
                return redirect()->route('vendor.orderDetail', ['id' => $this->vendorId])->with('success', 'Order updated to Processing.');
            } elseif ($this->vendorOrder->status == 'Processing') {
                $this->vendorOrder->update([
                    'status' => 'Delivered',
                ]);
                $order->update([
                    'order_status' => 'Warehouse',
                    'is_shipped' => 1,
                ]);
                DB::commit();
                return redirect()->route('vendor.orderDetail', ['id' => $this->vendorId])->with('success', 'Order updated to Delivered.');
            }
            // $order->save();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('vendor.orderDetail', ['id' => $this->vendorId])->with('error', 'Something went wrong: ' . $e->getMessage());
        }

    }

    public function cancelOrder()
    {
        // $order = Order::find($this->vendorOrder->order_id);
        DB::beginTransaction();
        try {
            if ($this->vendorOrder->status != 'Cancelled') {
                $this->vendorOrder->update([
                    'status' => 'Cancelled',
                ]);
                DB::commit();
                return redirect()->route('vendor.orderDetail', ['id' => $this->vendorId])->with('success', 'Order has been cancelled');
            } else {
                DB::rollBack();
                return redirect()->route('vendor.orderDetail', ['id' => $this->vendorId])->with('success', 'Order has been cancelled');

            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('vendor.orderDetail')->with('error', 'Some error occur' . $e->getMessage());
        }

    }

    public function pendingOrder()
    {
        // $order = Order::find($this->vendorOrder->order_id);
        DB::beginTransaction();
        try {
            if ($this->vendorOrder->status != 'Pending') {
                $this->vendorOrder->update([
                    'status' => 'Pending',
                ]);
                DB::commit();
                return redirect()->route('vendor.orderDetail', ['id' => $this->vendorId])->with('success', 'Order has been cancelled');
            } else {
                DB::rollBack();
                return redirect()->route('vendor.orderDetail', ['id' => $this->vendorId])->with('success', 'Order reset to pending');

            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('vendor.orderDetail')->with('error', 'Some error occur' . $e->getMessage());
        }

    }

    public function render()
    {
        return view('livewire.vendor.order-detail', [
            'vendorOrder' => $this->vendorOrder,
        ]);
    }
}
