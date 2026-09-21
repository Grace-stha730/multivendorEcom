@props(['label', 'name', 'hint' => null, 'required' => false])

<div {{ $attributes->only('class') }}>
    <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ $label }}@if ($required)<span class="text-rose-600"> *</span>@endif</label>
    {{ $slot }}
    @if ($hint)
        <p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
    @enderror
</div>
