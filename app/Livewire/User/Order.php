<?php

namespace App\Livewire\User;

use App\Models\Order_item;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Order as ModalOrder;
use DB;

#[Layout('components/layouts/user')]
class Order extends Component
{
    use \App\Livewire\Concerns\PaginatesList;
    public $orderItem;
    public $status = 'All'; // Default filter status

    public function mount()
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('user.login')->with('error', 'Login first');
        }
    }

    // Orders for the selected status, one page at a time
    private function ordersQuery()
    {
        $userid = Auth::guard('web')->user()->id;

        $query = ModalOrder::where('user_id', $userid)->with('orderItems', 'orderItems.product', 'user', 'vendorOrders');

        if ($this->status !== 'All') {
            $query->where('order_status', $this->status);
        } 

        return $query->latest();
    }

    // Filter orders by status
    public function setStatus($status)
    {
        $this->status = $status;
        $this->resetPage();
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
            $orderItems = Order_item::where('order_id', $order->id)->with('vendorOrder')->get();

            if ($order && !$order->is_shipped) {
                foreach ($orderItems as $item) {
                    // A vendor may have already cancelled their part of this order, which already
                    // restored this item's stock — restoring it again here would double-count it.
                    if ($item->vendorOrder && $item->vendorOrder->status === 'Cancelled') {
                        continue;
                    }
                    $this->restoreStock($item);
                }

                $order->orderItems()->delete();
                $order->delete();
                DB::commit();
                return redirect()->route('user.cart')->with('success', 'Order cancelled successfully. Your cart is ready for another order.');
            } else {
                DB::rollBack();
                session()->flash('error', 'Oops! Your order is on its way.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Mirrors Checkout::placeOrder()'s decrement: a line placed with structured variants only
     * ever decremented ProductVariant.stock, never Product.stock, so it must be restored the
     * same way — otherwise cancelling permanently loses the variant's stock while incorrectly
     * inflating a product-level count that was never actually reduced.
     */
    private function restoreStock(Order_item $item): void
    {
        $structured = $item->selected_variants['variants'] ?? [];
        if ($structured) {
            foreach ($structured as $selection) {
                ProductVariant::whereKey($selection['variant_id'])->increment('stock', $selection['quantity']);
            }
            return;
        }

        $product = Product::find($item->product_id);
        $product?->increment('stock', $item->quantity);
    }

    public function startChatWithShopUser($shopUserId, $productId = null)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('user.login')->with('error', 'Login first');
        }

        $userId = Auth::guard('web')->id();

        $conversation = \App\Models\Conversation::firstOrCreate([
            'user_id' => $userId,
            'shop_user_id' => $shopUserId,
            'product_id' => $productId,
        ], [
            'shop_id' => \App\Models\ShopUser::find($shopUserId)?->shop_id,
            'last_message_at' => now(),
        ]);

        return redirect()->route('user.chat', ['c' => $conversation->id]);
    }

    public function render()
    {
        return view('livewire.user.order', [
            'orders' => $this->ordersQuery()->paginate(8),
        ]);
    }
}
