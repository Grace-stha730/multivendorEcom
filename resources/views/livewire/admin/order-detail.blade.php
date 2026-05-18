<div class="p-6 bg-gray-100 min-h-screen">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Order Details</h2>
        <a href="{{ route('admin.order') }}"
            class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg text-sm font-medium">
            <i class="fa fa-arrow-left mr-1"></i> Back
        </a>
    </div>

    <!-- Order Info -->
    <div class="bg-white shadow-md rounded-2xl p-6 mb-6">
        <h3 class="text-xl font-semibold mb-4">{{ $order->order_number }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
            <div>
                <p><span class="font-semibold">Customer:</span> {{ $order->name }}</p>
                <p><span class="font-semibold">Email:</span> {{ $order->email }}</p>
                <p><span class="font-semibold">Phone:</span> +977 {{ $order->phone }}</p>
            </div>
            <div>
                <p><span class="font-semibold">Status:</span> {{ $order->order_status }}</p>
                <p><span class="font-semibold">Order Date:</span> {{ $order->created_at->format('j M Y') }}</p>
                <p><span class="font-semibold">Total Amount:</span> Rs. {{ number_format($order->price) }}</p>
            </div>
        </div>
    </div>

    @foreach ($order->vendorOrders as $key => $vendorOrder)
        <!-- Vendor Orders -->
        <div class="space-y-6">
            <!-- Example: Vendor 1 -->
            <div class="bg-white shadow-md rounded-2xl p-6">
                <h4 class="text-lg font-semibold mb-1 text-blue-600">Vendor: {{ $vendorOrder->vendor->shop_name }}</h4>
                <p class="mb-4">
                    <span class="font-semibold text-gray-700">Vendor Order Status:</span>
                    @if ($vendorOrder->status == 'Pending')
                        <span class="bg-orange-500 px-2.5 py-0.5 rounded-xl text-white">
                            {{ $vendorOrder->status }}
                        </span>
                    @endif

                    @if ($vendorOrder->status == 'Processing')
                        <span class="bg-blue-500 px-2.5 py-0.5 rounded-xl text-white">
                            {{ $vendorOrder->status }}
                        </span>
                    @endif

                    @if ($vendorOrder->status == 'Delivered')
                        <span class="bg-green-500 px-2.5 py-0.5 rounded-xl text-white">
                            @if ($vendorOrder->is_received)
                                Received at warehouse
                            @else
                                Dispatched to warehouse
                            @endif
                        </span>
                    @endif

                    @if ($vendorOrder->status == 'Cancelled')
                        <span class="bg-red-500 px-2.5 py-0.5 rounded-xl text-white">
                            {{ $vendorOrder->status }}
                        </span>
                    @endif
                </p>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium text-gray-700 w-[40%]">Product</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-700">Quantity</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-700">Price</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-700">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($vendorOrder->items as $item)
                                <tr class="hover:bg-gray-200 duration-100">
                                    <td class="px-4 py-2 flex items-center gap-3">
                                        <img class="w-14 h-14 rounded-lg object-cover"
                                            src="{{ asset('storage/' . $item->product->firstImage->url) }}"
                                            alt="">

                                        <span>
                                            <a class="font-semibold text-gray-800 hover:text-blue-800 underline decoration-transparent hover:decoration-blue-800 decoration-2 underline-offset-2 transition duration-200"
                                                href="{{ route('admin.product-detail', ['id' => $item->product_id]) }}"
                                                title="show product detail">{{ $item->product->name }}
                                            </a>
                                        </span>


                                    </td>
                                    <td class="px-4 py-2 text-gray-700">{{ $item->quantity }}</td>
                                    <td class="px-4 py-2 text-gray-700">Rs. {{ number_format($item->price) }}</td>
                                    <td class="px-4 py-2 text-gray-700">Rs.
                                        {{ number_format($item->total) }}</td>
                                </tr>
                            @endforeach

                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-4 py-2 font-semibold text-right">Vendor Total:</td>
                                <td class="px-4 py-2 font-semibold">Rs. {{ $vendorOrder->subtotal }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-4">
                    @if ($vendorOrder->status == 'Delivered' && !$vendorOrder->is_received)
                        <button class="bg-green-600 cursor-pointer hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm"
                            wire:click='recievedOrder({{ $vendorOrder->id }})'>
                            Received vendor Order
                        </button>
                    @endif
                </div>
            </div>
    @endforeach


</div>

<!-- Overall Order Actions -->
<div class="mt-6 flex justify-end gap-3">
    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm cursor-pointer">
        Print Invoice
    </button>
    @if ($order->vendorOrders->every(fn($v) => $v->is_received && $order->order_status == 'Warehouse'))
        <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded cursor-pointer"
            wire:click='shipOrder({{ $order->id }})'>
            Ship Order
        </button>
    @endif
    @if ($order->order_status == 'Shipped')
        <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded cursor-pointer"
            wire:click='DelivereOrder({{ $order->id }})'>
            Complete Order
        </button>
    @endif
    <a href="{{ route('admin.order') }}"
        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm cursor-pointer">
        Back to Orders
    </a>
</div>
</div>
