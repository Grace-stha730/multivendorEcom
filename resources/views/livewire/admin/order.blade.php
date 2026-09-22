<section class="bg-gray-100 min-h-screen py-10">
    <div class="max-w-[94%] mx-auto bg-white rounded-2xl shadow-md p-6">
        <h2 class="text-3xl font-semibold mb-6 flex items-center justify-between">
            🛍️ All Orders
            <span class="text-sm text-gray-500">Rs. {{ number_format($ordersTotal) }}</span>
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
                                <td class="px-4 py-3 font-medium text-gray-700">{{ $orders->firstItem() + $idx }}</td>
                                <td class="px-4 py-3 font-medium text-gray-700">{{ $order->order_number }}</td>
                                <td class="px-4 py-3 font-medium text-gray-700">{{ $order->user->name }}</td>
                                <td class="px-4 py-3 font-medium text-gray-700">{{ $order->payment_method }}</td>
                                <td class="px-4 py-3 font-medium text-gray-700">
                                    @if ($order->receiver_name)<span class="font-semibold">{{ $order->receiver_name }}</span><br>@endif
                                    {{ $order->tole }}, {{ $order->city }}<br>
                                    <span class="text-gray-500">{{ collect([$order->deliveryDistrict?->name, $order->deliveryProvince?->name ?? $order->province])->filter()->implode(', ') }}</span>
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-700">{{ $order->vendorOrders->count() }}</td>
                                <td class="px-4 py-3 font-medium text-gray-700">{{ number_format($order->price) }}</td>
                                <td class="px-4 py-3 font-medium text-gray-700">
                                    @if ($order->awaitingPayment())<span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">Awaiting payment</span>@else{{ $order->order_status }}@endif
                                </td>
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
            <div class="mt-4">{{ $orders->links() }}</div>
        </div>

    </div>
</section>
