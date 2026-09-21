<section class="min-h-screen bg-gray-100 py-10">
    <div class="max-w-6xl mx-auto px-4 space-y-6">
        <h1 class="text-2xl font-bold text-gray-800">Checkout</h1>

        <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left">Product</th>
                        <th class="px-4 py-3 text-center">Price</th>
                        <th class="px-4 py-3 text-center">Quantity</th>
                        <th class="px-4 py-3 text-center">Discount</th>
                        <th class="px-4 py-3 text-center">Subtotal</th>
                        <th class="px-4 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($viewItems as $key => $item)
                        <tr class="align-top">
                            <td class="px-4 py-4 min-w-72">
                                <div class="flex gap-3">
                                    <img class="w-16 h-16 object-cover rounded" src="{{ asset('storage/'.($item['image'] ?: 'default/product.webp')) }}" alt="{{ $item['name'] }}">
                                    <div>
                                        <h2 class="font-semibold text-gray-800">{{ $item['name'] }}</h2>
                                        <p class="text-xs text-gray-500">{{ $item['summary'] }}</p>
                                        @if(!empty($item['selected_variants']['variants']))
                                            <div class="flex flex-wrap gap-1 mt-2">
                                                @foreach($item['selected_variants']['variants'] as $variant)
                                                    <span class="inline-block bg-indigo-50 text-indigo-700 text-[10px] px-1.5 py-0.5 rounded border border-indigo-100 font-semibold">{{ $variant['attribute_value'] ?: $variant['attribute_name'] }} × {{ $variant['quantity'] }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                        @if(!empty($item['variants']))
                                            <button type="button" wire:click="openVariantModal('{{ $key }}')" class="mt-2 text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold px-2 py-1 rounded border border-indigo-200">{{ !empty($item['selected_variants']['variants']) ? 'Change variants' : 'Choose variants' }}</button>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center whitespace-nowrap font-medium">Rs. {{ number_format($item['unit_price'], 2) }}</td>
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                @if(!empty($item['selected_variants']['variants']))
                                    <span class="font-medium">{{ $item['quantity'] }}</span>
                                @else
                                    <div class="flex justify-center items-center gap-2"><button wire:click="decrement('{{ $key }}')" class="border px-2 rounded">−</button><input wire:model.live="items.{{ $key }}.quantity" class="border rounded w-14 text-center" type="number" min="1"><button wire:click="increment('{{ $key }}')" class="border px-2 rounded">+</button></div>
                                @endif
                                @error('items.'.$key.'.quantity')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                            </td>
                            <td class="px-4 py-4 text-center min-w-44">
                                @if($item['vendor_options']->isNotEmpty())
                                    <select wire:change="selectVendorCoupon('{{ $key }}', $event.target.value)" class="border rounded p-1 w-full text-xs">
                                        <option value="">No coupon</option>
                                        @foreach($item['vendor_options'] as $coupon)
                                            <option value="{{ $coupon->id }}" @selected(($vendorCoupons[$key] ?? null) == $coupon->id)>{{ $coupon->code }} — {{ $coupon->type === 'percent' ? $coupon->value.'%' : 'Rs. '.$coupon->value }}</option>
                                        @endforeach
                                    </select>
                                @endif
                                @if($item['coupon_discount'] > 0)
                                    <p class="mt-2 font-semibold text-green-600">- Rs. {{ number_format($item['coupon_discount'], 2) }}</p>
                                    <button wire:click="removeVendorCoupon('{{ $key }}')" class="mt-1 text-xs text-red-600 hover:underline">Remove coupon</button>
                                @elseif($item['vendor_options']->isEmpty())
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center font-semibold whitespace-nowrap">Rs. {{ number_format($item['subtotal'], 2) }}</td>
                            <td class="px-4 py-4 text-center text-gray-400">—</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($variantItemKey !== null && isset($items[$variantItemKey]))
            <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
                <div class="w-full max-w-lg bg-white rounded-xl shadow-xl p-6">
                    <div class="flex items-start justify-between gap-4 mb-4"><div><h3 class="text-lg font-bold">Choose variants and quantities</h3><p class="text-sm text-gray-500">Update the selected quantities for this product.</p></div><button type="button" wire:click="closeVariantModal" class="text-xl text-gray-500">&times;</button></div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($items[$variantItemKey]['variants'] as $attributeName => $variants)
                            @foreach($variants as $variant)
                                <div class="text-left border rounded-lg p-3 border-gray-200"><span class="block font-semibold">{{ $variant['value'] ?: $attributeName }}</span><span class="block mt-1 text-sm {{ $variant['price_extra'] >= 0 ? 'text-indigo-700' : 'text-green-700' }}">{{ $variant['price_extra'] >= 0 ? '+' : '' }}Rs. {{ number_format($variant['price_extra'], 2) }}</span><span class="block text-xs text-gray-500">{{ $variant['stock'] }} available</span><label class="block text-xs font-medium mt-2">Quantity <input type="number" min="0" max="{{ $variant['stock'] }}" wire:model.live="variantQuantities.{{ $variant['id'] }}" class="ml-2 w-16 border rounded p-1 text-center"></label>@error('variantQuantities.'.$variant['id'])<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
                            @endforeach
                        @endforeach
                    </div>
                    @error('variantQuantities')<p class="text-sm text-red-600 mt-3">{{ $message }}</p>@enderror
                    <div class="mt-5 flex justify-end gap-3"><button type="button" wire:click="closeVariantModal" class="px-4 py-2 border rounded">Cancel</button><button type="button" wire:click="applyVariantQuantities" class="px-4 py-2 bg-indigo-600 text-white rounded">Update cart</button></div>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm p-5">
            <livewire:user.address-manager mode="select" wire:model="addressId" />
            @error('addressId')<p class="mt-3 text-sm font-medium text-red-600">{{ $message }}</p>@enderror
        </div>

        <form wire:submit="placeOrder" class="bg-white rounded-xl shadow-sm p-5 grid md:grid-cols-2 gap-4">
            <select wire:model="paymentMethod" class="border rounded p-2"><option>Cash</option><option>E-Sewa</option></select>
            @if($adminOptions->isNotEmpty())
                <label class="text-sm">Have a Promo / Coupon Code?
                    <select wire:model.live="adminCouponId" class="border rounded p-2 w-full mt-1"><option value="">Select collected coupon</option>@foreach($adminOptions as $coupon)<option value="{{ $coupon->id }}">{{ $coupon->code }} — {{ $coupon->type === 'percent' ? $coupon->value.'%' : 'Rs. '.$coupon->value }}</option>@endforeach</select>
                </label>
            @endif
            <div class="md:col-span-2 text-right">@unless ($addressId)<p class="mb-2 text-sm text-amber-700">Add or select a delivery address above to place your order.</p>@endunless
                <button @disabled(!$addressId) class="bg-indigo-600 text-white px-6 py-2 rounded disabled:cursor-not-allowed disabled:opacity-50">Place Order</button></div>
        </form>
    </div>
</section>
