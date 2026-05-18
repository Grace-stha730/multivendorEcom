<section class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 py-10">
    <div class="w-[60%] max-w-lg bg-white shadow-lg rounded-2xl p-8">
        <h1 class="text-xl font-bold text-gray-800 mb-6 text-center">🛍️ Admin Login</h1>

        <form wire:submit="login" class="space-y-5">
            <!-- Email -->
            <div>
                <label for="email" class="block text-gray-700  text-sm font-medium mb-1">Email Address</label>
                <input type="email" id="email" wire:model="email"
                    class="w-full border border-gray-300 rounded-lg px-3 text-sm py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    placeholder="Enter your email">
                @error('email')
                    <small class="text-red-500">{{ $message }}</small>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-gray-700 font-medium mb-1 text-sm">Password</label>
                <input type="password" id="password" wire:model="password"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                    placeholder="Enter password">
                @error('password')
                    <small class="text-red-500">{{ $message }}</small>
                @enderror
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit"
                    class="w-full bg-blue-500 text-white text-sm font-semibold py-2 rounded-lg hover:bg-blue-600 transition duration-200 cursor-pointer">
                    Login
                </button>
            </div>


        </form>
    </div>
</section>
