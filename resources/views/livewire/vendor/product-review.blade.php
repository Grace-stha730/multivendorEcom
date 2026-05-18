<section>
    @php
        // Group reviews by product
        $reviewsByProduct = $productRatings->groupBy('product_id');
    @endphp

    @foreach ($reviewsByProduct as $productId => $reviews)
        <div class="bg-white p-6 rounded-2xl shadow-md mb-6">
            {{-- Product Info --}}
            <div class="flex items-center gap-4 mb-5">
                <img src="{{ asset('storage/' . $reviews->first()->product->firstImage->url) }}"
                    class="w-20 h-20 object-cover rounded-xl border border-gray-200" alt="Product">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">{{ $reviews->first()->product->name }}</h2>

                    {{-- Average Rating --}}
                    @php
                        $avgRating = round($reviews->avg('rating'), 1);
                    @endphp
                    <div class="flex items-center mt-1">
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="fa-solid fa-star {{ $avgRating >= $i ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                        @endfor
                        <span class="ml-2 text-sm text-gray-600">{{ $avgRating }} ({{ $reviews->count() }}
                            reviews)</span>
                    </div>
                </div>
            </div>

            {{-- Individual Reviews --}}
            <div class="space-y-5 border-t border-gray-200 pt-4">
                @foreach ($reviews as $review)
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <div class="flex gap-3 items-center">
                                <img class="w-15 h-15 object-cover rounded-full"
                                    src="{{ $review->user->photo ? asset('storage/' . $review->user->photo) : asset('storage/default/vendor.jpg') }}" alt="">
                                <div>
                                    <h4 class="font-semibold text-gray-800">{{ $review->user->name }}</h4>
                                    <p class="text-gray-600 text-sm">{{ $review->message }}</p>

                                    <p class="text-xs text-gray-400 mt-1">{{ $review->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <div class="text-yellow-400 text-sm">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="fa-solid fa-star {{ $review->rating >= $i ? '' : 'text-gray-300' }}"></i>
                                @endfor
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

</section>
