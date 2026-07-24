<section class="w-[90%] md:w-[80%] mx-auto my-8">
    <div class="bg-white border border-indigo-100 rounded-2xl shadow-md p-5 md:p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-indigo-600 uppercase">Coupon Rewards</p>
            <h2 class="text-2xl font-semibold text-gray-900 mt-1">Collect coupons before checkout</h2>
            <p class="text-sm text-gray-600 mt-2 max-w-2xl">
                Save coupons from the coupon center, then select an eligible coupon directly from your cart dropdown.
            </p>
        </div>

        <a href="{{ route('user.coupons') }}" wire:navigate
            class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg font-medium transition shadow-sm">
            <i class="fa-solid fa-ticket"></i>
            Collect Coupons
        </a>
    </div>
</section>
