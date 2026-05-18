<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <title>{{ $title ?? 'Page Title' }}</title>
</head>

<body>
    @include('common.message')
    <div class="flex h-screen" x-data="navbar()">
        <div class="w-70 bg-gray-800 min-h-screen sticky top-0 duration-200" :class="open? 'w-70 duration-200': 'w-[0%] duration-200'">
            @include('component.vendor.header')
        </div>

        <div class="flex-1 overflow-auto">
            <div class="py-3 shadow-lg sticky">
                @include('component.vendor.topbar')
                
            </div>
            <div class="px-5 py-2 text-sm">
                {{ $slot }}
            </div>
        </div>
    </div>


</body>

</html>
