<div class="bg-gray-100 min-h-screen p-6">

    <!-- Vendor Info -->
    <div class="max-w-6xl mx-auto p-6 bg-white rounded-2xl shadow-lg flex flex-col md:flex-row items-center gap-6">
        <img src="{{ asset('storage/' . $vendor->shop_image) }} " alt="Vendor Logo"
            class="w-32 h-32 rounded-full object-cover">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">{{ $vendor->shop_name }}</h1>
            <p class="text-gray-600 mt-2">We sell high-quality products ranging from electronics to fashion accessories.
                Visit us for the best deals!</p>
            <div class="mt-4 flex items-center gap-4">
                <span class="text-gray-700 font-semibold">Rating:</span>
                <div class="flex gap-1 ">
                    @for ($i = 1; $i <= 5; $i++)
                        <i class="fa-solid fa-star {{ ($averageRate ?? 0) >= $i ? 'text-yellow-400' : 'text-gray-400' }}"></i>
                    @endfor
                    <span class="ms-2">({{ $averageRate }})</span>
                </div>
            </div>
            <div class="mt-4 text-gray-600">
                <p>Email: {{ $vendor->shop_email }}</p>
                <p>Phone: +977-{{ $vendor->shop_phone }}</p>
                <p>Province: {{ $vendor->shop_province }}</p>
                <p>Address: {{ $vendor->shop_city }}, {{ $vendor->shop_tole }}</p>
            </div>
        </div>
    </div>

    <!-- Products -->
    <div class="max-w-6xl mx-auto mt-10">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Products</h2>
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <!-- Product Card  -->
            @foreach ($vendor->products as $item)
                <div class="bg-white rounded-2xl shadow p-4 flex flex-col relative">
                    @if ($item->discount)
                        <span
                            class="absolute top-5 right-5 bg-red-600 text-white text-xs font-semibold px-2 py-1 rounded-full">
                            -{{ $item->discount }}%
                        </span>
                    @endif
                    <img src="{{ asset('storage/' . $item->firstImage->url) }}" alt="Product 1"
                        class="rounded-xl w-full h-35 object-cover mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">{{ $item->name }}</h3>
                    <p class="text-gray-600 mt-2">Rs.
                        @if ($item->discount)
                            <span class="line-through">{{ $item->price }}</span> <span
                                class="text-green-700">{{ $item->price - $item->discount_amount }}</span>
                        @else
                            <span>$item->price</span>
                        @endif
                    </p>
                    <p class="text-gray-600 ">Stock: {{ $item->stock }}</p>
                    <a href="{{ route('product.detail', ['id' => $item->id]) }}"
                        class="mt-auto text-center cursor-pointer bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition">View
                        Details</a>
                    <button wire:click.prevent='AddToCart({{ $item->id }})'
                        class="mt-1.5 text-center cursor-pointer bg-green-600 text-white px-4 py-2 rounded-xl hover:bg-green-700 transition">Add
                        to Cart</button>
                </div>
            @endforeach
        </div>
    </div>

</div>
