<section class="bg-gray-100 min-h-screen py-10">
    <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-md p-6">
        @include('common.message')

        <div class="flex flex-col md:flex-row justify-between items-center mb-6 border-b pb-4 gap-4">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-heart text-red-500"></i> My Wishlist
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $wishlistItems->count() }} {{ Str::plural('item', $wishlistItems->count()) }} saved in your wishlist
                </p>
            </div>
            @if ($wishlistItems->count() > 0)
                <button wire:click="clearAll"
                    wire:confirm="Are you sure you want to clear your entire wishlist?"
                    class="bg-red-50 text-red-600 hover:bg-red-100 text-sm px-4 py-2 rounded-lg font-medium transition duration-150 flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-trash-can"></i> Clear Wishlist
                </button>
            @endif
        </div>

        @if ($wishlistItems->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($wishlistItems as $item)
                    @if ($item->product)
                        <div class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-md border border-gray-100 transition duration-200 flex flex-col justify-between">
                            <div>
                                {{-- Product Image & Badges --}}
                                <div class="relative">
                                    <a href="{{ route('product.detail', ['id' => $item->product->id]) }}">
                                        <img src="{{ asset('storage/' . ($item->product->firstImage->url ?? 'default/product.webp')) }}"
                                            alt="{{ $item->product->name }}" class="w-full h-48 object-cover hover:scale-105 transition duration-300">
                                    </a>

                                    @if ($item->product->discount)
                                        <span class="absolute top-2 left-2 bg-red-600 text-white text-xs font-semibold px-2 py-1 rounded-full">
                                            -{{ $item->product->discount }}%
                                        </span>
                                    @endif

                                    @if ($item->product->vendor)
                                        <span class="absolute top-2 right-2 bg-green-600 text-white text-xs font-semibold px-2 py-1 rounded-full">
                                            {{ $item->product->vendor->shop_name }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Details --}}
                                <div class="p-4 space-y-2">
                                    <h3 class="font-semibold text-gray-800 text-lg truncate">
                                        <a href="{{ route('product.detail', ['id' => $item->product->id]) }}" class="hover:text-blue-600">
                                            {{ $item->product->name }}
                                        </a>
                                    </h3>

                                    @if ($item->product->stock > 0)
                                        <p class="text-xs text-green-600 font-medium">In Stock ({{ $item->product->stock }} left)</p>
                                    @else
                                        <p class="text-xs text-red-500 font-semibold">Out of Stock</p>
                                    @endif

                                    {{-- Pricing --}}
                                    <div class="pt-1">
                                        @if ($item->product->discount)
                                            <div class="flex items-center space-x-2">
                                                <span class="text-lg font-bold text-gray-900">
                                                    Rs. {{ $item->product->price - ($item->product->price * $item->product->discount) / 100 }}
                                                </span>
                                                <span class="text-sm text-gray-400 line-through">
                                                    Rs. {{ $item->product->price }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-lg font-bold text-gray-900">
                                                Rs. {{ $item->product->price }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="p-4 border-t border-gray-100 flex items-center justify-between gap-2 bg-gray-50/50">
                                <button wire:click="moveToCart({{ $item->id }})"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-3 rounded-lg transition duration-150 flex items-center justify-center gap-2 cursor-pointer">
                                    <i class="fa-solid fa-cart-shopping"></i> Move to Cart
                                </button>
                                <button wire:click="removeFromWishlist({{ $item->id }})"
                                    title="Remove item"
                                    class="bg-gray-200 hover:bg-red-500 hover:text-white text-gray-600 text-sm p-2 rounded-lg transition duration-150 cursor-pointer">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <div class="w-20 h-20 bg-red-50 text-red-400 rounded-full flex items-center justify-center mb-4 text-3xl">
                    <i class="fa-regular fa-heart"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">Your wishlist is empty</h3>
                <p class="text-gray-500 max-w-sm mb-6">Explore our products and save your favorite items to view or purchase later!</p>
                <a href="{{ route('user.product') }}"
                    class="bg-gray-800 text-white px-6 py-2.5 rounded-lg hover:bg-gray-900 transition duration-150 font-medium inline-flex items-center gap-2">
                    <i class="fa-solid fa-store"></i> Explore Products
                </a>
            </div>
        @endif
    </div>
</section>
