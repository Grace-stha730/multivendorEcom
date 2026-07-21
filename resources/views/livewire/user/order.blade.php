<div class="max-w-6xl mx-auto px-4 py-10" x-data="{ showDelete: false }">
    <h1 class="text-3xl font-bold mb-8 text-gray-800 flex items-center gap-2">
        <i class="fa-solid fa-box text-blue-600"></i> My Orders
    </h1>

    <!-- Status Filter Buttons -->
    <div class="flex flex-wrap gap-3 mb-8">
        @php
            $statuses = ['All', 'Pending', 'Processing', 'Warehouse', 'Shipped', 'Delivered', 'Cancelled'];
        @endphp
        @foreach ($statuses as $s)
            <button wire:click="setStatus('{{ $s }}')" @class([
                'px-4 py-2 rounded-full font-medium transition cursor-pointer',
                'bg-blue-600 text-white' => $status === $s,
                'bg-gray-200 text-gray-700 hover:bg-gray-300' => $status !== $s,
            ])>
                {{ $s }}
            </button>
        @endforeach
    </div>



    <!-- Orders List -->
    <div>
        @if ($orders->count() > 0)
            <div class="space-y-6">
                @foreach ($orders as $order)
                    <div
                        class="bg-white rounded-2xl shadow-sm hover:shadow-md border border-gray-100 transition-all duration-200 overflow-hidden">
                        <div
                            class="flex flex-col md:flex-row justify-between items-start md:items-center px-6 py-4 bg-gray-50 border-b">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                                    <i class="fa-solid fa-receipt text-gray-500"></i>
                                    {{ $order->order_number }}
                                </h2>
                                <p class="text-sm text-gray-500">Placed on
                                    {{ $order->created_at->format('M d, Y h:i A') }}</p>
                            </div>

                            <div class="flex items-center gap-4 mt-3 md:mt-0">
                                @if ($order->order_status == 'Pending')
                                    <span class="text-yellow-500">Your order has been placed and is awaiting
                                        confirmation.</span>
                                @elseif ($order->order_status == 'Processing')
                                    <span class="text-blue-500">Your order is currently being processed.</span>
                                @elseif ($order->order_status == 'Warehouse')
                                    <span class="text-green-500">Your order is in the warehouse and will be shipped
                                        shortly.</span>
                                @elseif ($order->order_status == 'Shipped')
                                    <span class="text-green-500">Your order is on its way!</span>
                                @elseif ($order->order_status == 'Delivered')
                                    <span class="text-green-500">Your order has been delivered. Enjoy!</span>
                                @elseif ($order->order_status == 'Cancelled')
                                    <span class="text-red-500">Your order has been cancelled. Please contact support for
                                        assistance.</span>
                                @endif
                                <button @click.prevent="showDelete = true"
                                    wire:click='popDeleteOrder({{ $order->id }})'
                                    class="text-red-600 hover:text-red-800 text-lg cursor-pointer" title="Cancel Order">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </div>

                        <div class="p-6 space-y-3">
                            <!-- Visual Order Tracking Timeline -->
                            @if ($order->order_status !== 'Cancelled')
                                @php
                                    $statusSteps = [
                                        'Pending' => 1,
                                        'Processing' => 2,
                                        'Warehouse' => 3,
                                        'Shipped' => 4,
                                        'Out for Delivery' => 5,
                                        'Delivered' => 6
                                    ];
                                    $step = $statusSteps[$order->order_status] ?? 1;
                                    $percentages = [1 => 0, 2 => 20, 3 => 40, 4 => 60, 5 => 80, 6 => 100];
                                    $percent = $percentages[$step] ?? 0;
                                @endphp
                                <div class="py-6 px-4 bg-gray-50/50 rounded-xl border border-gray-100 my-4">
                                    <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-5">Order Tracking Status</h4>
                                    <div class="relative flex flex-col md:flex-row justify-between items-start md:items-center gap-4 md:gap-0">
                                        <!-- Connecting Line for Desktop -->
                                        <div class="hidden md:block absolute top-[15px] left-0 right-0 h-1 bg-gray-200 z-0"></div>
                                        <!-- Active Line -->
                                        <div class="hidden md:block absolute top-[15px] left-0 h-1 bg-indigo-600 z-0 transition-all duration-300" style="width: {{ $percent }}%;"></div>

                                        <!-- Step 1: Placed -->
                                        <div class="flex md:flex-col items-center gap-3 md:gap-1.5 z-10 flex-1 w-full text-left md:text-center">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition duration-300 {{ $step >= 1 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500' }}">
                                                @if($step > 1) <i class="fa-solid fa-check"></i> @else 1 @endif
                                            </div>
                                            <span class="text-[11px] font-semibold {{ $step >= 1 ? 'text-indigo-600' : 'text-gray-500' }}">Order Placed</span>
                                        </div>

                                        <!-- Step 2: Processing -->
                                        <div class="flex md:flex-col items-center gap-3 md:gap-1.5 z-10 flex-1 w-full text-left md:text-center">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition duration-300 {{ $step >= 2 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500' }}">
                                                @if($step > 2) <i class="fa-solid fa-check"></i> @else 2 @endif
                                            </div>
                                            <span class="text-[11px] font-semibold {{ $step >= 2 ? 'text-indigo-600' : 'text-gray-500' }}">Processing</span>
                                        </div>

                                        <!-- Step 3: Warehouse -->
                                        <div class="flex md:flex-col items-center gap-3 md:gap-1.5 z-10 flex-1 w-full text-left md:text-center">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition duration-300 {{ $step >= 3 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500' }}">
                                                @if($step > 3) <i class="fa-solid fa-check"></i> @else 3 @endif
                                            </div>
                                            <span class="text-[11px] font-semibold {{ $step >= 3 ? 'text-indigo-600' : 'text-gray-500' }}">In Warehouse</span>
                                        </div>

                                        <!-- Step 4: Shipped -->
                                        <div class="flex md:flex-col items-center gap-3 md:gap-1.5 z-10 flex-1 w-full text-left md:text-center">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition duration-300 {{ $step >= 4 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500' }}">
                                                @if($step > 4) <i class="fa-solid fa-check"></i> @else 4 @endif
                                            </div>
                                            <span class="text-[11px] font-semibold {{ $step >= 4 ? 'text-indigo-600' : 'text-gray-500' }}">Shipped</span>
                                        </div>

                                        <!-- Step 5: Out for Delivery -->
                                        <div class="flex md:flex-col items-center gap-3 md:gap-1.5 z-10 flex-1 w-full text-left md:text-center">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition duration-300 {{ $step >= 5 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500' }}">
                                                @if($step > 5) <i class="fa-solid fa-check"></i> @else 5 @endif
                                            </div>
                                            <span class="text-[11px] font-semibold {{ $step >= 5 ? 'text-indigo-600' : 'text-gray-500' }}">Out for Delivery</span>
                                        </div>

                                        <!-- Step 6: Delivered -->
                                        <div class="flex md:flex-col items-center gap-3 md:gap-1.5 z-10 flex-1 w-full text-left md:text-center">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition duration-300 {{ $step >= 6 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500' }}">
                                                @if($step > 6) <i class="fa-solid fa-check"></i> @else 6 @endif
                                            </div>
                                            <span class="text-[11px] font-semibold {{ $step >= 6 ? 'text-indigo-600' : 'text-gray-500' }}">Delivered</span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="flex flex-wrap justify-between text-sm text-gray-700">
                                <div>
                                    <p><strong>Payment:</strong> {{ $order->payment_method }}</p>
                                </div>
                                <div>
                                    <p><strong>Total:</strong> <span class="text-green-700 font-semibold">Rs.
                                            {{ number_format($order->price) }}</span></p>
                                </div>
                            </div>

                            <div class="mt-4">
                                <h3 class="font-semibold mb-2 text-gray-800">Items</h3>
                                <ul class="divide-y divide-gray-200">
                                    @foreach ($order->orderItems as $item)
                                        <li class="py-3 flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                                            <div class="flex items-center gap-3">
                                                <img src="{{ $item->product->firstImage?->url ? asset('storage/' . $item->product->firstImage->url) : asset('images/2.png') }}"
                                                    class="w-10 h-10 rounded object-cover border" alt="">
                                                <div>
                                                    <p class="font-medium text-gray-900">{{ $item->product->name }}</p>
                                                    <p class="text-sm text-gray-500">x{{ $item->quantity }}</p>
                                                    @if ($item->vendorOrder && $item->vendorOrder->status == 'Cancelled')
                                                        <span class="text-xs text-red-600 bg-red-50 px-2 py-1 rounded mt-1 inline-block">
                                                            Cancelled by {{ $item->vendorOrder->vendor->shop_name }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-4">
                                                <span class="font-semibold text-gray-700">Rs. {{ number_format($item->total) }}</span>
                                                @if ($item->product && $item->product->vendor_id)
                                                    <button wire:click="startChatWithVendor({{ $item->product->vendor_id }}, {{ $item->product->id }})"
                                                        class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer shadow-xs">
                                                        <i class="fa-solid fa-comments text-indigo-600"></i>
                                                        <span>Chat Vendor</span>
                                                    </button>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        @if (strtolower($order->order_status) === 'delivered')
                            <div class="my-4 mx-3 flex justify-end">
                                <a href="{{ route('user.review',['id' => $order->id]) }}"
                                    class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg flex items-center gap-2">
                                    <i class="fa-solid fa-star"></i> Review
                                </a>
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-10 bg-white rounded-lg shadow-sm">
                <i class="fa-solid fa-box-open text-4xl text-gray-400 mb-3"></i>
                <p class="text-gray-600">No orders found for selected status.</p>
            </div>
        @endif
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="showDelete" x-transition.opacity x-cloak
        class="fixed inset-0 flex items-center justify-center bg-black/40 backdrop-blur-sm z-50">
        <div x-transition.scale
            class="bg-white w-full max-w-md rounded-2xl shadow-lg p-6 relative border-t-4 border-red-600">
            <button @click.prevent="showDelete = false"
                class="absolute top-2 right-3 text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>

            @if ($orderItem && $orderItem->orderItems)
                <div class="border-b pb-3 mb-4">
                    <h2 class="text-base text-gray-800 font-semibold">
                        Delete Order <span class="text-red-600">{{ $orderItem->order_number }}</span>?
                    </h2>
                    <ul class="text-sm mt-2 text-gray-600 list-disc list-inside">
                        @foreach ($orderItem->orderItems as $item)
                            <li>{{ $item->product?->name ?? 'Unknown Product' }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="flex justify-end gap-3">
                <button @click.prevent="showDelete = false"
                    class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium">
                    Cancel
                </button>
                <button wire:click.prevent='deleteOrder'
                    class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>
