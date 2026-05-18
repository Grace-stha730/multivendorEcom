<section class="max-w-4xl mx-auto my-6 bg-white rounded-lg shadow p-6">
    <h1 class="text-2xl font-semibold text-gray-700 mb-6 border-b pb-3">Add New Product</h1>

    <form wire:submit.prevent="saveProduct" class="space-y-6"> <!-- Product Name -->
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
        <div>
            <label for="summary" class="block text-sm font-medium text-gray-700 mb-1">Summary</label>
            <input type="text" id="summary"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                wire:model="summary" placeholder="Enter product summary">
            @error('summary')
                <small class="text-red-500">{{ $message }}</small>
            @enderror
        </div>
        <!-- Price and Quantity (side by side) -->
        <div class="flex gap-4">
            <div class="flex-1">
                <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                <input type="number" id="price" wire:model="price" step="0.01"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                    placeholder="Enter price">
                @error('price')
                    <small class="text-red-500">{{ $message }}</small>
                @enderror
            </div>
            <div class="flex-1">
                <label for="discount" class="block text-sm font-medium text-gray-700 mb-1">Discount</label>
                <input type="number" id="discount" wire:model="discount"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                    placeholder="Enter Discount">
                @error('discount')
                    <small class="text-red-500">{{ $message }}</small>
                @enderror
            </div>
            <div class="flex-1"> <label for="quantity"
                    class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                <input type="number" id="quantity" wire:model="stock"
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
            @endif
        </div>
        <!-- Submit Button -->
        <div class="flex justify-end">
            <button
                class="px-6 py-2 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-600 transition duration-200 cursor-pointer">
                Save Product
            </button>
        </div>
    </form>
</section>
