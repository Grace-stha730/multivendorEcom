<section class="min-h-screen bg-gradient-to-br from-gray-100 to-gray-200 py-10 px-4">
    <div class="mx-auto max-w-4xl space-y-8">
        <div class="rounded-2xl bg-white p-8 shadow-lg">
            <h1 class="text-2xl font-bold text-gray-800">Register your shop</h1>
            <p class="mt-1 mb-6 text-sm text-gray-500">Submit your shop details. We'll email a 6-digit code to confirm your email, then an admin will review your request.</p>

            <form wire:submit="submit" class="grid gap-x-6 gap-y-5 md:grid-cols-2">
                <label class="block"><span class="form-label">Shop name <span class="text-rose-600">*</span></span><input wire:model="shop_name" class="form-input" placeholder="e.g. Himalayan Mart">@error('shop_name')<small class="form-error">{{ $message }}</small>@enderror</label>
                <label class="block"><span class="form-label">Owner name <span class="text-rose-600">*</span></span><input wire:model="owner" class="form-input" placeholder="Owner's full name">@error('owner')<small class="form-error">{{ $message }}</small>@enderror</label>
                <label class="block"><span class="form-label">Email <span class="text-rose-600">*</span></span><input wire:model="email" type="email" class="form-input" placeholder="Shop email or your personal email">@error('email')<small class="form-error">{{ $message }}</small>@enderror</label>
                <label class="block"><span class="form-label">PAN number <span class="text-rose-600">*</span></span><input wire:model="pan_number" inputmode="numeric" maxlength="9" class="form-input" placeholder="9-digit PAN">@error('pan_number')<small class="form-error">{{ $message }}</small>@enderror</label>
                <label class="block"><span class="form-label">Contact number <span class="text-rose-600">*</span></span><input wire:model="contact_number" class="form-input" placeholder="98XXXXXXXX">@error('contact_number')<small class="form-error">{{ $message }}</small>@enderror</label>
                <label class="block"><span class="form-label">Province <span class="text-rose-600">*</span></span>
                    <select wire:model.live="province_id" class="form-input"><option value="">Select province</option>@foreach ($provinces as $province)<option value="{{ $province->id }}">{{ $province->name }}</option>@endforeach</select>
                    @error('province_id')<small class="form-error">{{ $message }}</small>@enderror</label>
                <label class="block"><span class="form-label">District <span class="text-rose-600">*</span></span>
                    <select wire:model="district_id" class="form-input" @disabled(!$province_id)><option value="">Select district</option>@foreach ($districts as $district)<option value="{{ $district->id }}">{{ $district->name }}</option>@endforeach</select>
                    @error('district_id')<small class="form-error">{{ $message }}</small>@enderror</label>
                <label class="block"><span class="form-label">City <span class="text-rose-600">*</span></span><input wire:model="city" class="form-input" placeholder="City">@error('city')<small class="form-error">{{ $message }}</small>@enderror</label>
                <label class="block"><span class="form-label">Tole / street <span class="text-rose-600">*</span></span><input wire:model="tole" class="form-input" placeholder="Tole or street">@error('tole')<small class="form-error">{{ $message }}</small>@enderror</label>
                <div class="md:col-span-2"><x-button label="Submit registration" type="submit" icon="o-paper-airplane" class="btn-primary" spinner="submit" /></div>
            </form>
        </div>

        <div class="rounded-2xl bg-white p-8 shadow-lg">
            <h2 class="text-lg font-bold text-gray-800">Already registered?</h2>
            <p class="mt-1 mb-4 text-sm text-gray-500">Enter your email and we'll send a code so you can confirm your email or check your registration status.</p>
            <form wire:submit="requestStatusCode" class="flex flex-col gap-3 sm:flex-row sm:items-start">
                <div class="flex-1"><input wire:model="statusEmail" type="email" class="form-input" placeholder="you@example.com">@error('statusEmail')<small class="form-error">{{ $message }}</small>@enderror</div>
                <x-button label="Check status" type="submit" icon="o-magnifying-glass" class="btn-outline" spinner="requestStatusCode" />
            </form>
        </div>
    </div>

    <x-modal wire:model="verifyModal" title="{{ $verifiedStatus ? 'Registration status' : 'Verify your email' }}" separator persistent>
        @if ($verifiedStatus)
            <div class="space-y-3 text-center">
                <p class="text-gray-600">Your email <strong>{{ $verifyEmail }}</strong> is confirmed.</p>
                <span class="badge badge-lg {{ ['APPROVED' => 'badge-success', 'REJECTED' => 'badge-error', 'PENDING' => 'badge-warning'][$verifiedStatus] ?? '' }}">{{ ucfirst(strtolower($verifiedStatus)) }}</span>
                <p class="text-sm text-gray-500">
                    @if ($verifiedStatus === 'PENDING') An admin is reviewing your request.
                    @elseif ($verifiedStatus === 'APPROVED') Your shop was approved. Your login details were emailed to you; please check your inbox.
                    @else Your request was not approved. You may contact us for details.
                    @endif
                </p>
            </div>
            <x-slot:actions><x-button label="Close" wire:click="closeVerifyModal" class="btn-primary" /></x-slot:actions>
        @else
            <p class="mb-4 text-sm text-gray-500">If a registration exists for {{ $verifyEmail }}, we sent a 6-digit code to it. It expires in 10 minutes.</p>
            <form wire:submit="verify" class="space-y-4">
                <x-pin wire:model="otp" size="6" numeric autocomplete="one-time-code" />
                @error('otp')<small class="text-red-500">{{ $message }}</small>@enderror
                @if (session('code-sent'))<small class="block text-emerald-600">{{ session('code-sent') }}</small>@endif
                <x-button label="Verify code" type="submit" class="btn-primary w-full" spinner="verify" />
            </form>
            <x-button label="Resend code" wire:click="resend" spinner="resend" class="btn-ghost mt-3 w-full text-blue-500" />
            <x-slot:actions><x-button label="Close" wire:click="closeVerifyModal" /></x-slot:actions>
        @endif
    </x-modal>
</section>

<style>.form-label{display:block;margin-bottom:.375rem;font-size:.875rem;font-weight:500;color:#334155}.form-input{width:100%;border:1px solid #cbd5e1;border-radius:.5rem;background:#fff;padding:.625rem .75rem;color:#0f172a;outline:none}.form-input:focus{border-color:#059669;box-shadow:0 0 0 3px rgb(16 185 129 / .12)}.form-error{display:block;margin-top:.35rem;color:#e11d48}</style>
