<section class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 py-10">
    <div class="w-full max-w-md bg-white shadow-lg rounded-2xl p-8">
        <h1 class="text-xl font-bold text-center text-gray-800">Verify your email</h1>
        <p class="text-sm text-center text-gray-500 mt-2 mb-6">We sent a 6-digit code to {{ $email }}.</p>
        <form wire:submit="verify" class="space-y-4">
            <x-pin wire:model="otp" size="6" numeric autocomplete="one-time-code" />
            @error('otp') <small class="text-red-500">{{ $message }}</small> @enderror
            <button class="w-full bg-blue-500 text-white font-semibold py-2 rounded-lg text-sm hover:bg-blue-600">Verify code</button>
        </form>
        <x-button label="Resend code" wire:click="resend" spinner="resend" class="btn-ghost w-full mt-4 text-blue-500" />
    </div>
</section>
