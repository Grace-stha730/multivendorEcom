<section class="min-h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 py-10">
    <div class="w-[60%] max-w-md bg-white shadow-lg rounded-2xl p-8">
        <h1 class="text-xl font-bold text-gray-800 mb-6 text-center">🛍️ Login</h1>

        <form wire:submit.prevent="login" class="space-y-5">
            <x-input label="Email address" wire:model="email" type="email" icon="o-envelope" placeholder="Enter your email" class="login-input" />
            <x-password label="Password" wire:model="password" right placeholder="Enter password" class="login-password" />
            <div class="text-right -mt-3"><a href="{{ route('user.password.forgot') }}" wire:navigate class="text-xs text-blue-500 hover:underline">Forgot password?</a></div>

            <x-button label="Login" icon="o-arrow-right-on-rectangle" type="submit" class="btn-primary w-full" spinner="login" />

            <a href="{{ route('user.google.redirect') }}" class="block w-full border border-gray-300 text-center text-sm text-gray-700 font-medium py-2 rounded-lg hover:bg-gray-50">
                Continue with Google
            </a>

            <p class="text-center text-gray-600  text-xs">
                Don't have an account?
                <a href="{{ route('user.register') }}" wire:navigate class="text-blue-500 hover:underline font-medium">Register</a>
            </p>

        </form>
    </div>
</section>
