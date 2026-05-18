<section class="my-6 flex gap-6" x-data="{ open: false }">
    <!-- Left: Category Table -->
    <div class="w-2/3 bg-white rounded-lg shadow p-5">
        <h2 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">All Categories</h2>

        @if ($categories->isEmpty())
            <p class="text-gray-500 text-center py-10">No categories found.</p>
        @else
            <table class="w-full text-sm text-left border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-2 border-b">#</th>
                        <th class="px-4 py-2 border-b">Name</th>
                        <th class="px-4 py-2 border-b">Description</th>
                        <th class="px-4 py-2 border-b">Created By</th>
                        <th class="px-4 py-2 border-b text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($categories as $key => $category)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2">{{ $key + 1 }}</td>
                            <td class="px-4 py-2 font-medium text-gray-800">{{ $category->name }}</td>
                            <td class="px-4 py-2 text-gray-600">{{ Str::limit($category->description, 40) }}</td>
                            <td>
                                @if ($category->vendor_id)
                                    {{ $category->vendor->shop_name }}
                                @endif
                                @if ($category->admin_id)
                                    {{ $category->admin->name }}
                                @endif
                            </td>
                            <td class="px-4 py-2 text-center space-x-3">
                                <button @click.prevent="open = true" wire:click="edit({{ $category->id }})"
                                    class="text-blue-500 hover:underline text-sm cursor-pointer">
                                    Edit
                                </button>

                                <button wire:click="delete({{ $category->id }})"
                                    class="text-red-500 hover:underline text-sm cursor-pointer">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Right: Create Category Form -->
    <div class="w-1/3 bg-white rounded-lg shadow overflow-hidden">
        <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
            <h1 class="text-base font-semibold text-gray-700">Create New Category</h1>
        </div>

        <div class="p-5">
            <form wire:submit.prevent="store" class="space-y-5">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" id="name" wire:model="name"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                        placeholder="Enter category name">
                    @error('name')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="description" wire:model="description" rows="4"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                        placeholder="Enter short description"></textarea>
                    @error('description')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <button type="submit"
                        class="w-full cursor-pointer bg-blue-500 text-white text-sm font-semibold py-2 rounded-lg hover:bg-blue-600 transition duration-200">
                        Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Popup Modal -->
    <div x-show="open" x-transition.opacity x-cloak
        class="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm z-50">
        <!-- Modal Box -->
        <div x-transition.scale class="bg-white w-full max-w-sm rounded-lg shadow-lg p-5 relative">
            <!-- Close Button -->
            <button @click="open = false"
                class="absolute top-2 right-3 text-gray-400 hover:text-gray-600 text-xl leading-none cursor-pointer">
                &times;
            </button>

            <h2 class="text-base font-semibold text-gray-700 border-b pb-2 mb-3">
                Edit Category
            </h2>

            <form wire:submit.prevent="update" class="space-y-4">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" wire:model="new_name"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                    @error('name')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea wire:model="new_description" rows="3"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"></textarea>
                    @error('description')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-3 pt-3 border-t">
                    <button type="button" @click="open = false"
                        class="px-3 py-1.5 bg-gray-200 text-gray-700 text-sm rounded hover:bg-gray-300 cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-3 py-1.5 bg-blue-500 text-white text-sm rounded hover:bg-blue-600 cursor-pointer">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>  
</section>
