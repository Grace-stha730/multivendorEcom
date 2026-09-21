@php
    $admin = Auth::guard('admin')->user();
    $items = [
        ['Dashboard', 'fa-chart-pie', route('admin.dashboard'), ['admin/dashboard']],
        ['Products', 'fa-box', route('admin.product'), ['admin/products', 'admin/product-detail*']],
        ['Categories', 'fa-tags', route('admin.category'), ['admin/category']],
        ['Orders', 'fa-bag-shopping', route('admin.order'), ['admin/order', 'admin/order-detail*']],
        ['Shops', 'fa-store', route('admin.shops'), ['admin/shops']],
        ['Shop Registrations', 'fa-clipboard-check', route('admin.shop-registrations'), ['admin/shop-registrations']],
        ['Messages', 'fa-envelope', route('admin.message'), ['admin/message', 'admin/message-datail*']],
        ['Coupons', 'fa-ticket', route('admin.coupons'), ['admin/coupons']],
        ['Payouts', 'fa-hand-holding-dollar', route('admin.payouts'), ['admin/payouts']],
    ];
    if (authorizeUserCheck('admin-user-manage', 'admin')) {
        $items[] = ['Admin Users', 'fa-user-shield', route('admin.users'), ['admin/users']];
    }
@endphp
<div class="flex h-full min-h-0 w-full flex-col">
    <a wire:navigate href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-2 text-white">
        <span class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-400 text-lg text-slate-950"><i class="fa-solid fa-shield-halved"></i></span>
        <span><span class="block text-base font-bold tracking-tight">MarketFlow</span><span class="block text-[10px] font-semibold uppercase tracking-[.18em] text-emerald-300">Admin console</span></span>
    </a>
    <div class="mt-7 flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 p-3">
        @if ($admin?->image)<img src="{{ asset('storage/' . $admin->image) }}" class="h-10 w-10 rounded-xl object-cover" alt="{{ $admin->name }}">@else<div class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-400/15 font-bold text-emerald-300">{{ Str::upper(Str::substr($admin?->name, 0, 1)) }}</div>@endif
        <div class="min-w-0"><p class="truncate text-sm font-semibold text-white">{{ $admin?->name }}</p><p class="truncate text-xs text-slate-400">Administrator</p></div>
    </div>
    <p class="mt-7 px-3 text-[10px] font-bold uppercase tracking-[.18em] text-slate-500">Workspace</p>
    <nav class="mt-3 space-y-1">
        @foreach ($items as [$label, $icon, $route, $patterns])
            <a wire:navigate href="{{ $route }}" @class(['flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition', 'bg-emerald-400 text-slate-950 shadow-lg shadow-emerald-950/20' => request()->is(...$patterns), 'text-slate-300 hover:bg-white/8 hover:text-white' => !request()->is(...$patterns)])><i class="fa-solid {{ $icon }} w-5 text-center"></i>{{ $label }}</a>
        @endforeach
    </nav>
    <div class="mt-auto border-t border-white/10 pt-4"><a wire:navigate href="{{ route('admin.setting') }}" @class(['flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition', 'bg-white/10 text-white' => request()->is('admin/setting'), 'text-slate-300 hover:bg-white/8 hover:text-white' => !request()->is('admin/setting')])><i class="fa-solid fa-gear w-5 text-center"></i>Settings</a><form action="{{ route('admin.logout') }}" method="POST" class="mt-1">@csrf<button class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-rose-300 transition hover:bg-rose-400/10 hover:text-rose-200"><i class="fa-solid fa-arrow-right-from-bracket w-5 text-center"></i>Sign out</button></form></div>
</div>
