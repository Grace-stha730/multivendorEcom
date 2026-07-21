<section class="w-[92%] lg:w-[82%] mx-auto my-10 space-y-8">
    <div class="grid lg:grid-cols-3 gap-6">
        <form wire:submit.prevent="createCollection" class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm space-y-4">
            <h2 class="text-xl font-semibold text-gray-800">Create Collection</h2>
            <div>
                <label class="block text-sm text-gray-700 mb-1">Name</label>
                <input type="text" wire:model="name"
                    class="w-full border border-gray-300 rounded-md p-2 focus:ring focus:ring-blue-200">
                @error('name')
                    <small class="text-red-700">{{ $message }}</small>
                @enderror
            </div>
            <div>
                <label class="block text-sm text-gray-700 mb-1">Description</label>
                <textarea wire:model="description" rows="3"
                    class="w-full border border-gray-300 rounded-md p-2 focus:ring focus:ring-blue-200"></textarea>
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" wire:model="isPublic">
                Public list
            </label>
            <button class="w-full bg-gray-800 text-white rounded-md py-2 hover:bg-gray-900 cursor-pointer">
                Create
            </button>
        </form>

        <div class="lg:col-span-2 bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-end gap-3">
                <div class="flex-1">
                    <h2 class="text-xl font-semibold text-gray-800">Product of the Day</h2>
                    <p class="text-sm text-gray-500 mt-1">Adds the highest ranked product from clustered search into one of your lists.</p>
                </div>
                <select wire:model="selectedCollectionId"
                    class="border border-gray-300 rounded-md p-2 md:w-64 focus:ring focus:ring-blue-200">
                    <option value="">Choose collection</option>
                    @foreach ($myCollections as $collection)
                        <option value="{{ $collection->id }}">{{ $collection->name }}</option>
                    @endforeach
                </select>
                <button wire:click="generateProductOfTheDay"
                    class="bg-indigo-600 text-white rounded-md px-4 py-2 hover:bg-indigo-700 cursor-pointer">
                    Generate
                </button>
            </div>
            @error('selectedCollectionId')
                <small class="text-red-700">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <div>
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">My Collections</h2>
        @if ($myCollections->count() > 0)
            <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-5">
                @foreach ($myCollections as $collection)
                    <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
                        <div class="flex justify-between gap-3">
                            <div>
                                <h3 class="font-semibold text-gray-800">{{ $collection->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $collection->description }}</p>
                            </div>
                            <span class="text-xs text-gray-500">{{ $collection->stars->count() }} stars</span>
                        </div>
                        <div class="mt-4 grid grid-cols-4 gap-2">
                            @forelse ($collection->products as $product)
                                <a href="{{ route('product.detail', ['id' => $product->id]) }}">
                                    <img src="{{ $product->firstImage ? asset('storage/' . $product->firstImage->url) : asset('storage/default/product.webp') }}"
                                        class="w-full aspect-square object-cover rounded-md" alt="{{ $product->name }}">
                                </a>
                            @empty
                                <p class="col-span-4 text-sm text-gray-500">No products saved yet.</p>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">Create your first collection to save products.</p>
        @endif
    </div>

    <div>
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Public Collections</h2>
        @if ($publicCollections->count() > 0)
            <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-5">
                @foreach ($publicCollections as $collection)
                    <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
                        <div class="flex justify-between gap-3">
                            <div>
                                <h3 class="font-semibold text-gray-800">{{ $collection->name }}</h3>
                                <p class="text-sm text-gray-500">By {{ $collection->user->name }}</p>
                            </div>
                            <button wire:click="toggleStar({{ $collection->id }})"
                                class="text-amber-500 hover:text-amber-600 cursor-pointer">
                                <i class="fa-{{ $collection->stars->where('user_id', Auth::guard('web')->id())->count() ? 'solid' : 'regular' }} fa-star"></i>
                                {{ $collection->stars_count }}
                            </button>
                        </div>
                        <div class="mt-4 grid grid-cols-4 gap-2">
                            @forelse ($collection->products as $product)
                                <a href="{{ route('product.detail', ['id' => $product->id]) }}">
                                    <img src="{{ $product->firstImage ? asset('storage/' . $product->firstImage->url) : asset('storage/default/product.webp') }}"
                                        class="w-full aspect-square object-cover rounded-md" alt="{{ $product->name }}">
                                </a>
                            @empty
                                <p class="col-span-4 text-sm text-gray-500">No products yet.</p>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">No public collections from other users yet.</p>
        @endif
    </div>
</section>
