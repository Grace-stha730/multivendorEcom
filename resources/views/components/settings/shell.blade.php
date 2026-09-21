@props(['title', 'subtitle' => null, 'tabs', 'active', 'accent' => 'emerald'])

{{-- Settings page shell: header + tab menu (left on desktop, a scrolling strip on phones) + content. Tabs drive the component's `$tab` property. --}}
@php
    $activeClass = $accent === 'indigo'
        ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200'
        : 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200';
    $activeIcon = $accent === 'indigo' ? 'text-indigo-600' : 'text-emerald-600';
@endphp

<div class="mx-auto w-full max-w-6xl space-y-6 px-4 py-8 sm:px-6">
    <header>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-slate-500">{{ $subtitle }}</p>
        @endif
    </header>

    <div class="grid gap-6 lg:grid-cols-[15rem_minmax(0,1fr)]">
        <nav aria-label="Settings sections" class="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1 lg:sticky lg:top-6 lg:mx-0 lg:flex-col lg:self-start lg:overflow-visible lg:px-0 lg:pb-0">
            @foreach ($tabs as $key => $tab)
                <button type="button" wire:click="$set('tab', '{{ $key }}')" wire:key="tab-{{ $key }}"
                    @if ($active === $key) aria-current="page" @endif
                    class="flex shrink-0 items-center gap-3 rounded-xl px-4 py-2.5 text-left text-sm font-medium transition {{ $active === $key ? $activeClass : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <i class="fa-solid {{ $tab['icon'] }} w-4 text-center {{ $active === $key ? $activeIcon : 'text-slate-400' }}"></i>
                    <span class="whitespace-nowrap">{{ $tab['label'] }}</span>
                </button>
            @endforeach
        </nav>

        <div class="min-w-0 space-y-6">
            {{ $slot }}
        </div>
    </div>
</div>
