<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>{{ $title ?? 'Admin' }}</title>
</head>
<body class="bg-slate-50 antialiased">
    @include('common.message')
    <div x-data="{ sidebarOpen: false }" class="min-h-screen">
        <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-950/50 lg:hidden" @click="sidebarOpen = false"></div>
        <aside class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col overflow-y-auto bg-slate-950 px-4 py-5 text-slate-300 shadow-2xl transition-transform duration-300 lg:translate-x-0" :class="sidebarOpen && 'translate-x-0'">@include('component.admin.header')</aside>
        <main class="min-h-screen lg:pl-72">
            <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur-xl">@include('component.admin.topbar')</header>
            <div class="mx-auto max-w-[1600px] px-4 py-6 sm:px-6 lg:px-8">{{ $slot }}</div>
        </main>
    </div>
    @livewireScripts
    <x-toast position="toast-bottom toast-end" />
</body>
</html>
