 <!-- Static Review Section -->
 <div class="mt-10 bg-gray-100 p-6 rounded-xl shadow-lg">
     <h3 class="text-xl font-semibold mb-6 text-gray-800 border-4 border-gray-100 border-b-gray-400  pb-5">Customer
         Reviews <span class="text-gray-700 text-sm">({{ $reviews ? count($reviews) : 0 }} Reviews)</span></h3>

     @if (count($reviews) > 0)
         @foreach ($reviews as $review)
             <div class="pb-5 mb-5 border-b border-gray-200">
                 <div class="flex items-start gap-3">
                     @if ($review->user->photo)
                         <img src="{{ asset('storage/' . $review->user->photo) }}"
                             class="w-10 h-10 rounded-full object-cover border" alt="user">
                     @else
                         <img src="{{ asset('storage/default/vendor.jpg') }}"
                             class="w-10 h-10 rounded-full object-cover border" alt="user">
                     @endif
                     <div class="flex-1">
                         <div class="flex items-center justify-between">
                             <h4 class="font-semibold text-gray-800">{{ $review->user->name }}</h4>
                             <div class="flex text-yellow-400 text-sm">
                                 @for ($i = 1; $i <= 5; $i++)
                                     <i
                                         class="fa-solid fa-star {{ ($review->rating ?? 0) >= $i ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                 @endfor
                             </div>

                         </div>
                         <p class="text-gray-600 text-sm mt-2">
                             {{ $review->message }}
                         </p>

                         <!-- Review Images -->
                         <div class="flex gap-2 mt-3">
                             @foreach ($review->ratingImages as $image)
                                 <img src="{{ asset('storage/' . $image->images) }}"
                                     class="w-20 h-20 rounded-lg object-cover border">
                             @endforeach
                         </div>
                     </div>
                 </div>
             </div>
         @endforeach
     @else
         <div class="text-center font-semibold text-2xl text-gray-500">
             No Customer Review
         </div>
     @endif
 </div>
