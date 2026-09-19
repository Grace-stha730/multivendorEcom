<section class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 py-10">
    <div class="w-full max-w-md bg-white shadow-lg rounded-2xl p-8">
        <h1 class="text-xl font-bold text-center text-gray-800 mb-6">Create a password</h1>
        @php
            $photo = $profile['photo'] ?? null;
            $photoUrl = $photo
                ? (str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://')
                    ? $photo
                    : asset('storage/' . ltrim($photo, '/')))
                : null;
        @endphp
        <div class="flex items-center gap-3 rounded-lg bg-gray-50 p-3 mb-6">
            @if ($photoUrl)
                <img src="{{ $photoUrl }}" referrerpolicy="no-referrer" class="w-12 h-12 rounded-full object-cover" alt="Profile photo"
                    onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');">
                <div class="hidden w-12 h-12 rounded-full bg-blue-100 text-blue-600 items-center justify-center font-bold">
                    {{ strtoupper(substr($profile['name'] ?? 'U', 0, 1)) }}
                </div>
            @else
                <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold">
                    {{ strtoupper(substr($profile['name'] ?? 'U', 0, 1)) }}
                </div>
            @endif
            <div class="min-w-0">
                <p class="font-medium text-sm text-gray-800 truncate">{{ $profile['name'] }}</p>
                <p class="text-xs text-gray-500 truncate">{{ $profile['email'] }}</p>
            </div>
        </div>
        <form wire:submit="createAccount" class="space-y-4">
            <div><label class="block text-xs font-medium mb-1">Password</label><input type="password" wire:model="password" autocomplete="new-password" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"><small class="text-red-500">@error('password') {{ $message }} @enderror</small></div>
            <div><label class="block text-xs font-medium mb-1">Confirm password</label><input type="password" wire:model="password_confirmation" autocomplete="new-password" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
            <button class="w-full bg-blue-500 text-white font-semibold py-2 rounded-lg text-sm hover:bg-blue-600">Create account</button>
        </form>
    </div>
</section>
