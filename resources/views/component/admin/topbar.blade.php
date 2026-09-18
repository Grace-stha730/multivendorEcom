@php($admin = Auth::guard('admin')->user())
<div class="flex h-18 items-center justify-between px-4 sm:px-6 lg:px-8">
    <div class="flex items-center gap-3"><button type="button" @click="sidebarOpen = true" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 text-slate-600 lg:hidden"><i class="fa-solid fa-bars"></i></button><div><p class="text-xs font-medium text-slate-400">Administration</p><h1 class="text-sm font-bold text-slate-800">{{ $title ?? 'Dashboard' }}</h1></div></div>
    <div class="flex items-center gap-3"><span class="hidden text-right sm:block"><span class="block text-sm font-semibold text-slate-700">{{ $admin?->name }}</span><span class="block text-xs text-slate-400">Admin account</span></span><div class="grid h-10 w-10 place-items-center rounded-xl bg-slate-100 text-slate-600"><i class="fa-solid fa-user-shield"></i></div></div>
</div>
