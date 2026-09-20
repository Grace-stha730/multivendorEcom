<section class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 py-10 px-4">
    <div class="w-full max-w-md bg-white shadow-lg rounded-2xl p-8">
        <h1 class="text-xl font-bold text-gray-800 text-center">Forgot password</h1>

        @if ($step === 'request')
            <p class="text-sm text-center text-gray-500 mt-2 mb-6">
                @if ($guard === 'shop_user')
                    Enter the email address registered with your shop and we'll email you a 6-digit reset code.
                @else
                    Enter your account's email address and we'll email you a 6-digit reset code.
                @endif
            </p>
            <form wire:submit="sendCode" class="space-y-5">
                <x-input label="Email address" wire:model="identifier" type="email" icon="o-envelope" placeholder="Enter your email address" />
                <x-button label="Send reset code" icon="o-paper-airplane" type="submit" class="btn-primary w-full" spinner="sendCode" />
            </form>
        @else
            <p class="text-sm text-center text-gray-500 mt-2">If an account matches <strong>{{ $identifier }}</strong>, a 6-digit code was sent to it. It expires in 10 minutes.</p>
            <p class="text-xs text-center text-gray-400 mt-1 mb-6">No email? Check your spam folder and make sure this is the email registered with your account. You can request a new code after a minute.</p>
            <form wire:submit="resetPassword" class="space-y-5">
                <div>
                    <span class="block text-sm font-medium text-gray-700 mb-2">Reset code</span>
                    <x-pin wire:model="code" size="6" numeric autocomplete="one-time-code" />
                    @error('code') <small class="text-red-500">{{ $message }}</small> @enderror
                </div>
                <x-password label="New password" wire:model="password" right placeholder="Minimum 8 characters" />
                <x-password label="Confirm new password" wire:model="password_confirmation" right placeholder="Re-enter password" />
                <x-button label="Reset password" icon="o-check" type="submit" class="btn-primary w-full" spinner="resetPassword" />
            </form>
            <x-button label="Use a different email / resend code" wire:click="backToRequest" class="btn-ghost w-full mt-3 text-blue-500" />
        @endif

        <p class="mt-6 text-center text-sm"><a href="{{ $loginUrl }}" wire:navigate class="text-blue-500 hover:underline">Back to login</a></p>
    </div>
</section>
