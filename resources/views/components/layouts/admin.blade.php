<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    

    <title>{{ $title ?? 'Page Title' }}</title>
</head>

<body class="bg-[#f5f8f6] antialiased">
    @include('common.message')
    <div class="flex min-h-screen" x-data="navbar()">
        <div class="w-70 bg-gray-800 min-h-screen sticky top-0 duration-200 shadow-2xl" :class="open? 'w-70 duration-200': 'w-[0%] duration-200'">
            @include('component.admin.header')
        </div>

        <div class="flex-1 overflow-auto">
            <div class="py-4 bg-white/90 backdrop-blur border-b border-emerald-950/5 sticky top-0 z-30">
                @include('component.admin.topbar')
                
            </div>
            <div class="max-w-[1600px] mx-auto px-5 py-6 text-sm">
                {{ $slot }}
            </div>
        </div>
    </div>


    @livewireScripts
</body>

</html>
