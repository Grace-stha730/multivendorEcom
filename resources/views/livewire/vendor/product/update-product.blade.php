<section class="max-w-4xl mx-auto my-6 bg-white rounded-lg shadow p-6">
    <h1 class="text-2xl font-semibold text-gray-700 mb-6 border-b pb-3">Update Product</h1>

    <form wire:submit.prevent="updateProduct" class="space-y-6"> <!-- Product Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
            <input type="text" id="name" wire:model="name"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                placeholder="Enter product name">
            @error('name')
                <small class="text-red-500">{{ $message }}</small>
            @enderror
        </div>
        <!-- Description -->
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea id="description" wire:model="description" rows="4"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                placeholder="Enter product description"></textarea>
            @error('description')
                <small class="text-red-500">{{ $message }}</small>
            @enderror
        </div>
        {{-- Summary --}}
        <div> <label for="summary" class="block text-sm font-medium text-gray-700 mb-1">Summary</label> <input
                type="text" id="summary"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                wire:model="summary" placeholder="Enter product summary">
            @error('summary')
                <small class="text-red-500">{{ $message }}</small>
            @enderror
        </div>
        <!-- Price and Quantity (side by side) -->
        <div class="flex gap-4">
            <div class="flex-1"> <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                <input type="number" id="price" wire:model="price" step="0.01"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                    placeholder="Enter price">
                @error('price')
                    <small class="text-red-500">{{ $message }}</small>
                @enderror
            </div>
            <div class="flex-1"> <label for="discount"
                    class="block text-sm font-medium text-gray-700 mb-1">Discount</label> <input type="number"
                    id="discount" wire:model="discount"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                    placeholder="Enter Discount">
                @error('discount')
                    <small class="text-red-500">{{ $message }}</small>
                @enderror
            </div>
            <div class="flex-1"> <label for="quantity"
                    class="block text-sm font-medium text-gray-700 mb-1">Quantity</label> <input type="number"
                    id="quantity" wire:model="stock"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                    placeholder="Enter quantity">
                @error('stock')
                    <small class="text-red-500">{{ $message }}</small>
                @enderror
            </div>
        </div>
        <!-- Category Select -->
        <div> <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label> <select
                id="category" wire:model="category_id"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                <option value="">Select a category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id')
                <small class="text-red-500">{{ $message }}</small>
            @enderror
        </div>
        <!-- Image Upload -->
        <div> <label class="block text-sm font-medium text-gray-700 mb-1">Product Images</label> <input type="file"
                wire:model="images" multiple
                class="w-full py-2 px-3 text-sm text-gray-700 border border-gray-300 rounded-lg cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-400">
            @error('images.*')
                <small class="text-red-500">{{ $message }}</small>
            @enderror <!-- Preview -->
            @if ($images)
                <div class="mt-3 flex flex-wrap gap-3">
                    @foreach ($images as $index => $image)
                        <div class="relative"> <img src="{{ $image->temporaryUrl() }}"
                                class="h-24 w-24 object-cover rounded-lg"> <button type="button"
                                wire:click="removeImage({{ $index }})"
                                class="cursor-pointer absolute top-0 right-0 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600">
                                &times; </button> </div>
                    @endforeach
                </div>
            @else
                <div class="mt-3 flex flex-wrap gap-3">
                    @foreach ($realImg as $index => $image)
                        <div class="relative">
                            <img src="{{ asset('storage/' . $image['url']) }}" alt="Product Image"
                                class="h-24 w-24 object-cover rounded-lg border border-gray-300">

                            <button type="button" wire:click="removeImage({{ $index }})"
                                class="absolute top-0 right-0 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600">
                                &times;
                            </button>
                        </div>
                    @endforeach
                </div>

            @endif
        </div>
        <!-- Product Variants -->
        <div class="bg-gray-50 p-5 rounded-xl border border-gray-200">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-sm font-bold text-gray-700 flex items-center gap-1">
                    <i class="fa-solid fa-sliders text-blue-500"></i> Product Variants (e.g. Size, Color)
                </h3>
                <button type="button" wire:click="addVariant"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1.5 rounded-lg font-medium transition cursor-pointer">
                    + Add Variant
                </button>
            </div>

            @if (count($variants) > 0)
                <div class="space-y-3">
                    @foreach ($variants as $index => $variant)
                        <div class="flex flex-col md:flex-row gap-3 items-end bg-white p-3 rounded-lg border border-gray-100 shadow-sm relative">
                            <div class="flex-1">
                                <label class="block text-[11px] font-semibold text-gray-500 mb-1">Attribute (e.g., Size, Color)</label>
                                <input type="text" wire:model="variants.{{ $index }}.attribute_name" placeholder="e.g. Size"
                                    class="w-full border border-gray-300 rounded-lg px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div class="flex-1">
                                <label class="block text-[11px] font-semibold text-gray-500 mb-1">Value (e.g., XL, Blue)</label>
                                <input type="text" wire:model="variants.{{ $index }}.attribute_value" placeholder="e.g. XL"
                                    class="w-full border border-gray-300 rounded-lg px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div class="w-32">
                                <label class="block text-[11px] font-semibold text-gray-500 mb-1">Price Extra (Rs.)</label>
                                <input type="number" step="0.01" wire:model="variants.{{ $index }}.price_extra" placeholder="0"
                                    class="w-full border border-gray-300 rounded-lg px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div class="w-32">
                                <label class="block text-[11px] font-semibold text-gray-500 mb-1">Stock</label>
                                <input type="number" wire:model="variants.{{ $index }}.stock" placeholder="0"
                                    class="w-full border border-gray-300 rounded-lg px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <button type="button" wire:click="removeVariant({{ $index }})"
                                class="bg-red-50 hover:bg-red-100 text-red-500 border border-red-200 text-xs px-2.5 py-1.5 rounded-lg font-medium transition cursor-pointer">
                                Remove
                            </button>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-gray-500 text-center py-4 bg-white rounded-lg border border-dashed">
                    No variants added. Click "+ Add Variant" to support multiple options for this product.
                </p>
            @endif
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end"> <button
                class="px-6 py-2 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-600 transition duration-200 cursor-pointer">
                Update Product </button> </div>
    </form>
</section>