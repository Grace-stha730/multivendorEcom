<section class="bg-gray-100 min-h-screen py-10">
    <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-md p-6">
        <h2 class="text-3xl font-semibold mb-6 flex items-center justify-between">
            🛍️ Vendor Orders
            <span class="text-sm text-gray-500">Rs. {{ number_format($orders->sum('subtotal')) }}</span>
        </h2>

        <!-- Orders Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">Order ID</th>
                        <th class="px-4 py-3 text-left">Customer</th>
                        <th class="px-4 py-3 text-left">Items</th>
                        <th class="px-4 py-3 text-left">Total</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Order Date</th>
                        <th class="px-4 py-3 text-left">Order Time</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <!-- Single Order Row -->
                    @foreach ($orders as $idx => $order)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 font-medium text-gray-700">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3 font-semibold text-blue-600">{{ $order->order->order_number }}</td>
                            <td class="px-4 py-3">{{ $order->order->name }}</td>
                            <td class="px-4 py-3">{{ $order->items->count() }}</td>
                            <td class="px-4 py-3 font-semibold">Rs. {{ number_format($order->subtotal) }}</td>
                            <td class="px-4 py-3">
                                @if ($order->status == 'Pending')
                                    <span class="bg-orange-500 px-2 rounded-xl text-white">{{ $order->status }}</span>
                                @elseif($order->status == 'Processing')
                                    <span class="bg-blue-500 px-2 rounded-xl text-white">{{ $order->status }}</span>
                                @elseif($order->status == 'Delivered')
                                    @if ($order->is_received)
                                        <span
                                            class="bg-green-500 px-2 rounded-xl text-white">{{ $order->status }}</span>
                                    @else
                                        <span
                                            class="bg-orange-500 px-2 rounded-xl text-white">Dispatched</span>
                                    @endif
                                @elseif($order->status == 'Cancelled')
                                    <span class="bg-red-500 px-2 rounded-xl text-white">{{ $order->status }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $order->created_at->format('Y-m-d') }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $order->created_at->format('H:i') }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('vendor.orderDetail', $order->id) }}"
                                        class="bg-blue-500 hover:bg-blue-600 text-white text-xs px-3 py-1 rounded-lg transition">View</a>
                                    <button
                                        class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1 rounded-lg transition">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                    <!-- Example more rows -->

                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex justify-between items-center mt-6">
            <p class="text-sm text-gray-600">Showing 1–10 of 124 orders</p>
            <div class="flex space-x-1">
                <button class="px-3 py-1 border rounded-lg text-sm hover:bg-gray-100">Prev</button>
                <button class="px-3 py-1 border rounded-lg bg-blue-500 text-white text-sm">1</button>
                <button class="px-3 py-1 border rounded-lg text-sm hover:bg-gray-100">2</button>
                <button class="px-3 py-1 border rounded-lg text-sm hover:bg-gray-100">Next</button>
            </div>
        </div>
    </div>
</section>
