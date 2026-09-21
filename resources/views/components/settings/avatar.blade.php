@props(['name' => 'image', 'current' => null, 'preview' => null, 'initials' => '?', 'label' => 'Change photo', 'accent' => 'emerald', 'square' => false])

@php
    $link = $accent === 'indigo' ? 'text-indigo-600 hover:bg-indigo-50' : 'text-emerald-700 hover:bg-emerald-50';
    $shape = $square ? 'rounded-2xl' : 'rounded-full';
@endphp

<div class="flex items-center gap-5">
    <div class="relative h-20 w-20 shrink-0 overflow-hidden bg-slate-100 shadow ring-2 ring-white {{ $shape }}">
        @if ($preview)
            <img src="{{ $preview }}" alt="New photo preview" class="h-full w-full object-cover">
        @elseif ($current)
            <img src="{{ $current }}" alt="Current photo" class="h-full w-full object-cover" referrerpolicy="no-referrer">
        @else
            <span class="grid h-full w-full place-items-center text-2xl font-bold text-slate-400">{{ $initials }}</span>
        @endif
        <div wire:loading.flex wire:target="{{ $name }}" class="absolute inset-0 items-center justify-center bg-white/75 text-xs font-medium text-slate-600">Uploading</div>
    </div>

    <div>
        <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium {{ $link }}">
            <i class="fa-solid fa-camera"></i> {{ $label }}
            <input type="file" wire:model="{{ $name }}" accept="image/*" class="sr-only">
        </label>
        <p class="mt-1.5 text-xs text-slate-500">JPG or PNG, up to 2 MB. Press Save to apply it.</p>
        @error($name)
            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
        @enderror
    </div>
</div>
