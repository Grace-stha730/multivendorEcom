@props(['accent' => 'emerald', 'target' => null])

@php
    $colour = $accent === 'indigo'
        ? 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-200'
        : 'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-200';
@endphp

<button {{ $attributes->merge(['type' => 'submit', 'class' => "inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm transition focus:outline-none focus:ring-2 disabled:cursor-not-allowed disabled:opacity-60 {$colour}"]) }}
    @if ($target) wire:loading.attr="disabled" wire:target="{{ $target }}" @endif>
    @if ($target)
        <span wire:loading wire:target="{{ $target }}" class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
    @endif
    {{ $slot }}
</button>
