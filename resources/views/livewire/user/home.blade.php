<div class="">
    @include('component.user.carousel')
    @include('component.user.coupon-card')

    <section class="mx-auto my-8 w-[90%] overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-950 via-emerald-800 to-teal-700 text-white shadow-lg md:w-[80%]">
        <div class="flex flex-col items-start justify-between gap-6 px-6 py-8 sm:px-10 md:flex-row md:items-center">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-emerald-100">
                    <i class="fa-solid fa-store"></i> Grow with us
                </span>
                <h2 class="mt-3 text-2xl font-bold sm:text-3xl">Sell your products on our marketplace</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-emerald-50/80">Manage your shop, products, orders, and earnings from one simple vendor workspace.</p>
            </div>
            <a href="{{ route('shop-user.login') }}" class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-semibold text-emerald-900 shadow-sm transition hover:bg-emerald-50" wire:navigate>
                Vendor login <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </section>

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
            Top Rated Products
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
                            {{ $product->shop->name }}</p>

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

    @if ($topRatedShops->isNotEmpty())
        <section class="w-[90%] md:w-[80%] mx-auto my-10">
            <h2 class="text-2xl md:text-3xl font-semibold text-gray-800 mb-6 text-center">Top Rated Shops</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach ($topRatedShops as $shop)
                    <div class="rounded-xl border border-gray-100 bg-white p-4 text-center shadow-sm">
                        <img src="{{ $shop->image ? asset('storage/' . $shop->image) : asset('storage/default/product.webp') }}" alt="{{ $shop->name }}" class="mx-auto mb-3 h-16 w-16 rounded-full object-cover">
                        <p class="truncate font-semibold text-gray-800">{{ $shop->name }}</p>
                        <p class="mt-1 text-xs text-yellow-600"><i class="fa-solid fa-star"></i> {{ number_format($shop->weighted_rating, 1) }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
