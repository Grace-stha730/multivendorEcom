<section class="bg-gray-100 min-h-screen py-10" x-data="{ modal: false }">
    <div class="max-w-6xl mx-auto px-4">
        @include('common.message')

        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-wallet text-emerald-600"></i> Earnings & Financial Payouts
                </h2>
                <p class="text-sm text-gray-500 mt-1">Track net earnings, commission fees, and withdraw funds</p>
            </div>
            <button @click="modal = true"
                class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-5 py-2.5 rounded-xl shadow transition duration-150 flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-hand-holding-dollar"></i> Request Payout
            </button>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-5 rounded-2xl shadow-md border border-gray-100">
                <span class="text-xs font-semibold uppercase text-gray-400">Gross Sales</span>
                <h3 class="text-2xl font-bold text-gray-900 mt-1">Rs. {{ number_format($grossSales) }}</h3>
                <p class="text-xs text-gray-500 mt-1">Total revenue from delivered orders</p>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-md border border-gray-100">
                <span class="text-xs font-semibold uppercase text-gray-400">Platform Fee (10%)</span>
                <h3 class="text-2xl font-bold text-rose-600 mt-1">- Rs. {{ number_format($commissionFee) }}</h3>
                <p class="text-xs text-gray-500 mt-1">10% marketplace commission</p>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-md border border-gray-100">
                <span class="text-xs font-semibold uppercase text-gray-400">Net Earnings</span>
                <h3 class="text-2xl font-bold text-blue-600 mt-1">Rs. {{ number_format($netEarnings) }}</h3>
                <p class="text-xs text-gray-500 mt-1">Your total earned income</p>
            </div>

            <div class="bg-emerald-500 text-white p-5 rounded-2xl shadow-lg">
                <span class="text-xs font-semibold uppercase opacity-80">Available to Withdraw</span>
                <h3 class="text-2xl font-extrabold mt-1">Rs. {{ number_format($availableBalance) }}</h3>
                <p class="text-xs opacity-90 mt-1">Ready for payout request</p>
            </div>
        </div>

        <!-- Payout History Table -->
        <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100">
            <h3 class="text-lg font-semibold mb-4 text-gray-800">Payout History</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm divide-y divide-gray-200">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                        <tr>
                            <th class="py-3 px-4">#</th>
                            <th class="py-3 px-4">Requested Date</th>
                            <th class="py-3 px-4">Amount</th>
                            <th class="py-3 px-4">Method</th>
                            <th class="py-3 px-4">Account Details</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($payouts as $idx => $p)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium text-gray-500">{{ $idx + 1 }}</td>
                                <td class="py-3 px-4 text-xs text-gray-600">{{ $p->created_at->format('j M Y, h:i A') }}</td>
                                <td class="py-3 px-4 font-bold text-gray-900">Rs. {{ number_format($p->amount) }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ $p->payment_method }}</td>
                                <td class="py-3 px-4 text-xs text-gray-500">{{ $p->account_details }}</td>
                                <td class="py-3 px-4">
                                    @if ($p->status == 'Pending')
                                        <span class="bg-orange-100 text-orange-800 text-xs px-2.5 py-0.5 rounded-full font-semibold">Pending</span>
                                    @elseif($p->status == 'Approved' || $p->status == 'Paid')
                                        <span class="bg-green-100 text-green-800 text-xs px-2.5 py-0.5 rounded-full font-semibold">Paid</span>
                                    @else
                                        <span class="bg-red-100 text-red-800 text-xs px-2.5 py-0.5 rounded-full font-semibold">Rejected</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-8 text-gray-500">No payout requests submitted yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Request Payout Modal -->
        <div x-show="modal" x-transition x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl relative" @click.outside="modal = false">
                <button @click="modal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 cursor-pointer text-lg">&times;</button>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Request Payout</h3>
                <p class="text-xs text-gray-500 mb-4">Available balance: <span class="font-bold text-emerald-600">Rs. {{ number_format($availableBalance) }}</span></p>

                <form wire:submit.prevent="requestPayout({{ $availableBalance }})" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Withdrawal Amount (Rs.)</label>
                        <input type="number" step="0.01" max="{{ $availableBalance }}" wire:model="amount" placeholder="Min. Rs. 100"
                            class="border border-gray-300 rounded-lg p-2.5 w-full text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        @error('amount') <small class="text-red-500">{{ $message }}</small> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                        <select wire:model="payment_method" class="border border-gray-300 rounded-lg p-2.5 w-full text-sm bg-white">
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="E-Sewa">E-Sewa</option>
                            <option value="Khalti">Khalti</option>
                            <option value="Cash/Check">Cash / Cheque</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Account Details</label>
                        <input type="text" wire:model="account_details" placeholder="e.g. Bank Name, Account No., Branch or ID"
                            class="border border-gray-300 rounded-lg p-2.5 w-full text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        @error('account_details') <small class="text-red-500">{{ $message }}</small> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Additional Notes (Optional)</label>
                        <textarea wire:model="notes" rows="2" placeholder="Any special notes..."
                            class="border border-gray-300 rounded-lg p-2.5 w-full text-sm"></textarea>
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="submit"
                            class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2.5 rounded-xl transition text-sm cursor-pointer shadow">
                            Submit Request
                        </button>
                        <button type="button" @click="modal = false"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2.5 rounded-xl transition text-sm cursor-pointer">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
