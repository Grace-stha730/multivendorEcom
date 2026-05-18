<section class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 py-10">
    <div class="w-full max-w-xl bg-white shadow-lg rounded-2xl p-8">
        <h1 class="text-xl font-bold text-gray-800 mb-6 text-center">🛒 Registration</h1>

        <form wire:submit.prevent="register" enctype="multipart/form-data" class="space-y-4">
            <!-- Name -->
            <div>
                <label for="name" class="block text-gray-700 font-medium mb-1 text-xs">Name</label>
                <input type="text" id="name" wire:model="name"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 text-xs focus:ring-blue-400"
                    placeholder="Enter your name">
                <small class="text-red-500">
                    @error('name')
                        {{ $message }}
                    @enderror
                </small>
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-gray-700 font-medium mb-1  text-xs">Email</label>
                <input type="email" id="email" wire:model="email"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2  text-xs focus:ring-blue-400"
                    placeholder="Enter your email">
                <small class="text-red-500">
                    @error('email')
                        {{ $message }}
                    @enderror
                </small>
            </div>

            <!-- Province -->
            <div>
                <label for="province" class="block text-gray-700 font-medium mb-1  text-xs">Province</label>
                <input type="text" id="province" wire:model="province"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2  text-xs focus:ring-blue-400"
                    placeholder="Enter your province">
                <small class="text-red-500">
                    @error('province')
                        {{ $message }}
                    @enderror
                </small>
            </div>

            <!-- City -->
            <div>
                <label for="city" class="block text-gray-700 font-medium mb-1  text-xs">City</label>
                <input type="text" id="city" wire:model="city"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2  text-xs focus:ring-blue-400"
                    placeholder="Enter your city">
                <small class="text-red-500">
                    @error('city')
                        {{ $message }}
                    @enderror
                </small>
            </div>

            <!-- Tole -->
            <div>
                <label for="tole" class="block text-gray-700 font-medium mb-1  text-xs">Tole</label>
                <input type="text" id="tole" wire:model="tole"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2  text-xs focus:ring-blue-400"
                    placeholder="Enter your tole/street">
                <small class="text-red-500">
                    @error('tole')
                        {{ $message }}
                    @enderror
                </small>
            </div>

            <!-- Image -->
            <div>
                <label for="photo" class="block text-gray-700 font-medium mb-1  text-xs">Image
                    (Optional)</label>
                <input type="file" id="photo" wire:model="photo"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:ring-2  text-xs focus:ring-blue-400">
                <small class="text-red-500">
                    @error('photo')
                        {{ $message }}
                    @enderror
                </small>

            </div>

            <!-- Phone -->
            <div>
                <label for="phone" class="block text-gray-700 font-medium mb-1  text-xs">Phone</label>
                <input type="number" id="phone" wire:model="phone"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2  text-xs focus:ring-blue-400"
                    placeholder="Enter your contact number">
                <small class="text-red-500">
                    @error('phone')
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
                <label for="confirm_password" class="block text-gray-700 font-medium mb-1  text-xs">Confirm
                    Password</label>
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
                <a href="{{ route('user.login') }}" class="text-blue-500 hover:underline font-medium"
                    wire:navigate>Login</a>
            </p>
        </form>
    </div>
</section>
