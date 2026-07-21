<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\VendorOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    /**
     * Generate & Display Sales Invoice for Admin
     */
    public function adminInvoice($id)
    {
        $order = Order::with(['user', 'vendorOrders.vendor', 'vendorOrders.items.product'])->findOrFail($id);

        return view('invoice.admin_invoice', compact('order'));
    }

    /**
     * Generate & Display Sales Invoice for Vendor
     */
    public function vendorInvoice($id)
    {
        $vendorOrder = VendorOrder::with(['order', 'vendor', 'items.product'])->findOrFail($id);

        // Ensure vendor accesses only their own order
        if (Auth::guard('vendor')->check() && Auth::guard('vendor')->id() != $vendorOrder->vendor_id) {
            abort(403, 'Unauthorized access to this sales invoice.');
        }

        return view('invoice.vendor_invoice', compact('vendorOrder'));
    }
}
