<section x-data="cart">
    @if ($carts->count() > 0)
        <div class="bg-gray-100 min-h-screen py-10">
            <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-md p-6">
                <h2 class="text-2xl font-semibold mb-6">🛒 Shopping Cart</h2>

                <!-- Responsive Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-gray-700">
                                <th></th>
                                <th class="px-4 py-3 text-left">Product</th>
                                <th class="px-4 py-3 text-center">Price</th>
                                <th class="px-4 py-3 text-center">Quantity</th>
                                <th class="px-4 py-3 text-center">Discount</th>
                                <th class="px-4 py-3 text-center">Subtotal</th>
                                <th class="px-4 py-3 text-center">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @foreach ($carts as $cart)
                                @foreach ($cart->cartItems as $item)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td><input class="cusrsor-pointer" type="checkbox"></td>
                                        <!-- Product Info -->
                                        <td class="px-4 py-4 flex items-center space-x-3 min-w-[200px]">
                                            <img src="{{ asset('storage/' . ($item->product->images->first()->url ?? 'default/product.webp')) }}"
                                                alt="{{ $item->product->name }}"
                                                class="w-16 h-16 rounded-lg object-cover flex-shrink-0">
                                            <div class="truncate">
                                                <h3 class="font-medium text-gray-800 truncate">
                                                    {{ $item->product->name }}</h3>
                                                <p class="text-gray-500 text-xs truncate">{{ $item->product->summary }}
                                                </p>
                                                <p class="text-xs text-gray-400">Stock Left: {{ $item->product->stock }}</p>
                                                @if(!empty($item->selected_variants['variants']))
                                                    <div class="flex flex-wrap gap-1 mt-1">
                                                        @foreach($item->selected_variants['variants'] as $variant)
                                                            <span class="inline-block bg-indigo-50 text-indigo-700 text-[10px] px-1.5 py-0.5 rounded border border-indigo-100 font-semibold">{{ $variant['attribute_value'] ?: $variant['attribute_name'] }} × {{ $variant['quantity'] }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                                @if($item->product->variants->where('stock', '>', 0)->isNotEmpty())
                                                    <button type="button" wire:click="openVariantModal({{ $item->id }})" class="mt-2 text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold px-2 py-1 rounded border border-indigo-200">{{ !empty($item->selected_variants['variants']) ? 'Change variants' : 'Choose variants' }}</button>
                                                @endif
                                                @if($this->vendorCouponsFor($item->product)->isNotEmpty())
                                                    <button type="button" wire:click="openCouponModal({{ $item->id }})" class="mt-2 ml-1 text-xs bg-amber-50 hover:bg-amber-100 text-amber-700 font-semibold px-2 py-1 rounded border border-amber-200">{{ isset($vendorCouponSelections[$item->id]) ? 'Change coupon' : 'View coupons' }}</button>
                                                @endif
                                                @if(!empty($couponMessages[$item->id]))<p class="text-xs text-red-600 mt-1">{{ $couponMessages[$item->id] }}</p>@endif
                                            </div>
                                        </td>

                                        <!-- Price -->
                                        <td class="px-4 py-4 text-center whitespace-nowrap">
                                            @if ($item->product->discount)
                                                <div class="flex flex-col items-center">
                                                    <span class="text-gray-400 line-through text-sm">
                                                        Rs. {{ $item->product->price }}
                                                    </span>
                                                    <span class="text-red-500 font-semibold">
                                                        Rs.
                                                        {{ number_format($item->price, 2) }}
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-gray-700 font-semibold">
                                                    Rs. {{ number_format($item->price, 2) }}
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Quantity -->
                                        <td class="px-4 py-4 text-center">
                                            <div class="flex justify-center items-center space-x-2">
                                                <button
                                                    class="bg-gray-200 px-2 py-1 rounded hover:bg-gray-300 cursor-pointer"
                                                    wire:click="decrementQuantity({{ $item->id }})">-</button>
                                                <input type="number" wire:model.lazy="cartItems.{{ $item->id }}"
                                                    min="1"
                                                    class="w-12 text-center border rounded-md focus:ring-2 focus:ring-indigo-400 outline-none">

                                                <button
                                                    class="bg-gray-200 px-2 py-1 rounded hover:bg-gray-300 cursor-pointer"
                                                    wire:click="incrementQuantity({{ $item->id }})">+</button>
                                            </div>
                                            @error('cartItems.' . $item->id)
                                                <p class="text-red-500 text-xs mt-1" x-data="{ show: true }" x-show="show"
                                                    x-init="setTimeout(() => show = false, 2000)">{{ $message }}</p>
                                            @enderror
                                        </td>

                                        <!-- Coupon discount -->
                                        <td class="px-4 py-4 text-center whitespace-nowrap">
                                            @if(($vendorCouponDiscounts[$item->id] ?? 0) > 0)
                                                <p class="font-semibold text-green-600">- Rs. {{ number_format($vendorCouponDiscounts[$item->id], 2) }}</p>
                                                <button wire:click="removeVendorCoupon({{ $item->id }})" class="mt-1 text-xs text-red-600 hover:underline">Remove coupon</button>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>

                                        <!-- Final subtotal -->
                                        <td class="px-4 py-4 text-center font-semibold text-gray-800 whitespace-nowrap">
                                            Rs. {{ number_format(max(0, $item->sub_total - ($vendorCouponDiscounts[$item->id] ?? 0)), 2) }}
                                        </td>

                                        <!-- Remove -->
                                        <td class="px-4 py-4 text-center ">
                                            <button class="text-red-500 text-sm hover:underline cursor-pointer"
                                                @click="show"
                                                wire:click='removePopup({{ $item->id }})'>Remove</button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($variantCartItem)
                    <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
                        <div class="w-full max-w-lg bg-white rounded-xl shadow-xl p-6">
                            <div class="flex items-start justify-between gap-4 mb-4"><div><h3 class="text-lg font-bold">Choose variants and quantities</h3><p class="text-sm text-gray-500">Only available variants are shown. You can order more than one variant.</p></div><button type="button" wire:click="closeVariantModal" class="text-xl text-gray-500">&times;</button></div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($variantCartItem->product->variants->where('stock', '>', 0) as $variant)
                                    <div class="text-left border rounded-lg p-3 border-gray-200">
                                        <span class="block font-semibold">{{ $variant->attribute_value ?: $variant->attribute_name }}</span>
                                        @if($variant->attribute_value)<span class="block text-xs text-gray-500">{{ $variant->attribute_name }}</span>@endif
                                        <span class="block mt-1 text-sm {{ $variant->price_extra >= 0 ? 'text-indigo-700' : 'text-green-700' }}">{{ $variant->price_extra >= 0 ? '+' : '' }}Rs. {{ number_format($variant->price_extra, 2) }}</span>
                                        <span class="block text-xs text-gray-500">{{ $variant->stock }} available</span>
                                        <label class="block text-xs font-medium mt-2">Quantity <input type="number" min="0" max="{{ $variant->stock }}" wire:model.live="variantQuantities.{{ $variant->id }}" class="ml-2 w-16 border rounded p-1 text-center"></label>
                                        @error('variantQuantities.'.$variant->id)<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-5 flex justify-end gap-3"><button type="button" wire:click="closeVariantModal" class="px-4 py-2 border rounded">Cancel</button><button type="button" wire:click="applyVariantQuantities" class="px-4 py-2 bg-indigo-600 text-white rounded">Update cart</button></div>
                        </div>
                    </div>
                @endif

                @if($couponCartItem)
                    <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
                        <div class="w-full max-w-lg bg-white rounded-xl shadow-xl p-6">
                            <div class="flex items-start justify-between gap-4 mb-4"><div><h3 class="text-lg font-bold">Available coupons</h3><p class="text-sm text-gray-500">Choose one vendor coupon for this product.</p></div><button type="button" wire:click="closeCouponModal" class="text-xl text-gray-500">&times;</button></div>
                            <div class="space-y-3">
                                @foreach($this->vendorCouponsFor($couponCartItem->product) as $coupon)
                                    @php($collected = \App\Models\CouponUser::where('coupon_id', $coupon->id)->where('user_id', $userId)->exists())
                                    <div class="border rounded-lg p-3 flex items-center justify-between gap-3"><div><p class="font-semibold">{{ $coupon->code }}</p><p class="text-xs text-gray-500">{{ $coupon->type === 'percent' ? $coupon->value.'%' : 'Rs. '.$coupon->value }} off{{ $coupon->max_discount_amount ? ', max Rs. '.$coupon->max_discount_amount : '' }}</p></div><div class="flex gap-2">@if(!$collected)<button wire:click="collectVendorCoupon({{ $coupon->id }})" class="text-xs px-3 py-1 bg-gray-100 rounded">Collect</button>@endif<button wire:click="applyVendorCoupon({{ $coupon->id }})" @disabled(!$collected) class="text-xs px-3 py-1 bg-indigo-600 text-white rounded disabled:opacity-40">Apply</button></div></div>
                                @endforeach
                            </div>
                            @error('couponModal')<p class="text-sm text-red-600 mt-3">{{ $message }}</p>@enderror
                        </div>
                    </div>
                @endif

                <!-- Cart Summary & Coupon -->
                <div class="mt-8 border-t pt-6">
                    @include('common.message')
                    <div class="flex flex-col md:flex-row justify-between items-start gap-6">
                        
                        <!-- Coupon Code Section -->
                        <div class="w-full md:w-80 bg-gray-50 p-4 rounded-xl border border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-1">
                                <i class="fa-solid fa-ticket text-indigo-600"></i> Have a Promo / Coupon Code?
                            </h3>
                            @if ($appliedCoupon)
                                <div class="flex items-center justify-between bg-green-50 text-green-800 p-2.5 rounded-lg border border-green-200 text-sm">
                                    <div>
                                        <span class="font-bold">{{ $appliedCoupon->code }}</span>
                                        <span class="text-xs text-green-600 block">
                                            ({{ $appliedCoupon->type === 'percent' ? $appliedCoupon->value . '% off' : 'Rs. ' . $appliedCoupon->value . ' off' }})
                                        </span>
                                    </div>
                                    <button wire:click="removeCoupon" class="text-red-500 hover:text-red-700 font-semibold text-xs cursor-pointer">
                                        Remove
                                    </button>
                                </div>
                            @else
                                <div class="flex gap-2">
                                    <select wire:model="selectedCouponId"
                                        class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm w-full focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                                        <option value="">Select collected coupon</option>
                                        @foreach ($availableCoupons as $coupon)
                                            <option value="{{ $coupon->id }}">
                                                {{ $coupon->code }} -
                                                {{ $coupon->type === 'percent' ? rtrim(rtrim($coupon->value, '0'), '.') . '% off' : 'Rs. ' . number_format($coupon->value) . ' off' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button wire:click="applyCoupon"
                                        class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-4 py-1.5 rounded-lg font-medium transition cursor-pointer">
                                        Apply
                                    </button>
                                </div>
                                @error('selectedCouponId')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <a href="{{ route('user.coupons') }}" wire:navigate
                                    class="inline-block mt-2 text-xs font-medium text-indigo-600 hover:underline">
                                    Collect coupons first
                                </a>
                            @endif
                        </div>

                        <!-- Price Breakdown -->
                        <div class="text-gray-700 space-y-2">
                            <div class="flex justify-between w-60 md:w-80">
                                <span>Subtotal</span>
                                <span>Rs. {{ number_format($subTotal) }}</span>
                            </div>

                            @if ($discountAmount > 0)
                                <div class="flex justify-between w-60 md:w-80 text-green-600 font-medium">
                                    <span>Discount (Coupon)</span>
                                    <span>- Rs. {{ number_format($discountAmount) }}</span>
                                </div>
                            @endif

                            @if (collect($vendorCouponDiscounts)->sum() > 0)
                                <div class="flex justify-between w-60 md:w-80 text-green-600 font-medium"><span>Vendor coupons</span><span>- Rs. {{ number_format(collect($vendorCouponDiscounts)->sum()) }}</span></div>
                            @endif

                            <div class="flex justify-between w-60 md:w-80">
                                <span>Tax & Delivery</span>
                                <span class="text-green-600 font-medium">Free</span>
                            </div>

                            <div class="flex justify-between w-60 md:w-80 font-bold text-lg border-t pt-2 text-gray-900">
                                <span>Total Amount</span>
                                <span class="text-indigo-600">Rs. {{ number_format(max(0, $subTotal - $discountAmount - collect($vendorCouponDiscounts)->sum())) }}</span>
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row gap-3 mt-4 md:mt-0">
                            <a href="{{ route('user.product') }}"
                                class="text-gray-600 hover:underline text-sm md:text-base flex items-center gap-1">
                                ← Continue Shopping
                            </a>
                            <a href="{{ route('user.checkout') }}" class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg hover:bg-indigo-700 transition cursor-pointer font-medium shadow">Proceed to Checkout</a>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="popup" x-transition.opacity x-cloak
                class="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm z-50">
                <!-- Modal Box -->
                <div x-transition.scale class="bg-white w-full max-w-sm rounded-lg shadow-lg p-5 relative">
                    <!-- Close Button -->
                    <button @click.prevent="popup = false"
                        class="absolute top-2 right-3 text-gray-400 hover:text-gray-600 text-xl leading-none cursor-pointer">
                        &times;
                    </button>

                    <h2 class="text-base text-red-700 border-b pb-2 mb-3 my-3">
                        @if ($cartItem)
                            Are you sure you want to remove <span class="font-semibold">
                                {{ $cartItem->product->name }}</span>
                            from cart?
                        @endif
                    </h2>
                    <div class="flex gap-2 justify-end mt-4">
                        <button @click.prevent="popup = false"
                            class="bg-green-800 hover:bg-green-900 py-1 px-3 rounded-md cursor-pointer text-white block">Cancel</button>
                        <button class="bg-red-800 hover:bg-red-900 py-1 px-3 rounded-md cursor-pointer text-white block"
                            wire:click.prevent='deleteItem'>Delete</button>
                    </div>
                </div>
            </div>

            <!-- Modal Overlay -->
            <div x-show="checkout" x-transition.opacity x-cloak
                class="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm z-50">

                <!-- Modal Box -->
                <div x-transition.scale class="bg-white w-full max-w-[95%] lg:max-w-[50%] rounded-lg shadow-lg p-6 relative">
                    <!-- Close Button -->
                    <button @click.prevent="checkout = false" wire:click="resetCheckout"
                        class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-lg cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>

                    <!-- Title -->
                    <h2 class="text-2xl font-semibold mb-2 text-center">Checkout</h2>

                    <form wire:submit.prevent='checkoutSubmit'>
                        <div class="flex items-center justify-center gap-2 text-xs font-medium text-gray-500 mb-5">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center {{ $checkoutStep === 1 ? 'bg-indigo-600 text-white' : 'bg-green-100 text-green-700' }}">1</span>
                            <span>Details</span>
                            <span class="w-8 h-px bg-gray-300"></span>
                            <span class="w-6 h-6 rounded-full flex items-center justify-center {{ $checkoutStep === 2 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500' }}">2</span>
                            <span>Review</span>
                        </div>

                        @if ($checkoutStep === 1)
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-4 ">
                                <!-- Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Full Name</label>
                                    <input type="text" placeholder="John Doe" wire:model='userName'
                                        class="w-full border border-gray-300 rounded-md p-2 focus:ring focus:ring-blue-300">
                                </div>

                                <!-- Email -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email</label>
                                    <input type="email" placeholder="john@example.com" wire:model='userEmail'
                                        class="w-full border border-gray-300 rounded-md p-2 focus:ring focus:ring-blue-300">
                                </div>

                                {{-- phone --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Shipping Phone</label>
                                    <input type="text" placeholder="98 xxxxxxxx" wire:model='userPhone'
                                        class="w-full border border-gray-300 rounded-md p-2 focus:ring focus:ring-blue-300">
                                </div>

                                <!-- Payment Method -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                                    <select
                                        class="w-full border border-gray-300 rounded-md p-2 focus:ring focus:ring-blue-300"
                                        wire:model.live='paymentMethod'>
                                        <option value="">--- Select Payment Option ---</option>
                                        <option value="E-Sewa">E-Sewa</option>
                                        <option value="Cash">Cash on Delivery</option>
                                    </select>
                                    @error('paymentMethod')
                                    <small class="text-red-800">{{ $message }}</small>
                                    @enderror
                                </div>

                            </div>


                            <div class="space-y-4">
                                <!-- Province -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Shipping Province</label>
                                    <input type="text" placeholder="123 Main Street" wire:model='userProvince'
                                        class="w-full border border-gray-300 rounded-md p-2 focus:ring focus:ring-blue-300">
                                </div>

                                {{-- City --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Shipping city</label>
                                    <input type="text" placeholder="Baneshwor" wire:model='userCity'
                                        class="w-full border border-gray-300 rounded-md p-2 focus:ring focus:ring-blue-300">
                                </div>

                                {{-- tole --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Shipping tole</label>
                                    <input type="text" placeholder="Magar tole" wire:model='userTole'
                                        class="w-full border border-gray-300 rounded-md p-2 focus:ring focus:ring-blue-300">
                                </div>
                            </div>
                        </div>

                        <button type="button" wire:click="proceedToReview"
                            class="w-full mt-5 cursor-pointer bg-blue-600 text-white p-2 rounded-md hover:bg-blue-700 transition">
                            Continue to Review
                        </button>
                        @endif

                        @if ($checkoutStep === 2)
                        <div class="space-y-4">
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                <h3 class="font-semibold text-gray-800 mb-3">Review your order</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-gray-600">
                                    <p><span class="font-medium text-gray-800">Customer:</span> {{ $userName }}</p>
                                    <p><span class="font-medium text-gray-800">Email:</span> {{ $userEmail }}</p>
                                    <p><span class="font-medium text-gray-800">Delivery:</span> {{ $userCity }}, {{ $userProvince }}</p>
                                    <p><span class="font-medium text-gray-800">Payment:</span> {{ $paymentMethod ?: 'Not selected' }}</p>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <button type="button" wire:click="returnToCheckoutDetails"
                                    class="flex-1 cursor-pointer bg-gray-200 text-gray-800 p-2 rounded-md hover:bg-gray-300 transition">
                                    Back
                                </button>
                                <button type="submit"
                                    class="flex-1 cursor-pointer bg-blue-600 text-white p-2 rounded-md hover:bg-blue-700 transition">
                                    Place Order
                                </button>
                            </div>
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    @else
        <div class="w-[90%] my-65 mx-auto text-center">
            {{-- <img class="block" src="{{ asset('storage/default/emptyCart.png') }}" alt=""> --}}
            <h1 class="text-3xl  text-gray-400">No Product is added to cart </h1>
            <a class="mt-6 inline-block bg-gray-800 text-white  py-2 px-5 rounded-lg hover:scale-105 duration-200" href="{{ route('user.product') }}">Shop Now</a>
        </div>
    @endif


</section>
