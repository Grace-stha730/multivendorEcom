<section class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 py-10">
    <div class="w-full max-w-xl bg-white shadow-lg rounded-2xl p-8">
        <h1 class="text-xl font-bold text-gray-800 mb-6 text-center">🛒 Admin Registration</h1>

        <form wire:submit.prevent="register" enctype="multipart/form-data" class="space-y-4">
            <!-- Name -->
            <div>
                <label for="name" class="block text-gray-700 font-medium mb-1 text-xs">Name</label>
                <input type="text" id="name" wire:model="name"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 text-xs focus:ring-blue-400"
                    placeholder="Enter name">
                <small class="text-red-500">
                    @error('name')
                        {{ $message }}
                    @enderror
                </small>
            </div>


            <!-- Image -->
            <div>
                <label for="image" class="block text-gray-700 font-medium mb-1  text-xs">Image
                    (Optional)</label>
                <input type="file" id="image" wire:model="image"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:ring-2  text-xs focus:ring-blue-400">
                <small class="text-red-500">
                    @error('image')
                        {{ $message }}
                    @enderror
                </small>

            </div>

            <!-- Phone -->
            <div>
                <label for="phone" class="block text-gray-700 font-medium mb-1  text-xs">Phone</label>
                <input type="text" id="phone" wire:model="phone"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2  text-xs focus:ring-blue-400"
                    placeholder="Enter contact number">
                <small class="text-red-500">
                    @error('phone')
                        {{ $message }}
                    @enderror
                </small>
            </div>

            <!-- department -->
            <div>
                <label for="department" class="block text-gray-700 font-medium mb-1  text-xs">Department</label>
                <input type="text" id="department" wire:model="department"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2  text-xs focus:ring-blue-400"
                    placeholder="Enter Department">
                <small class="text-red-500">
                    @error('department')
                        {{ $message }}
                    @enderror
                </small>
            </div>

            <!-- address -->
            <div>
                <label for="address" class="block text-gray-700 font-medium mb-1  text-xs">Address</label>
                <input type="text" id="address" wire:model="address"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2  text-xs focus:ring-blue-400"
                    placeholder="Enter Address">
                <small class="text-red-500">
                    @error('address')
                        {{ $message }}
                    @enderror
                </small>
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-gray-700 font-medium mb-1  text-xs">Email</label>
                <input type="email" id="email" wire:model="email"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2  text-xs focus:ring-blue-400"
                    placeholder="Enter email">
                <small class="text-red-500">
                    @error('email')
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
                    class="w-full cursor-pointer bg-blue-500 text-white font-semibold py-2 rounded-lg  text-xs hover:bg-blue-600 transition duration-200">
                    Register
                </button>
            </div>

            <p class="text-center text-gray-600  text-xs">
                Already have an account?
                <a href="{{ route('admin.login') }}" class="text-blue-500 hover:underline font-medium">Login</a>
            </p>
        </form>
    </div>
</section>
