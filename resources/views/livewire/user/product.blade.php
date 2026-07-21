<section class="w-[95%] md:w-[85%] mx-auto my-10">
    @include('common.message')

    {{-- Search & Filter Bar --}}
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Our Products</h2>
            <p class="text-sm text-gray-500 mt-1">Browse our wide selection of items from various vendors</p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
            {{-- Category Filter --}}
            <select wire:model.live="category"
                class="border border-gray-300 rounded-full py-2 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                <option value="">All Categories</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>

            {{-- Search Input --}}
            <div class="relative w-full sm:w-64">
                <input type="text" placeholder="Search product..."
                    class="border border-gray-300 rounded-full py-2 pl-4 pr-10 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                    wire:model.live="search">
                <i class="fa-solid fa-magnifying-glass absolute right-3 top-2.5 text-gray-400"></i>
            </div>
        </div>
    </div>

    {{-- Products Grid --}}
    @if ($products->count() > 0)
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
            @foreach ($products as $product)
                @php
                    $isWishlisted = in_array($product->id, $userWishlistProductIds);
                @endphp
                <div
                    class="bg-white rounded-xl overflow-hidden shadow-md hover:shadow-lg hover:scale-[1.02] transition-all duration-300 border border-gray-100 flex flex-col justify-between">

                    <div>
                        {{-- Product Image & Badges --}}
                        <div class="relative">
                            <a href="{{ route('product.detail', ['id' => $product->id]) }}">
                                <img src="{{ $product->firstImage ? asset('storage/' . $product->firstImage->url) : asset('storage/default/product.webp') }}"
                                    alt="{{ $product->name }}" class="w-full h-48 object-cover">
                            </a>

                            @if ($product->discount)
                                <span
                                    class="absolute top-2 left-2 bg-red-600 text-white text-xs font-semibold px-2 py-1 rounded-full">
                                    -{{ $product->discount }}%
                                </span>
                            @endif

                            @if ($product->vendor)
                                <p
                                    class="absolute top-2 right-2 bg-green-600 text-white text-xs font-semibold px-2 py-1 rounded-full">
                                    {{ $product->vendor->shop_name }}
                                </p>
                            @endif

                            {{-- Quick Wishlist Toggle Badge --}}
                            <button wire:click.prevent="toggleWishlist({{ $product->id }})"
                                title="{{ $isWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist' }}"
                                class="absolute bottom-2 right-2 bg-white/90 hover:bg-white text-red-500 rounded-full w-8 h-8 flex items-center justify-center shadow transition duration-200 cursor-pointer">
                                <i class="{{ $isWishlisted ? 'fa-solid fa-heart' : 'fa-regular fa-heart' }}"></i>
                            </button>
                        </div>

                        {{-- Product Details --}}
                        <div class="p-3 text-center space-y-2">
                            <h3 class="font-semibold text-gray-800 truncate" title="{{ $product->name }}">
                                <a href="{{ route('product.detail', ['id' => $product->id]) }}" class="hover:text-blue-600">
                                    {{ $product->name }}
                                </a>
                            </h3>

                            @if ($product->stock > 0)
                                <p class="text-xs text-gray-500">Stock: {{ $product->stock }}</p>
                            @else
                                <p class="text-xs text-red-500 font-semibold">Out of Stock</p>
                            @endif

                            @if ($product->discount)
                                <p class="text-gray-400 line-through text-xs">
                                    Rs. {{ $product->price }}
                                </p>
                                <p class="text-base font-bold text-green-600">
                                    Rs. {{ $product->price - ($product->price * $product->discount) / 100 }}
                                </p>
                            @else
                                <p class="text-base font-bold text-gray-800">Rs. {{ $product->price }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="p-3 pt-0 flex justify-center gap-2">
                        <a href="{{ route('product.detail', ['id' => $product->id]) }}"
                            title="View Details"
                            class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1.5 rounded-full transition flex items-center gap-1">
                            <i class="fa-solid fa-eye"></i> Details
                        </a>
                        <button wire:click.prevent="AddToCart({{ $product->id }})"
                            title="Add to Cart"
                            class="bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1.5 rounded-full transition flex items-center gap-1 cursor-pointer">
                            <i class="fa-solid fa-cart-plus"></i> Cart
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="flex flex-col items-center justify-center h-96 text-center">
            <img class="w-48 mb-4 opacity-75" src="{{ asset('storage/default/noProduct.png') }}" alt="No Products">
            <p class="text-gray-600 text-lg">
                No products found{{ $search ? " for '{$search}'" : '' }}{{ $category ? ' in selected category' : '' }}.
            </p>
            @if ($search || $category)
                <button wire:click="$set('search', ''); $set('category', '');"
                    class="mt-4 text-sm text-blue-600 hover:underline font-medium cursor-pointer">
                    Clear Filters
                </button>
            @endif
        </div>
    @endif
</section>
