@props(['title', 'description' => null])

<section {{ $attributes->class(['rounded-2xl border border-slate-200 bg-white shadow-sm']) }}>
    <div class="border-b border-slate-100 px-6 py-4">
        <h2 class="text-base font-semibold text-slate-900">{{ $title }}</h2>
        @if ($description)
            <p class="mt-0.5 text-sm text-slate-500">{{ $description }}</p>
        @endif
    </div>

    <div class="px-6 py-5">{{ $slot }}</div>

    @isset($footer)
        <div class="flex flex-wrap items-center justify-end gap-3 rounded-b-2xl border-t border-slate-100 bg-slate-50/70 px-6 py-3">{{ $footer }}</div>
    @endisset
</section>
