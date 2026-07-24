<section class="" x-data="{ mainImage: '{{ asset('storage/' . $mainImage) }}' }">
    <div class="w-[90%] md:w-[80%] mx-auto mt-4">
        @include('common.message')
    </div>
    <div class="w-[90%] md:w-[80%] mx-auto my-10 grid md:grid-cols-2 gap-6">
        <!-- Left: Product Images -->
        <div>
            <!-- Main Image -->
            <img :src="mainImage" class="w-full h-96 object-cover rounded-lg mb-3" alt="Product">

            <!-- Thumbnail Images -->
            <div class="flex space-x-2 flex-wrap space-y-2">
                @foreach ($product->images as $img)
                    <img src="{{ asset('storage/' . $img->url) }}"
                        class="w-20 h-20 object-cover rounded cursor-pointer hover:opacity-80 duration-150"
                        @click.prevent="mainImage='{{ asset('storage/' . $img->url) }}'">
                @endforeach
            </div>
        </div>

        <!-- Right: Product Info -->
        <div class="space-y-3">
            <h2 class="text-3xl font-bold">{{ $product->name }}</h2>
            @for ($i = 1; $i <= 5; $i++)
                <i class="fa-solid fa-star {{ ($averateRate ?? 0) >= $i ? 'text-yellow-400' : 'text-gray-400' }}"></i>
                @endfor
                <span class="ms-2">({{ $averateRate }})</span>
            <p class="text-sm text-yellow-600"><i class="fa-solid fa-ranking-star"></i> Weighted rating: {{ number_format($weightedRating, 1) }}</p>
            <p class="text-green-500">{{ $product->vendor->shop_name }}</p>
            <p class="text-gray-600">{{ $product->summary }}</p>

            <!-- Price (Dynamic based on selected variants) -->
            @php
                $currentBasePrice = $product->price;
                foreach ($selectedVariants as $name => $val) {
                    if (isset($availableVariants[$name])) {
                        foreach ($availableVariants[$name] as $var) {
                            if ($var['attribute_value'] === $val) {
                                $currentBasePrice += $var['price_extra'];
                            }
                        }
                    }
                }
                $currentFinalPrice = $product->discount
                    ? $currentBasePrice - ($currentBasePrice * $product->discount / 100)
                    : $currentBasePrice;
            @endphp
            <p class="text-lg font-semibold">
                Price:
                @if ($product->discount)
                    <span class="line-through text-gray-400">Rs. {{ number_format($currentBasePrice, 2) }}</span>
                    <span class="text-red-600 ml-2 font-bold">
                        Rs. {{ number_format($currentFinalPrice, 2) }}
                    </span>
                    <span class="text-xs bg-red-100 text-red-800 font-semibold px-2 py-0.5 rounded-full ml-1">
                        {{ $product->discount }}% OFF
                    </span>
                @else
                    <span class="text-gray-800 font-bold">Rs. {{ number_format($currentBasePrice, 2) }}</span>
                @endif
            </p>

            <!-- Stock Info -->
            <p class="text-sm text-gray-500">Available Stock:
                <span class="font-medium text-gray-800">{{ $product->stock }}</span>
            </p>

            <!-- Product Variants Selectors -->
            @if (count($availableVariants) > 0)
                <div class="space-y-3 py-3 border-t border-b border-gray-100 my-4">
                    @foreach ($availableVariants as $name => $values)
                        <div class="flex items-center space-x-3">
                            <span class="text-gray-600 font-semibold text-xs w-16 uppercase tracking-wider">{{ $name }}:</span>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($values as $var)
                                    <button type="button" wire:click="$set('selectedVariants.{{ $name }}', '{{ $var['attribute_value'] }}')"
                                        @class([
                                            'px-3 py-1 rounded-full text-xs font-medium border transition cursor-pointer shadow-sm',
                                            'bg-indigo-600 border-indigo-600 text-white' => ($selectedVariants[$name] ?? '') === $var['attribute_value'],
                                            'bg-gray-100 border-gray-200 text-gray-700 hover:bg-gray-200' => ($selectedVariants[$name] ?? '') !== $var['attribute_value'],
                                        ])>
                                        {{ $var['attribute_value'] }}
                                        @if ($var['price_extra'] > 0)
                                            <span class="text-[9px] opacity-80 font-normal">(+ Rs. {{ number_format($var['price_extra']) }})</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Quantity Input -->
            <div class="flex items-center space-x-3">
                <label for="quantity" class="text-gray-700 font-medium">Quantity:</label>
                <input type="number" id="quantity" min="1" max="{{ $product->stock }}" wire:model='quantity'
                    class="w-20 border border-gray-300 rounded-lg px-3 py-1 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent text-center">
                @error('quantity')
                    <small class="text-red-500">{{ $message }}</small>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3">
                <button class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-900 duration-150 cursor-pointer flex items-center gap-2"
                    wire:click.prevent='addToCart'>
                    <i class="fa-solid fa-cart-plus"></i> Add to Cart
                </button>
                <button wire:click.prevent="toggleWishlist"
                    class="{{ $inWishlist ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' }} px-4 py-2 rounded duration-150 cursor-pointer flex items-center gap-2">
                    <i class="{{ $inWishlist ? 'fa-solid fa-heart' : 'fa-regular fa-heart' }}"></i>
                    <span>{{ $inWishlist ? 'In Wishlist' : 'Add to Wishlist' }}</span>
                </button>
                <button wire:click.prevent="askQuestion"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded duration-150 cursor-pointer flex items-center gap-2">
                    <i class="fa-solid fa-comments"></i>
                    <span>Ask Vendor a Question</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Product Details -->
    <div class="w-[90%] md:w-[80%] mx-auto my-10">
        <div class="mt-5">
            <h3 class="font-semibold text-xl mb-2">Product Details:</h3>
            <p class="text-gray-700">{{ $product->description }}</p>
        </div>

        @livewire('user.vendor', ['productId' => $product->id])

        @livewire('user.view-review', ['productId' => $product->id])

    </div>

    <!-- Related Products -->
    @if ($recommendations->isNotEmpty())
        <div class="w-[90%] md:w-[80%] mx-auto my-10">
            <h3 class="text-2xl font-semibold text-gray-800 mb-5">Customers with Similar Purchases Also Like</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                @foreach ($recommendations as $recommended)
                    <a href="{{ route('product.detail', ['id' => $recommended->id]) }}" class="rounded-lg border border-gray-100 bg-white overflow-hidden shadow-sm hover:shadow-md transition">
                        <img src="{{ $recommended->firstImage ? asset('storage/' . $recommended->firstImage->url) : asset('storage/default/product.webp') }}" alt="{{ $recommended->name }}" class="w-full h-36 object-cover">
                        <div class="p-3">
                            <p class="font-medium text-sm text-gray-800 truncate">{{ $recommended->name }}</p>
                            <p class="text-xs text-yellow-600"><i class="fa-solid fa-star"></i> {{ number_format($recommended->weighted_rating, 1) }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</section>
