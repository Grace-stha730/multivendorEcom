<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Invoice #{{ $order->order_number }} - Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: white !important;
                color: black !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .invoice-container {
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
            }
            @page {
                size: A4;
                margin: 12mm;
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen text-gray-800 py-8 px-4 font-sans">

    <!-- Action Bar (Hidden when printing) -->
    <div class="max-w-4xl mx-auto mb-6 bg-white p-4 rounded-xl shadow-md flex justify-between items-center no-print">
        <div class="flex items-center gap-2 text-gray-700">
            <i class="fa-solid fa-file-invoice text-blue-600 text-xl"></i>
            <span class="font-semibold text-lg">Sales Invoice (#{{ $order->order_number }})</span>
        </div>
        <div class="flex gap-3">
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-print"></i> Print Sales Invoice
            </button>
            <button onclick="window.close()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-xmark"></i> Close
            </button>
        </div>
    </div>

    <!-- Sales Invoice Printable Document -->
    <div class="max-w-4xl mx-auto bg-white p-8 rounded-2xl shadow-xl border border-gray-200 invoice-container">
        
        <!-- Header -->
        <div class="flex justify-between items-start border-b border-gray-200 pb-6 mb-6">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">MULTI-VENDOR STORE</h1>
                <p class="text-xs text-gray-500 mt-1">E-Commerce Platform & Marketplace</p>
                <p class="text-xs text-gray-500">Kathmandu, Nepal | Support: support@multivendor.com</p>
            </div>
            <div class="text-right">
                <span class="inline-block bg-blue-100 text-blue-800 text-xs font-bold uppercase px-3 py-1 rounded-full mb-2">
                    SALES INVOICE
                </span>
                <h2 class="text-xl font-bold text-gray-800">#INV-{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</h2>
                <p class="text-xs text-gray-500">Order Ref: <span class="font-medium text-gray-700">{{ $order->order_number }}</span></p>
                <p class="text-xs text-gray-500">Date: <span class="font-medium text-gray-700">{{ $order->created_at->format('j M Y, h:i A') }}</span></p>
            </div>
        </div>

        <!-- Billed & Order Details -->
        <div class="grid grid-cols-2 gap-6 mb-8 text-sm">
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                <h3 class="font-bold text-gray-700 uppercase text-xs tracking-wider mb-2 text-blue-600">Customer Details (Billed To)</h3>
                <p class="font-semibold text-gray-900 text-base">{{ $order->name }}</p>
                <p class="text-gray-600"><i class="fa-regular fa-envelope mr-1 text-gray-400"></i>{{ $order->email }}</p>
                <p class="text-gray-600"><i class="fa-solid fa-phone mr-1 text-gray-400"></i>+977 {{ $order->phone }}</p>
                <p class="text-gray-600 mt-1"><i class="fa-solid fa-location-dot mr-1 text-gray-400"></i>{{ $order->tole }}, {{ $order->city }}, {{ $order->province }}</p>
            </div>

            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-gray-700 uppercase text-xs tracking-wider mb-2 text-blue-600">Invoice Information</h3>
                    <div class="space-y-1">
                        <p class="flex justify-between"><span class="text-gray-500">Payment Method:</span> <span class="font-semibold text-gray-800">{{ $order->payment_method ?? 'Cash on Delivery' }}</span></p>
                        <p class="flex justify-between"><span class="text-gray-500">Payment Status:</span> <span class="font-semibold text-green-600">{{ $order->payment_status ?? 'Pending' }}</span></p>
                        <p class="flex justify-between"><span class="text-gray-500">Order Status:</span> <span class="font-semibold text-blue-600">{{ $order->order_status }}</span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div class="overflow-x-auto mb-8">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 font-semibold uppercase text-xs border-b border-gray-300">
                        <th class="py-3 px-4">#</th>
                        <th class="py-3 px-4">Product & Vendor Details</th>
                        <th class="py-3 px-4 text-center">Qty</th>
                        <th class="py-3 px-4 text-right">Unit Price</th>
                        <th class="py-3 px-4 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-gray-700">
                    @php $itemIndex = 1; @endphp
                    @foreach ($order->vendorOrders as $vendorOrder)
                        @foreach ($vendorOrder->items as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium text-gray-500">{{ $itemIndex++ }}</td>
                                <td class="py-3 px-4">
                                    <p class="font-semibold text-gray-900">{{ $item->product->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-blue-600">Vendor: {{ $vendorOrder->vendor->shop_name ?? 'Vendor' }}</p>
                                </td>
                                <td class="py-3 px-4 text-center font-medium">{{ $item->quantity }}</td>
                                <td class="py-3 px-4 text-right">Rs. {{ number_format($item->price) }}</td>
                                <td class="py-3 px-4 text-right font-semibold text-gray-900">Rs. {{ number_format($item->total) }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Invoice Calculation Summary -->
        <div class="flex flex-col md:flex-row justify-between items-start gap-6 border-t border-gray-200 pt-6">
            <div class="text-xs text-gray-500 space-y-1 max-w-md">
                <p class="font-bold text-gray-700 text-sm">Terms & Conditions:</p>
                <p>1. Invoice is computer generated and valid without signature.</p>
                <p>2. Products once sold are subject to platform exchange policy within 7 days.</p>
                <p>3. Keep this invoice for warranty and customer support claims.</p>
            </div>

            <div class="w-full md:w-72 bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-2 text-sm">
                <div class="flex justify-between text-gray-600">
                    <span>Subtotal:</span>
                    <span>Rs. {{ number_format($order->price) }}</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Shipping Fee:</span>
                    <span class="text-green-600 font-medium">Free</span>
                </div>
                <div class="border-t border-gray-300 pt-2 flex justify-between font-bold text-base text-gray-900">
                    <span>Grand Total:</span>
                    <span class="text-blue-700">Rs. {{ number_format($order->price) }}</span>
                </div>
            </div>
        </div>

        <!-- Authorized Signature -->
        <div class="mt-12 pt-8 border-t border-gray-200 flex justify-between items-end">
            <div class="text-xs text-gray-400">
                <p>Multi-Vendor E-Commerce Platform</p>
                <p>Generated on {{ date('Y-m-d H:i:s') }}</p>
            </div>
            <div class="text-center">
                <div class="w-40 border-b border-gray-400 mb-2"></div>
                <p class="text-xs font-bold text-gray-600 uppercase">Authorized Signature</p>
            </div>
        </div>

    </div>

</body>
</html>
