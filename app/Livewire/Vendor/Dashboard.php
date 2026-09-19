<?php

namespace App\Livewire\Vendor;

use App\Models\Order_item;
use App\Models\Product;
use App\Models\VendorOrder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function mount()
    {
        if (!Auth::guard('shop_user')->check()) {
            return redirect()->route('shop-user.login')
                ->with('error', 'Please log in first.');
        }

        // dd(Auth::guard('vendor')->user());
    }
    public function render()
    {
        $shopId = Auth::guard('shop_user')->user()->shop_id;
        $products = Product::where('shop_id', $shopId)->get();
        $orders = VendorOrder::where('shop_id', $shopId)->get();
        $recentOrders = VendorOrder::where('shop_id', $shopId)->with('order')->latest()->take(5)->get();
        $report = Order_item::whereIn('vendor_order_id',$orders->pluck('id'))->selectRaw('product_id, SUM(quantity) as total_sold, SUM(total) as total_price')
        ->groupBy('product_id')
        ->orderByDesc('total_sold')
        ->orderByDesc('total_price')
        ->get();

        $lowStockProducts = Product::where('shop_id', $shopId)->where('stock', '<', 5)->get();

        return view('livewire.vendor.dashboard', [
            'products' => $products,
            'orders' => $orders,
            'recentOrders' =>$recentOrders,
            'report' => $report,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }
}
