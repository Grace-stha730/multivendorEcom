@props(['order'])

{{-- The delivery address exactly as it was when the order was placed (a snapshot, not the customer's live address). --}}
@php
    $snapshot = $order->hasAddressSnapshot();
    $provinceName = $order->deliveryProvince?->name ?? $order->province;
    $districtName = $order->deliveryDistrict?->name;
    $line = collect([$order->tole, $order->city, $districtName, $provinceName])->filter()->implode(', ');
    $hours = $order->address_type === 'office' ? \App\Models\UserAddress::formatHours($order->office_start_time, $order->office_end_time) : null;
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700']) }}>
    <h3 class="mb-2 font-semibold text-gray-900"><i class="fa-solid fa-location-dot mr-1 text-gray-400"></i>Delivery address</h3>
    <p><span class="font-semibold">Receiver:</span> {{ $order->receiver_name ?? $order->name }}</p>
    <p><span class="font-semibold">Address:</span> {{ $line }}</p>
    <p><span class="font-semibold">Contact:</span> {{ $order->phone }}</p>
    @if ($snapshot && $order->address_type)
        <p><span class="font-semibold">Type:</span> {{ ucfirst($order->address_type) }}</p>
    @endif
    @if ($hours)
        <p><span class="font-semibold">Office hours:</span> {{ $hours }}</p>
    @endif
    @unless ($snapshot)
        <p class="mt-2 text-xs text-gray-400">This order was placed before saved addresses were introduced.</p>
    @endunless
</div>
