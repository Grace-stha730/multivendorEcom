<section class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 py-10">
    <div class="w-[60%] max-w-lg bg-white shadow-lg rounded-2xl p-8">
        <h1 class="text-xl font-bold text-gray-800 mb-6 text-center">🛍️ Admin Login</h1>

        <form wire:submit.prevent="login" class="space-y-5">
            <x-input label="Email address" wire:model="email" type="email" icon="o-envelope" placeholder="Enter your email" class="login-input" />
            <x-password label="Password" wire:model="password" right placeholder="Enter password" class="login-password" />
            <div class="text-right -mt-3"><a href="{{ route('admin.password.forgot') }}" wire:navigate class="text-xs text-blue-500 hover:underline">Forgot password?</a></div>

            <x-button label="Login" icon="o-arrow-right-on-rectangle" type="submit" class="btn-primary w-full" spinner="login" />


        </form>
    </div>
</section>
