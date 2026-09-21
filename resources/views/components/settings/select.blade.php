@props(['accent' => 'emerald'])

@php
    $focus = $accent === 'indigo'
        ? 'focus:border-indigo-500 focus:ring-indigo-200'
        : 'focus:border-emerald-500 focus:ring-emerald-200';
@endphp

<select {{ $attributes->merge(['class' => "w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500 {$focus}"]) }}>
    {{ $slot }}
</select>
