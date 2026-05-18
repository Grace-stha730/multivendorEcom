<div class="x-1500">
    @if (session('success'))
        <div class="fixed top-15 right-5 bg-green-500 text-white px-4 py-2 rounded shadow z-100 text-sm"
            x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="fixed top-15 right-5 bg-red-500 text-white px-4 py-2 rounded shadow text-sm"
        x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
            {{ session('error') }}
        </div>
    @endif
</div>
