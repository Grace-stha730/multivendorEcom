<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\Order_item;
use App\Models\Product;
use App\Models\VendorOrder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        $products = Product::all();
        $orders = Order::all();
        $vendorOrder = VendorOrder::all();
        $recentOrder = Order::latest()->take(5)->get();
        $report = Order_item::selectRaw('product_id, SUM(quantity) as total_sold, SUM(total) as total_price')
        ->groupBy('product_id')
        ->orderByDesc('total_sold')
            ->orderByDesc('total_price')
            ->with('product') // assuming you have a relation in OrderItem model
            ->get();


            return view('livewire.admin.dashboard', [
                'products' => $products,
                'orders' => $orders,
                'vendorOrder' => $vendorOrder,
                'recentOrder' => $recentOrder,
                'report' => $report,
            ]);
        }
    }
    