<section class=" min-h-screen py-10">
    <div lass="max-w-7xl mx-auto bg-white rounded-2xl shadow-md p-6">
        <h2 class="text-3xl font-semibold mb-6 flex items-center justify-between">
            All Products
        </h2>

        {{-- vendor table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">Image</th>
                        <th class="px-4 py-3 text-left">vendor name</th>
                        <th class="px-4 py-3 text-left">Created By</th>
                        <th class="px-4 py-3 text-left">product Name</th>
                        <th class="px-4 py-3 text-left">Price</th>
                        <th class="px-4 py-3 text-left">stock</th>
                        <th class="px-4 py-3 text-left">discount</th>
                        <th class="px-4 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $idx => $product)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 font-medium text-gray-700">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3 font-medium text-gray-700">
                                <img class="w-20 h-10 object-cover rounded-md"
                                    src="{{ asset('storage/' . $product->firstImage->url) }}" alt="">
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-700">{{ $product->vendor->shop_name }}</td>
                            <td class="px-4 py-3 font-medium text-gray-700">{{ $product->vendor->owner_name }}</td>
                            <td class="px-4 py-3 font-medium text-gray-700">{{ $product->name }}</td>
                            <td class="px-4 py-3 ">{{ $product->price }}</td>
                            <td class="px-4 py-3 ">{{ $product->stock > 1 ?  $product->stock : 'Out of stock' }}</td>
                            <td class="px-4 py-3 ">{{ $product->discount ? $product->discount : 'No Discount' }}</td>
                            <td class="px-4 py-3 ">
                                <a href="{{ route('admin.product-detail',['id' => $product->id] ) }}"
                                    class="bg-gray-800 text-white py-0.5 px-2 rounded-md cursor-pointer hover:bg-green-700"
                                    wire:click='viewDetail({{ $product->id }})'>View Detail</a
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</section>
