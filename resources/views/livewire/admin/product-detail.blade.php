<div class="p-6">

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Product Details</h2>
        <a href="{{ route('admin.product') }}" 
           class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
            <i class="fa fa-arrow-left"></i> Back
        </a>
    </div>

    <!-- Product Detail Card -->
    <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-3">

            <!-- Product Image Gallery -->
            <div class="p-6 bg-gray-50 flex flex-col items-center">
                
                <!-- Main Image -->
                <div class="mb-4 w-72 h-72 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <img src="{{ asset('storage/'. $product->firstImage->url) }}" 
                         alt="Product Image" 
                         class="w-full h-full object-cover" id="mainImage">
                </div>

                <!-- Thumbnail Images -->
                <div class="flex gap-3">
                    @foreach ($product->images as $image)
                        <img src="{{ asset('storage/'. $image->url) }}" 
                         class="w-20 h-20 object-cover rounded-lg border-2 border-transparent hover:border-blue-500 cursor-pointer transition"
                         onclick="document.getElementById('mainImage').src=this.src">
                    @endforeach
                    
                </div>
            </div>

            <!-- Product Info -->
            <div class="col-span-2 p-6 space-y-3">
                <h3 class="text-2xl font-semibold text-gray-900">{{ $product->name }}</h3>
                <p class="text-gray-500 text-sm">Product Code: 
                    <span class="font-semibold text-gray-700">PROD12345</span>
                </p>

                <p class="text-gray-700 mt-3 leading-relaxed">{{ $product->summary }}</p>
                <p class="text-gray-700 mt-3 leading-relaxed">{{ $product->description }}</p>

                <div class="grid grid-cols-2 gap-6 mt-6 text-sm">
                    <div class="space-y-2">
                        <p><span class="font-semibold text-gray-800">Category:</span> {{ $product->category->name }}</p>
                        <p><span class="font-semibold text-gray-800">Stock:</span> {{ $product->stock }}</p>
                        <p><span class="font-semibold text-gray-800">Status:</span> <span class="text-green-600 font-medium">Available</span></p>
                    </div>
                    <div class="space-y-2">
                        <p><span class="font-semibold text-gray-800">Price:</span> Rs. {{ number_format($product->price) }}</p>
                        <p><span class="font-semibold text-gray-800">Discount: </span>{{ $product->discount > 0 ? $product->discount. '%' : 'No Discount' }}</p>
                        <p><span class="font-semibold text-gray-800">Added On:</span> {{ $product->created_at->format('j M Y') }}</p>
                    </div>
                </div>

                <div class="flex gap-3 mt-8">
                    <button class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition">
                        <i class="fa fa-edit"></i> Edit Product
                    </button>
                    <button class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>