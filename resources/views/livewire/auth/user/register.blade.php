<section class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 py-10">
    <div class="w-full max-w-md bg-white shadow-lg rounded-2xl p-8">
        <h1 class="text-xl font-bold text-gray-800 mb-2 text-center">Create your account</h1>
        <p class="text-xs text-gray-500 text-center mb-6">Verify your email first, then choose a password.</p>

        <a href="{{ route('user.google.redirect') }}"
            class="flex items-center justify-center gap-2 w-full border border-gray-300 rounded-lg py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Continue with Google
        </a>

        <div class="flex items-center gap-3 my-5 text-xs text-gray-400"><span class="h-px bg-gray-200 flex-1"></span>OR<span class="h-px bg-gray-200 flex-1"></span></div>

        <form wire:submit="continueWithEmail" class="space-y-4">
            <div>
                <label for="name" class="block text-gray-700 font-medium mb-1 text-xs">Name</label>
                <input type="text" id="name" wire:model="name" autocomplete="name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Your name">
                @error('name') <small class="text-red-500">{{ $message }}</small> @enderror
            </div>
            <div>
                <label for="email" class="block text-gray-700 font-medium mb-1 text-xs">Gmail address</label>
                <input type="email" id="email" wire:model="email" autocomplete="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="you@gmail.com">
                @error('email') <small class="text-red-500">{{ $message }}</small> @enderror
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white font-semibold py-2 rounded-lg text-sm hover:bg-blue-600">Continue</button>
        </form>

        <p class="text-center text-gray-600 text-xs mt-5">Already have an account? <a href="{{ route('user.login') }}" class="text-blue-500 hover:underline font-medium" wire:navigate>Enter your password</a></p>
    </div>
</section>
