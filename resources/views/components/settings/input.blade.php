@props(['accent' => 'emerald', 'numeric' => false])

@php
    $focus = $accent === 'indigo'
        ? 'focus:border-indigo-500 focus:ring-indigo-200'
        : 'focus:border-emerald-500 focus:ring-emerald-200';
    $class = "w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500 {$focus}";
@endphp

@if ($numeric)
    {{-- Digits only: no spinner arrows, no e + - . , keys, and the mouse wheel cannot change the value. --}}
    <input {{ $attributes->merge(['class' => $class]) }} type="number" inputmode="numeric" min="0" step="1" x-data
        @keydown="['e','E','+','-','.',','].includes($event.key) && $event.preventDefault()" @wheel="$el.blur()">
@else
    <input {{ $attributes->merge(['class' => $class, 'type' => 'text']) }}>
@endif
