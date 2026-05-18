<div class="p-6 space-y-6">

    <!-- Top Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-5 rounded-xl shadow hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Products</p>
                    <h3 class="text-2xl font-bold text-blue-600 mt-1">{{ $products ? count($products) : 0 }}</h3>
                </div>
                <i class="fa-solid fa-box text-blue-500 text-3xl"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Orders</p>
                    <h3 class="text-2xl font-bold text-green-600 mt-1">{{ $orders ? count($orders) : 0 }}</h3>
                </div>
                <i class="fa-solid fa-truck text-green-500 text-3xl"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Earnings</p>
                    <h3 class="text-2xl font-bold text-yellow-600 mt-1">Rs.
                        {{ $orders ? number_format($orders->sum('subtotal'), 2) : 0 }}</h3>
                </div>
                <i class="fa-solid fa-coins text-yellow-500 text-3xl"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Messages</p>
                    <h3 class="text-2xl font-bold text-red-600 mt-1">8</h3>
                </div>
                <i class="fa-solid fa-envelope text-red-500 text-3xl"></i>
            </div>
        </div>
    </div>

    <!-- Recent Orders Section -->
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Recent Orders</h2>
            <a href="{{ route('vendor.order') }}" class="text-blue-600 text-sm hover:underline">View All</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left">
                <thead class="text-gray-600 border-b">
                    <tr>
                        <th class="py-3 px-4">Order ID</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Total</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Date</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @if (count($recentOrders) > 0)
                        @foreach ($recentOrders as $recentOrder)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium">{{ $recentOrder->order->order_number }}</td>
                                <td class="py-3 px-4">{{ $recentOrder->order->name }}</td>
                                <td class="py-3 px-4">Rs. {{ $recentOrder->subtotal }}</td>
                                <td class="py-3 px-4">
                                    @if ($recentOrder->status == 'Pending')
                                        <span class="bg-orange-100 text-orange-700 px-2 py-1 rounded text-xs">Pending
                                        </span>
                                    @elseif($recentOrder->status == 'Processing')
                                        <span
                                            class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs">Delivered</span>
                                    @elseif($recentOrder->status == 'Delivered')
                                        @if ($recentOrder->is_received)
                                            <span
                                                class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Delivered</span>
                                        @else
                                            <span
                                                class="bg-pink-100 text-pink-700 px-2 py-1 rounded text-xs">Dispatched</span>
                                        @endif
                                    @elseif($recentOrder->status == 'Cancelled')
                                        <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">Cancelled</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">{{ $recentOrder->created_at->format('Y-m-d') }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr class="text-center ">
                            <td colspan="5" class="py-4 px-4 font-semibold text-2xl text-gray-400">No Recent Order
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Analytics / Graph placeholder -->
    <div class="bg-white rounded-2xl shadow p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-6">Sales Overview</h2>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Quantity Sold Chart -->
            <div class="p-12 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-md h-120">
                <h3 class="text-md font-semibold text-blue-700 mb-3 text-center">📦 Quantity Sold by Product</h3>
                <canvas id="quantityChart" class="h-150"></canvas>
            </div>

            <!-- Revenue Chart -->
            <div class="p-12 bg-gradient-to-br from-pink-50 to-pink-100 rounded-xl shadow-md h-120">
                <h3 class="text-md font-semibold text-pink-700 mb-3 text-center">💰 Revenue by Product</h3>
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="overflow-x-auto mt-5">
        <h3 class="text-md font-semibold text-gray-700 mb-3">Sales by Product</h3>
        <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100 text-gray-600 uppercase text-xs tracking-wider">
                <tr>
                    <th class="py-3 px-4">Product</th>
                    <th class="py-3 px-4">Quantity Sold</th>
                    <th class="py-3 px-4">Revenue</th>
                </tr>
            </thead>
            <tbody class="divide-y text-gray-700">
                @foreach ($report as $sale)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-3 px-4 font-medium">{{ $sale->product->name ?? 'Unknown' }}</td>
                        <td class="py-3 px-4">{{ $sale->total_sold }}</td>
                        <td class="py-3 px-4 text-green-600 font-semibold">
                            Rs. {{ number_format($sale->total_price) }}
                        </td>
                    </tr>
                @endforeach

            </tbody>
        </table>
        <canvas id="salesChart2"></canvas>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('livewire:navigated', () => {
        const reportData = @json($report);

        const labels = reportData.map(item => item.product.name);
        const totalSold = reportData.map(item => item.total_sold);
        const totalPrice = reportData.map(item => item.total_price);

        // Common line style generator for clean consistency
        function createLineChart(ctx, label, data, borderColor, backgroundColor) {
            return new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: label,
                        data: data,
                        borderColor: borderColor,
                        backgroundColor: backgroundColor,
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: borderColor,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: label,
                            font: {
                                size: 16,
                                weight: '600'
                            },
                            color: '#333'
                        },
                        tooltip: {
                            backgroundColor: '#fff',
                            titleColor: '#000',
                            bodyColor: '#000',
                            borderColor: borderColor,
                            borderWidth: 1
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                            },
                            title: {
                                display: true,
                                text: label === 'Quantity Sold' ? 'Units Sold' : 'Revenue (Rs.)',
                                color: '#555',
                                font: {
                                    size: 13
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#555'
                            }
                        }
                    }
                }
            });
        }

        // Initialize both charts
        createLineChart(
            document.getElementById('quantityChart').getContext('2d'),
            'Quantity Sold',
            totalSold,
            'rgba(37, 99, 235, 1)',
            'rgba(37, 99, 235, 0.15)'
        );

        createLineChart(
            document.getElementById('revenueChart').getContext('2d'),
            'Total Revenue (Rs)',
            totalPrice,
            'rgba(236, 72, 153, 1)',
            'rgba(236, 72, 153, 0.15)'
        );
    })
</script>
