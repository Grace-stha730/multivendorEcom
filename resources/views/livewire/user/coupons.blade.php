<section class="bg-gray-100 min-h-screen py-10">
    <div class="w-[90%] lg:w-[80%] mx-auto space-y-8">
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <p class="text-sm font-semibold text-indigo-600 uppercase">Coupon Center</p>
                    <h1 class="text-3xl font-semibold text-gray-900 mt-1">Get coupons and use them at checkout</h1>
                    <p class="text-gray-600 mt-2 max-w-2xl">
                        Collect an active coupon, add matching products to your cart, then select the coupon from the cart dropdown before placing your order.
                    </p>
                </div>

                @auth('web')
                    <div class="grid grid-cols-3 gap-3 text-center">
                        <div class="bg-indigo-50 border border-indigo-100 rounded-xl px-4 py-3">
                            <p class="text-xl font-bold text-indigo-700">{{ number_format($walletBalance) }}</p>
                            <p class="text-xs text-gray-600">Points</p>
                        </div>
                        <div class="bg-emerald-50 border border-emerald-100 rounded-xl px-4 py-3">
                            <p class="text-xl font-bold text-emerald-700">{{ number_format($reviewCount) }}</p>
                            <p class="text-xs text-gray-600">Reviews</p>
                        </div>
                        <div class="bg-rose-50 border border-rose-100 rounded-xl px-4 py-3">
                            <p class="text-xl font-bold text-rose-700">{{ number_format($wishlistCount) }}</p>
                            <p class="text-xs text-gray-600">Wishlist</p>
                        </div>
                    </div>
                @else
                    <a href="{{ route('user.login') }}" wire:navigate
                        class="inline-flex items-center justify-center bg-indigo-600 text-white px-5 py-2.5 rounded-lg hover:bg-indigo-700 transition">
                        Login to Track Rewards
                    </a>
                @endauth
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-md border border-gray-200 p-6">
                <div class="flex items-center justify-between gap-4 mb-5">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Available Coupons</h2>
                        <p class="text-sm text-gray-500">Collect a coupon here, then choose it in your cart.</p>
                    </div>
                    <a href="{{ route('user.cart') }}" wire:navigate class="text-sm text-indigo-600 hover:underline">Go to Cart</a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse ($coupons as $coupon)
                        <div class="border border-gray-200 rounded-xl p-4 bg-gray-50">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs text-gray-500">Marketplace Coupon</p>
                                    <h3 class="text-xl font-bold text-indigo-700 tracking-wide">{{ $coupon->code }}</h3>
                                </div>
                                <span class="text-xs font-semibold px-2 py-1 rounded-full bg-green-100 text-green-700">
                                    {{ $coupon->remainingUses() }} left
                                </span>
                            </div>

                            <div class="mt-4 space-y-2 text-sm text-gray-700">
                                @if ($coupon->description)
                                    <p class="text-gray-600">{{ $coupon->description }}</p>
                                @endif
                                <p>
                                    <span class="font-semibold">Discount:</span>
                                    {{ $coupon->type === 'percent' ? rtrim(rtrim($coupon->value, '0'), '.') . '% off' : 'Rs. ' . number_format($coupon->value) . ' off' }}
                                </p>
                                <p>
                                    <span class="font-semibold">Minimum order:</span>
                                    Rs. {{ number_format($coupon->min_order_amount) }}
                                </p>
                                <p>
                                    <span class="font-semibold">Minimum item price:</span>
                                    Rs. {{ number_format($coupon->min_item_price) }}
                                </p>
                                <p>
                                    <span class="font-semibold">Valid until:</span>
                                    {{ $coupon->expires_at ? $coupon->expires_at->format('j M Y') : 'No expiry' }}
                                </p>
                            </div>

                            <div class="mt-4">
                                @if (in_array($coupon->id, $collectedCouponIds))
                                    <span class="inline-flex items-center justify-center w-full bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">
                                        Collected
                                    </span>
                                @else
                                    <button wire:click="collectCoupon({{ $coupon->id }})"
                                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition cursor-pointer">
                                        Collect Coupon
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="md:col-span-2 border border-dashed border-gray-300 rounded-xl p-8 text-center text-gray-500">
                            No active coupons are available right now. Check reviews, referrals, and wishlist alerts for new rewards.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6 h-fit">
                <h2 class="text-xl font-semibold text-gray-900">How to Use a Coupon</h2>
                <ol class="mt-4 space-y-3 text-sm text-gray-700">
                    <li class="flex gap-3">
                        <span class="w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold shrink-0">1</span>
                        Collect a coupon from this page.
                    </li>
                    <li class="flex gap-3">
                        <span class="w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold shrink-0">2</span>
                        Add eligible products to your cart and meet the minimum order amount.
                    </li>
                    <li class="flex gap-3">
                        <span class="w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold shrink-0">3</span>
                        Select the coupon from the cart dropdown and click Apply before checkout.
                    </li>
                </ol>
            </div>
        </div>

        
    </div>
</section>
