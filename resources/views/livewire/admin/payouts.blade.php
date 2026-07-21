<div class="p-6 bg-gray-100 min-h-screen">
    @include('common.message')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-semibold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-hand-holding-dollar text-emerald-600"></i> Vendor Payout Requests
            </h2>
            <p class="text-sm text-gray-500 mt-1">Review and process withdrawal requests submitted by vendors</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm divide-y divide-gray-200">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="py-3 px-4">#</th>
                        <th class="py-3 px-4">Vendor Shop</th>
                        <th class="py-3 px-4">Amount</th>
                        <th class="py-3 px-4">Method</th>
                        <th class="py-3 px-4">Account Details</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($payouts as $idx => $p)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 font-medium text-gray-500">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4 font-bold text-gray-900">{{ $p->vendor->shop_name ?? 'Vendor' }}</td>
                            <td class="py-3 px-4 font-bold text-emerald-600">Rs. {{ number_format($p->amount) }}</td>
                            <td class="py-3 px-4 text-gray-600">{{ $p->payment_method }}</td>
                            <td class="py-3 px-4 text-xs text-gray-500">{{ $p->account_details }}</td>
                            <td class="py-3 px-4 text-xs text-gray-500">{{ $p->created_at->format('j M Y, h:i A') }}</td>
                            <td class="py-3 px-4">
                                @if ($p->status == 'Pending')
                                    <span class="bg-orange-100 text-orange-800 text-xs px-2.5 py-0.5 rounded-full font-semibold">Pending</span>
                                @elseif($p->status == 'Approved' || $p->status == 'Paid')
                                    <span class="bg-green-100 text-green-800 text-xs px-2.5 py-0.5 rounded-full font-semibold">Paid</span>
                                @else
                                    <span class="bg-red-100 text-red-800 text-xs px-2.5 py-0.5 rounded-full font-semibold">Rejected</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right space-x-2">
                                @if ($p->status == 'Pending')
                                    <button wire:click="approvePayout({{ $p->id }})"
                                        class="bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1.5 rounded-lg font-medium transition cursor-pointer">
                                        Approve & Pay
                                    </button>
                                    <button wire:click="rejectPayout({{ $p->id }})"
                                        class="bg-red-600 hover:bg-red-700 text-white text-xs px-3 py-1.5 rounded-lg font-medium transition cursor-pointer">
                                        Reject
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400">Processed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-gray-500">No payout requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
