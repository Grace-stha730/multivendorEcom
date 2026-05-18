<section class="bg-gray-100 min-h-screen py-10">
    <div class="max-w-[94%] mx-auto bg-white rounded-2xl shadow-md p-6">
        <h2 class="text-3xl font-semibold mb-6 flex items-center justify-between">
            🛍️ All Orders
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
                        <th class="px-4 py-3 text-left">Payment Method</th>
                        <th class="px-4 py-3 text-left">Address</th>
                        <th class="px-4 py-3 text-left">Items</th>
                        <th class="px-4 py-3 text-left">Total</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Order Date</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <!-- Single Order Row -->
                    @if ($orders && count($orders) > 0)
                        @foreach ($orders as $idx => $order)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 font-medium text-gray-700">{{ $idx + 1 }}</td>
                                <td class="px-4 py-3 font-medium text-gray-700">{{ $order->order_number }}</td>
                                <td class="px-4 py-3 font-medium text-gray-700">{{ $order->user->name }}</td>
                                <td class="px-4 py-3 font-medium text-gray-700">{{ $order->payment_method }}</td>
                                <td class="px-4 py-3 font-medium text-gray-700">{{ $order->province }},
                                    {{ $order->city }} <br> {{ $order->tole }}</td>
                                <td class="px-4 py-3 font-medium text-gray-700">{{ $order->vendorOrders->count() }}</td>
                                <td class="px-4 py-3 font-medium text-gray-700">{{ number_format($order->price) }}</td>
                                <td class="px-4 py-3 font-medium text-gray-700">{{ $order->order_status }}</td>
                                <td class="px-4 py-3 font-medium text-gray-700">
                                    {{ $order->created_at->format('j M Y') }}</td>
                                <td class="text-center">
                                    <a class="bg-gray-800 text-white px-2 py-0.5 rounded-md hover:bg-green-800 duration-150"
                                        href="{{ route('admin.order-detail', ['id' => $order->id]) }}">View Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="9" class="text-center py-6 text-gray-500">No orders found</td>
                        </tr>
                    @endif

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
