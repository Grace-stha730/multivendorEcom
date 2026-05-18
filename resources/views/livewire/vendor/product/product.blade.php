<section x-data="product">
    <nav class="py-4">
        <button @click.prevent="showCreateView"
            class="bg-gray-700 py-2 px-3 text-white rounded-sm cursor-pointer hover:bg-gray-900 duration-150 hover:duration-150" :class="createView ? 'bg-gray-900' : 'bg-gray-700'">
            Create Product
        </button>

        <button @click.prevent="showListView"
            class="bg-gray-700 py-2 px-3 text-white rounded-sm cursor-pointer hover:bg-gray-900 duration-150 hover:duration-150" :class="listView ? 'bg-gray-900' : 'bg-gray-700'">
            Product List
        </button>
    </nav>

    <div x-show="createView">
        @livewire('vendor.product.create-product')
    </div>

    <div x-show="listView">
        <h1 class="text-2xl font-semibold text-gray-700 mb-6 border-b pb-3">Product List</h1>
        @livewire('vendor.product.product-list')
    </div>

    <div x-show="updateView ">
        @livewire('vendor.product.update-product', ['productId' => $productId ?? null])
    </div>
</section>
