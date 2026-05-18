<section>
    <table class="w-full text-sm text-left border border-gray-200 rounded-lg overflow-hidden">
        <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
            <tr>
                <th class="px-4 py-2 border-b">#</th>
                <th class="px-4 py-2 border-b">Name</th>
                <th class="px-4 py-2 border-b">Stock</th>
                <th class="px-4 py-2 border-b">Discount</th>
                <th class="px-4 py-2 border-b">Category</th>
                <th class="px-4 py-2 border-b">Summary</th>
                <th class="px-4 py-2 border-b text-center">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach ($products as $key => $product)
                <tr class="hover:bg-gray-50">
                    {{-- {{ $products }} --}}
                    <td class="px-4 py-2">{{ $key + 1 }}</td>
                    <td class="px-4 py-2">{{ $product->name }}</td>
                    <td class="px-4 py-2">{{ $product->stock }}</td>
                    @if ($product->discount || $product->discount > 0)
                        <td class="px-4 py-2">{{ $product->discount }}%</td>
                    @else
                        <td class="px-4 py-2">0%</td>
                    @endif
                    <td class="px-4 py-2">{{ $product->category->name }}</td>
                    <td class="px-4 py-2">{{ $product->summary }}</td>
                    <td class="px-4 py-2 text-center space-x-3">
                        <button class="text-blue-500 hover:underline text-sm cursor-pointer"
                            wire:click="productDetail({{ $product->id }})"
                            @click.prevent="showUpdateView">Edit</button>
                        <button class="text-red-500 hover:underline text-sm cursor-pointer"
                            @click.prevent="popup = true"
                            wire:click.prevent='popupFunc({{ $product->id }})'>Delete</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Popup Modal -->
    <div x-show="popup" x-transition.opacity x-cloak
        class="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm z-50">
        <!-- Modal Box -->
        <div x-transition.scale class="bg-white w-full max-w-sm rounded-lg shadow-lg p-5 relative">
            <!-- Close Button -->
            <button @click.prevent="popup = false"
                class="absolute top-2 right-3 text-gray-400 hover:text-gray-600 text-xl leading-none cursor-pointer">
                &times;
            </button>

            <h2 class="text-base font-semibold text-red-700 border-b pb-2 mb-3">
                Are you Sure you want to delete this product?
            </h2>
            <p class="text-red-700">Can Process can't be undone</p>
            <div class="flex gap-2 justify-end mt-4">
                <button @click.prevent="popup = false" class="bg-green-800 hover:bg-green-900 py-1 px-3 rounded-md cursor-pointer text-white block">Cancel</button>
                <button class="bg-red-800 hover:bg-red-900 py-1 px-3 rounded-md cursor-pointer text-white block" wire:click.prevent='deleteProduct'>Delete</button>
            </div>
        </div>
    </div>
</section>
