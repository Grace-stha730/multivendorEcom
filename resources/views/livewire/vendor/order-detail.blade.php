<section class="bg-gray-100 min-h-screen py-10">
    <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-md p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold">Order Details</h2>
            <span class="text-gray-500 text-sm">{{ $vendorOrder->order->order_number }}</span>
        </div>

        <!-- Customer & Order Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <h3 class="font-semibold">Customer</h3>
                <p>{{ $vendorOrder->order->name }}</p>
                <p>{{ $vendorOrder->order->email }}</p>
                <p>+977 {{ $vendorOrder->order->phone }}</p>
            </div>
            <div>
                <h3 class="font-semibold">Order Info</h3>
                <p>Date: {{ $vendorOrder->created_at->format('Y-m-d') }}</p>
                <p>Time: {{ $vendorOrder->created_at->format('H:i') }}</p>
                <p>
                    Status:
                    @if ($vendorOrder->status == 'Pending')
                        <span class="bg-orange-500 px-2 rounded-xl text-white">{{ $vendorOrder->status }}</span>
                    @elseif($vendorOrder->status == 'Processing')
                        <span class="bg-blue-500 px-2 rounded-xl text-white">{{ $vendorOrder->status }}</span>
                    @elseif($vendorOrder->status == 'Delivered')
                        @if ($vendorOrder->is_received)
                            <span class="bg-green-500 px-2 rounded-xl text-white">{{ $vendorOrder->status }}</span>
                            @else
                            <span class="bg-orange-500 px-2 rounded-xl text-white">Dispatched</span>
                        @endif
                    @elseif($vendorOrder->status == 'Cancelled')
                        <span class="bg-red-500 px-2 rounded-xl text-white">{{ $vendorOrder->status }}</span>
                    @endif
                </p>
            </div>
        </div>

        <!-- Items Table -->
        <div class="overflow-x-auto mb-6">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">Item</th>
                        <th class="px-4 py-3 text-center">Quantity</th>
                        <th class="px-4 py-3 text-right">Price</th>
                        <th class="px-4 py-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($vendorOrder->items as $idx => $item)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3">{{ $item->product->name }}</td>
                            <td class="px-4 py-3 text-center">{{ $item->quantity }}</td>
                            <td class="px-4 py-3 text-right">Rs. {{ number_format($item->price) }}</td>
                            <td class="px-4 py-3 text-right">Rs. {{ number_format($item->total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-100 font-semibold">
                        <td colspan="4" class="px-4 py-3 text-right">Total:</td>
                        <td class="px-4 py-3 text-right">Rs. {{ number_format($vendorOrder->subtotal) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-2">
            @if ($vendorOrder->status == 'Pending')
                <button
                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition cursor-pointer"
                    wire:click='receivedOrder'>
                    Receive Order
                </button>
                <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition cursor-pointer"
                    wire:click='cancelOrder'>
                    Cancel Order
                </button>
            @elseif($vendorOrder->status == 'Processing')
                <button
                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition cursor-pointer"
                    wire:click='receivedOrder'>
                    Deliver Order
                </button>

                <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition cursor-pointer"
                    wire:click='cancelOrder'>
                    Cancel Order
                </button>
            @elseif($vendorOrder->status == 'Cancelled')
                <button class="bg-orange-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition cursor-pointer"
                    wire:click='pendingOrder'>
                    Reset to pending
                </button>
            @endif
        </div>
    </div>
</section>
