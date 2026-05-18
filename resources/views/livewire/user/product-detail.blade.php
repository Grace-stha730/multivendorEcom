<section class="" x-data="{ mainImage: '{{ asset('storage/' . $mainImage) }}' }">
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
            <p class="text-green-500">{{ $product->vendor->shop_name }}</p>
            <p class="text-gray-600">{{ $product->summary }}</p>

            <!-- Price -->
            <p class="text-lg font-semibold">
                Price:
                @if ($product->discount)
                    <span class="line-through text-gray-400">Rs. {{ $product->price }}</span>
                    <span class="text-red-600 ml-2">
                        Rs. {{ $product->price - ($product->price * $product->discount) / 100 }}
                    </span>
                @else
                    <span class="text-gray-800">Rs. {{ $product->price }}</span>
                @endif
            </p>

            <!-- Stock Info -->
            <p class="text-sm text-gray-500">Available Stock:
                <span class="font-medium text-gray-800">{{ $product->stock }}</span>
            </p>

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
            <div class="flex space-x-3">
                <button class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-900 duration-150 cursor-pointer"
                    wire:click.prevent='addToCart'>
                    Add to Cart
                </button>
                <button
                    class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 duration-150 cursor-pointer">
                    Wishlist
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
    <div>
        @livewire('user.product', ['limit' => 5])
    </div>
</section>
