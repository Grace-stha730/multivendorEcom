   {{-- <!-- Vendor Details + Static Review --> --}}
   <div class="mt-10 bg-gray-50 p-6 rounded-xl shadow-sm">
       <h3 class="font-semibold text-lg mb-4">Store Information</h3>
       <div class="flex items-center gap-5">
           @if ($product->shop)
               <img class="w-24 h-24 object-cover rounded-full border"
                   src="{{ $product->shop->image ? asset('storage/' . $product->shop->image) : asset('default/vendor.svg') }}"
                   alt="{{ $product->shop->name }}">
               <div>
                   <a href="{{ route('user.shop', ['id' => $product->shop->id]) }}"
                       class="text-xl font-semibold text-gray-600 hover:text-gray-800"
                       title="View store">{{ $product->shop->name }}</a>
           @else
               <img class="w-24 h-24 object-cover rounded-full border" src="{{ asset('default/vendor.svg') }}"
                   alt="Store unavailable">
               <div>
                   <span class="text-xl font-semibold text-gray-600">Store unavailable</span>
           @endif

               <!-- Static Rating -->
               <div class="flex items-center gap-1 text-yellow-400 mb-1">
                   @for ($i = 1; $i <= 5; $i++)
                       <i
                           class="fa-solid fa-star {{ ($averageRate ?? 0) >= $i ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                   @endfor
                   <span class="text-gray-600 text-sm ml-2">({{ $averageRate }} out of 5)</span>
               </div>

               <p class="text-gray-500 text-sm italic">“Excellent service and good quality products. Highly
                   recommended!”</p>
               </div>
       </div>
   </div>
