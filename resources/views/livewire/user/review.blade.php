<div class="max-w-3xl mx-auto bg-white shadow-md rounded-2xl p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">
        Review Your Order #{{ $order->order_number }}
    </h2>

    @foreach ($order->orderItems as $item)
        <div class="border-b border-gray-200 pb-6 mb-6">
            <div class="flex items-center gap-4 mb-3">
                <img src="{{ asset('storage/' . $item->product->firstImage->url) }}"
                    class="w-16 h-16 object-cover rounded-lg border">
                <div>
                    <p class="font-semibold text-gray-800">{{ $item->product->name }}</p>
                    <p class="text-sm text-gray-500">Product ID: {{ $item->product->id }}</p>
                </div>
            </div>

            <!-- Rating -->
            <div class="flex items-center gap-2 mb-3">
                @for ($i = 1; $i <= 5; $i++)
                    <i class="fa-solid fa-star text-2xl cursor-pointer transition-colors duration-200 
                        {{ ($newRate[$item->product_id] ?? 0) >= $i ? 'text-yellow-400' : 'text-gray-300' }}"
                        wire:click="updateRate({{ $item->product_id }}, {{ $i }})">
                    </i>
                @endfor
            </div>

            <!-- Comment -->
            <textarea wire:model="message.{{ $item->product_id }}" rows="2" placeholder="Write your review..."
                class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400">
        </textarea>

            <div class="text-end mt-2">
                <label for="image_{{ $item->product_id }}"
                    class="inline-block bg-blue-600 py-1 px-1.5 rounded-lg text-white cursor-pointer hover:bg-blue-700">
                    Update Image
                </label>
                <input type="file" class="hidden" id="image_{{ $item->product_id }}"
                    wire:model="images.{{ $item->product_id }}" multiple>
            </div>

            @if (!empty($images[$item->product_id]))
                <div class="mt-3 flex flex-wrap gap-3">
                    @foreach ($images[$item->product_id] as $index => $image)
                        <div class="relative">
                            <img src="{{ $image->temporaryUrl() }}" class="h-24 w-24 object-cover rounded-lg">
                            <button type="button" wire:click="removeImage({{ $item->product_id }}, {{ $index }})"
                                class="cursor-pointer absolute top-0 right-0 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600">
                                &times;
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach

    <div class="flex justify-end">
        <button wire:click="submit"
            class="px-6 py-2 bg-yellow-500 cursor-pointer hover:bg-yellow-600 text-white rounded-lg font-semibold transition">
            Submit All Reviews
        </button>
    </div>

    @if (session()->has('message'))
        <div class="mt-4 text-green-600 font-medium text-center">
            {{ session('message') }}
        </div>
    @endif
</div>
