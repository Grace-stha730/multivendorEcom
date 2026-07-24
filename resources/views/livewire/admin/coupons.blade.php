<div class="p-6 bg-gray-100 min-h-screen">
    @include('common.message')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-semibold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-ticket text-blue-600"></i> Manage Coupons
            </h2>
            <p class="text-sm text-gray-500 mt-1">Create and manage platform discount promo codes</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Section -->
        <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100 h-fit">
            <h3 class="text-lg font-semibold mb-4 text-gray-800">
                {{ $editingId ? 'Edit Coupon' : 'Create New Coupon' }}
            </h3>

            <form wire:submit.prevent="saveCoupon" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Coupon Code</label>
                    <input type="text" placeholder="e.g. SUMMER20" wire:model="code"
                        class="uppercase border border-gray-300 rounded-lg p-2.5 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    @error('code') <small class="text-red-500">{{ $message }}</small> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Description / Requirement</label>
                    <textarea rows="3" placeholder="Explain who can use this coupon and what products qualify" wire:model="description"
                        class="border border-gray-300 rounded-lg p-2.5 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                    @error('description') <small class="text-red-500">{{ $message }}</small> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Discount Type</label>
                        <select wire:model="type" class="border border-gray-300 rounded-lg p-2.5 w-full text-sm bg-white">
                            <option value="fixed">Fixed Amount (Rs.)</option>
                            <option value="percent">Percentage (%)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Value</label>
                        <input type="number" step="0.01" placeholder="e.g. 100 or 15" wire:model="value"
                            class="border border-gray-300 rounded-lg p-2.5 w-full text-sm">
                        @error('value') <small class="text-red-500">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Min. Order Amount (Rs.)</label>
                    <input type="number" step="0.01" placeholder="0 for no limit" wire:model="min_order_amount"
                        class="border border-gray-300 rounded-lg p-2.5 w-full text-sm">
                    @error('min_order_amount') <small class="text-red-500">{{ $message }}</small> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">User Limit</label>
                        <input type="number" min="1" step="1" placeholder="How many users can apply" wire:model="usage_limit"
                            class="border border-gray-300 rounded-lg p-2.5 w-full text-sm">
                        @error('usage_limit') <small class="text-red-500">{{ $message }}</small> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Min. Item Price (Rs.)</label>
                        <input type="number" min="0" step="0.01" placeholder="0 for any item" wire:model="min_item_price"
                            class="border border-gray-300 rounded-lg p-2.5 w-full text-sm">
                        @error('min_item_price') <small class="text-red-500">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Starts At</label>
                        <input type="date" wire:model="starts_at" class="border border-gray-300 rounded-lg p-2 w-full text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Expires At</label>
                        <input type="date" wire:model="expires_at" class="border border-gray-300 rounded-lg p-2 w-full text-xs">
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" id="is_active" wire:model="is_active" class="rounded text-blue-600 cursor-pointer">
                    <label for="is_active" class="text-sm font-medium text-gray-700 cursor-pointer">Active</label>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg transition text-sm cursor-pointer">
                        {{ $editingId ? 'Update Coupon' : 'Create Coupon' }}
                    </button>
                    @if ($editingId)
                        <button type="button" wire:click="resetForm"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2.5 rounded-lg transition text-sm cursor-pointer">
                            Cancel
                        </button>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table Section -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-md p-6 border border-gray-100">
            <h3 class="text-lg font-semibold mb-4 text-gray-800">All Coupons</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm divide-y divide-gray-200">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                        <tr>
                            <th class="py-3 px-4">Code</th>
                            <th class="py-3 px-4">Discount</th>
                            <th class="py-3 px-4">Min Order</th>
                            <th class="py-3 px-4">Min Item</th>
                            <th class="py-3 px-4">Usage</th>
                            <th class="py-3 px-4">Expiry</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($coupons as $c)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4">
                                    <p class="font-bold text-blue-700 uppercase">{{ $c->code }}</p>
                                    @if ($c->description)
                                        <p class="text-xs text-gray-500 mt-1 max-w-[180px] truncate">{{ $c->description }}</p>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-medium">
                                    {{ $c->type === 'percent' ? $c->value . '%' : 'Rs. ' . number_format($c->value) }}
                                </td>
                                <td class="py-3 px-4 text-gray-600">Rs. {{ number_format($c->min_order_amount) }}</td>
                                <td class="py-3 px-4 text-gray-600">Rs. {{ number_format($c->min_item_price) }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ number_format($c->used_count) }} / {{ number_format($c->usage_limit) }}</td>
                                <td class="py-3 px-4 text-xs text-gray-500">
                                    {{ $c->expires_at ? $c->expires_at->format('j M Y') : 'No Expiry' }}
                                </td>
                                <td class="py-3 px-4">
                                    <button wire:click="toggleStatus({{ $c->id }})"
                                        class="px-2.5 py-0.5 text-xs font-semibold rounded-full cursor-pointer {{ $c->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $c->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td class="py-3 px-4 text-right space-x-2">
                                    <button wire:click="editCoupon({{ $c->id }})" class="text-blue-600 hover:underline cursor-pointer text-xs">Edit</button>
                                    <button wire:click="deleteCoupon({{ $c->id }})" wire:confirm="Delete this coupon?" class="text-red-500 hover:underline cursor-pointer text-xs">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-gray-500">No coupons created yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
