<section class="w-[95%] md:w-[85%] mx-auto my-10">
    {{-- Search & Filter --}}
    <div class="text-end mb-8">
        {{-- Search Input --}}
        <input type="text" placeholder="Search Product..."
            class="border border-gray-300 rounded-full py-2 px-4 w-full md:w-1/4 focus:outline-none focus:ring-2 focus:ring-blue-500"
            wire:model.live="search">

    </div>

    {{-- Title --}}
    <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Our Products</h2>

    {{-- Products Grid --}}
    @if ($products->count() > 0)
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
            @foreach ($products as $product)
                <div
                    class="bg-white rounded-xl overflow-hidden shadow-md hover:shadow-lg hover:scale-[1.03] transition-all duration-300 border border-gray-100">

                    {{-- Product Image --}}
                    <div class="relative">
                        <img src="{{ $product->firstImage ? asset('storage/' . $product->firstImage->url) : asset('storage/default/product.webp') }}"
                            alt="{{ $product->name }}" class="w-full h-48 object-cover">

                        @if ($product->discount)
                            <span
                                class="absolute top-2 left-2 bg-red-600 text-white text-xs font-semibold px-2 py-1 rounded-full">
                                -{{ $product->discount }}%
                            </span>
                        @endif

                        <p
                            class="absolute top-2 right-2 bg-green-600 text-white text-xs font-semibold px-2 py-1 rounded-full px-2">
                            {{ $product->vendor->shop_name }}
                        </p>
                    </div>

                    {{-- Product Details --}}
                    <div class="p-3 text-center space-y-2">
                        <h3 class="font-semibold text-gray-800 truncate">{{ $product->name }}</h3>

                        @if ($product->stock > 0)
                            <p class="text-sm text-gray-500">Stock: {{ $product->stock }}</p>
                        @else
                            <p class="text-sm text-red-500 font-semibold">Out of Stock</p>
                        @endif

                        @if ($product->discount)
                            <p class="text-gray-400 line-through text-sm">
                                Rs. {{ $product->price }}
                            </p>
                            <p class="text-lg font-bold text-green-600">
                                Rs. {{ $product->price - ($product->price * $product->discount) / 100 }}
                            </p>
                        @else
                            <p class="text-lg font-bold text-gray-800">Rs. {{ $product->price }}</p>
                        @endif

                        {{-- Action Buttons --}}
                        <div class="flex justify-center gap-2 mt-3">
                            <a href="{{ route('product.detail', ['id' => $product->id]) }}"
                                class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-1.5 rounded-full transition">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <button wire:click.prevent="AddToCart({{ $product->id }})"
                                class="bg-green-600 hover:bg-green-700 text-white text-sm px-3 py-1.5 rounded-full transition">
                                <i class="fa-solid fa-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="flex flex-col items-center justify-center h-96 text-center">
            <img class="w-48 mb-4" src="{{ asset('storage/default/noProduct.png') }}" alt="No Products">
            <p class="text-gray-600 text-lg">
                No products found{{ $search ? " for '{$search}'" : '' }}{{ $category ? ' in this category' : '' }}.
            </p>
        </div>
    @endif
</section>
