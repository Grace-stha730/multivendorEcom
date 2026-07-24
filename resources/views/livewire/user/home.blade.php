<div class="">
    @include('component.user.carousel')
    @include('component.user.coupon-card')

    @if ($recommendations->isNotEmpty())
        <section class="w-[90%] md:w-[80%] mx-auto my-8">
            <h2 class="text-2xl md:text-3xl font-semibold text-gray-800 mb-2 text-center">Recommended for You</h2>
            <p class="text-sm text-gray-500 mb-6 text-center">Based on products purchased by customers with similar buying patterns.</p>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5">
                @foreach ($recommendations as $product)
                    <a href="{{ route('product.detail', ['id' => $product->id]) }}" class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-lg hover:scale-[1.03] transition-all duration-300 border border-gray-100">
                        <img src="{{ $product->firstImage ? asset('storage/' . $product->firstImage->url) : asset('storage/default/product.webp') }}" alt="{{ $product->name }}" class="w-full h-40 object-cover">
                        <div class="p-3 text-center space-y-1">
                            <h3 class="font-semibold text-gray-800 truncate">{{ $product->name }}</h3>
                            <p class="text-xs text-yellow-600"><i class="fa-solid fa-star"></i> {{ number_format($product->weighted_rating, 1) }}</p>
                            <p class="text-base font-bold text-gray-800">Rs. {{ number_format($product->price) }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="w-[90%] md:w-[80%] mx-auto my-8">
        <h2 class="text-2xl md:text-3xl font-semibold text-gray-800 mb-6 text-center">
            Our Products
        </h2>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5">
               
            @foreach ($products as $product)
                <div
                    class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-lg hover:scale-[1.03] transition-all duration-300 border border-gray-100">

                    {{-- Product Image --}}
                 
                    <div class="relative">
                        @if ($product->firstImage)
                            <img src="{{ asset('storage/' . $product->firstImage->url) }}" alt="{{ $product->name }}"
                                class="w-full h-48 object-cover">
                        @else
                            <img src="{{ asset('storage/default/product.webp') }}" alt="{{ $product->name }}"
                                class="w-full h-48 object-cover">
                        @endif

                        @if ($product->discount)
                            <span
                                class="absolute top-2 left-2 bg-red-600 text-white text-xs font-semibold px-2 py-1 rounded-full">
                                -{{ $product->discount }}%
                            </span>
                        @endif

                        <p
                            class="absolute top-2 right-2 bg-green-600 text-white text-xs font-semibold px-2 py-1 rounded-full">
                            {{ $product->vendor->shop_name }}</p>

                    </div>

                    {{-- Product Details --}}
                    <div class="p-3 text-center space-y-1">
                        <h3 class="font-semibold text-gray-800 truncate">{{ $product->name }}</h3>

                        <p class="text-xs text-yellow-600"><i class="fa-solid fa-star"></i> {{ number_format($product->weighted_rating, 1) }} <span class="text-gray-400">({{ $product->reviews_count }} reviews)</span></p>

                        @if ($product->stock > 0)
                            <p class="text-sm text-gray-500">Stock: {{ $product->stock }}</p>
                        @else
                            <p class="text-sm text-red-500">Out of Stock</p>
                        @endif

                        @if ($product->discount)
                            <p class="text-gray-500 line-through text-sm">
                                Rs. {{ $product->price }}
                            </p>
                            <p class="text-lg font-bold text-green-600">
                                Rs. {{ $product->price - ($product->price * $product->discount) / 100 }}
                            </p>
                        @else
                            <p class="text-lg font-bold text-gray-800">Rs. {{ $product->price }}</p>
                        @endif

                        {{-- Action Buttons --}}
                        <div class="flex justify-center space-x-3 mt-3">
                            <a href="{{ route('product.detail', ['id' => $product->id]) }}"
                                class="bg-blue-600 text-white text-sm px-3 py-1.5 rounded-md hover:bg-blue-700 transition cursor-pointer">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <button
                                class="bg-green-600 text-white text-sm px-3 py-1.5 rounded-md hover:bg-green-700 transition cursor-pointer">
                                <i class="fa-solid fa-cart-plus"
                                    wire:click.prevent='AddToCart({{ $product->id }})'></i>
                            </button>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    </section>
</div>
