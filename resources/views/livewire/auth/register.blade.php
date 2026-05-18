<section class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 py-10">
    <div class="w-full max-w-xl bg-white shadow-lg rounded-2xl p-8">
        <h1 class="text-xl font-bold text-gray-800 mb-6 text-center">🛒 Vendor Registration</h1>

        <form wire:submit.prevent="register" enctype="multipart/form-data" class="space-y-4">
            <!-- Shop Name -->
            <div>
                <label for="shop_name" class="block text-gray-700 font-medium mb-1 text-xs">Shop Name</label>
                <input type="text" id="shop_name" wire:model="shop_name"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 text-xs focus:ring-blue-400"
                    placeholder="Enter your shop name">
                <small class="text-red-500">
                    @error('shop_name')
                        {{ $message }}
                    @enderror
                </small>
            </div>

            <!-- Owner Name -->
            <div>
                <label for="owner_name" class="block text-gray-700 font-medium mb-1 text-xs">Owner Name</label>
                <input type="text" id="owner_name" wire:model="owner_name"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2  text-xs focus:ring-blue-400"
                    placeholder="Enter owner’s full name">
                <small class="text-red-500">
                    @error('owner_name')
                        {{ $message }}
                    @enderror
                </small>
            </div>

            <!-- Shop Province -->
            <div>
                <label for="shop_province" class="block text-gray-700 font-medium mb-1  text-xs">Shop Province</label>
                <input type="text" id="shop_province" wire:model="shop_province"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2  text-xs focus:ring-blue-400"
                    placeholder="Enter your province">
                <small class="text-red-500">
                    @error('shop_province')
                        {{ $message }}
                    @enderror
                </small>
            </div>

            <!-- Shop City -->
            <div>
                <label for="shop_city" class="block text-gray-700 font-medium mb-1  text-xs">Shop City</label>
                <input type="text" id="shop_city" wire:model="shop_city"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2  text-xs focus:ring-blue-400"
                    placeholder="Enter your city">
                <small class="text-red-500">
                    @error('shop_city')
                        {{ $message }}
                    @enderror
                </small>
            </div>

            <!-- Shop Tole -->
            <div>
                <label for="shop_tole" class="block text-gray-700 font-medium mb-1  text-xs">Shop Tole</label>
                <input type="text" id="shop_tole" wire:model="shop_tole"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2  text-xs focus:ring-blue-400"
                    placeholder="Enter your tole/street">
                <small class="text-red-500">
                    @error('shop_tole')
                        {{ $message }}
                    @enderror
                </small>
            </div>

            <!-- Shop Image -->
            <div>
                <label for="shop_image" class="block text-gray-700 font-medium mb-1  text-xs">Shop Image (Optional)</label>
                <input type="file" id="shop_image" wire:model="shop_image"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:ring-2  text-xs focus:ring-blue-400">
                <small class="text-red-500">
                    @error('shop_image')
                        {{ $message }}
                    @enderror
                </small>

            </div>

            <!-- Shop Phone -->
            <div>
                <label for="shop_phone" class="block text-gray-700 font-medium mb-1  text-xs">Shop Phone</label>
                <input type="text" id="shop_phone" wire:model="shop_phone"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2  text-xs focus:ring-blue-400"
                    placeholder="Enter your shop contact number">
                <small class="text-red-500">
                    @error('shop_phone')
                        {{ $message }}
                    @enderror
                </small>
            </div>

            <!-- Shop Email -->
            <div>
                <label for="shop_email" class="block text-gray-700 font-medium mb-1  text-xs">Shop Email</label>
                <input type="email" id="shop_email" wire:model="shop_email"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2  text-xs focus:ring-blue-400"
                    placeholder="Enter your shop email">
                <small class="text-red-500">
                    @error('shop_email')
                        {{ $message }}
                    @enderror
                </small>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-gray-700 font-medium mb-1  text-xs">Password</label>
                <input type="password" id="password" wire:model="password"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2  text-xs focus:ring-blue-400"
                    placeholder="Enter password">
                <small class="text-red-500">
                    @error('password')
                        {{ $message }}
                    @enderror
                </small>
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="confirm_password" class="block text-gray-700 font-medium mb-1  text-xs">Confirm Password</label>
                <input type="password" id="confirm_password" wire:model="confirm_password"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2  text-xs focus:ring-blue-400"
                    placeholder="Re-enter password">
                <small class="text-red-500">
                    @error('confirm_password')
                        {{ $message }}
                    @enderror
                </small>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit"
                    class="w-full bg-blue-500 text-white font-semibold py-2 rounded-lg  text-xs hover:bg-blue-600 transition duration-200">
                    Register
                </button>
            </div>

            <p class="text-center text-gray-600  text-xs">
                Already have an account?
                <a href="{{ route('vendor.login') }}" class="text-blue-500 hover:underline font-medium">Login</a>
            </p>
        </form>
    </div>
</section>
