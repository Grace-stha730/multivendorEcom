<x-settings.shell title="Settings" :subtitle="'Manage your account and ' . ($shop?->name ?? 'your shop') . '.'" :tabs="$tabs" :active="$tab">

    @if ($tab === 'profile')
        <x-settings.card title="My profile" description="Your own login account in this shop.">
            <form id="profile-form" wire:submit="updateProfile" class="space-y-6">
                <x-settings.avatar name="photo" :current="$photoUrl" :preview="$photoPreview" :initials="strtoupper(mb_substr($name ?: 'S', 0, 1))" />

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-settings.field label="Full name" name="name" required>
                        <x-settings.input wire:model="name" autocomplete="name" placeholder="Your full name" />
                    </x-settings.field>
                    <x-settings.field label="Username" name="username" hint="Your login. It can only be changed by an admin.">
                        <x-settings.input :value="$me->username" disabled />
                    </x-settings.field>
                    <x-settings.field label="Your email" name="personal_email" hint="Used to reset your password and receive account emails.">
                        <x-settings.input wire:model="personal_email" type="email" autocomplete="email" placeholder="you@example.com" />
                    </x-settings.field>
                    <x-settings.field label="Contact number" name="contact" hint="10 to 15 digits, numbers only.">
                        <x-settings.input wire:model="contact" numeric autocomplete="tel" placeholder="98XXXXXXXX" />
                    </x-settings.field>
                    <x-settings.field label="Address" name="address">
                        <x-settings.input wire:model="address" autocomplete="street-address" placeholder="Optional" />
                    </x-settings.field>
                    <x-settings.field label="Role" name="role" hint="Set by your shop owner.">
                        <div class="flex min-h-[2.375rem] flex-wrap items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5">
                            @forelse ($roles as $role)
                                <span class="rounded-full bg-emerald-100 px-3 py-0.5 text-xs font-semibold text-emerald-800">{{ $role }}</span>
                            @empty
                                <span class="text-sm text-slate-500">No role assigned</span>
                            @endforelse
                        </div>
                    </x-settings.field>
                </div>
            </form>

            <x-slot:footer>
                <x-settings.button type="submit" form="profile-form" target="updateProfile">Save changes</x-settings.button>
            </x-slot:footer>
        </x-settings.card>
    @endif

    @if ($tab === 'shop')
        <x-settings.card title="Shop details" description="What customers and the platform see about your shop.">
            @unless ($canEditShop)
                <p class="mb-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">You can view these details, but only someone with permission to edit shop settings (usually the owner) can change them.</p>
            @endunless

            <form id="shop-form" wire:submit="updateShop" class="space-y-6">
                <x-settings.avatar name="shop_logo" :current="$logoUrl" :preview="$logoPreview" :initials="strtoupper(mb_substr($shop_name ?: 'S', 0, 1))" label="Change logo" :square="true" />

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-settings.field label="Shop name" name="shop_name" required>
                        <x-settings.input wire:model="shop_name" :disabled="!$canEditShop" placeholder="Shop name" />
                    </x-settings.field>
                    <x-settings.field label="Owner name" name="shop_owner" required>
                        <x-settings.input wire:model="shop_owner" :disabled="!$canEditShop" placeholder="Owner's full name" />
                    </x-settings.field>
                    <x-settings.field label="Shop email" name="shop_email" required hint="Shown to the platform. Also used to reset the owner's password.">
                        <x-settings.input wire:model="shop_email" type="email" :disabled="!$canEditShop" placeholder="shop@example.com" />
                    </x-settings.field>
                    <x-settings.field label="Shop contact number" name="shop_contact" required hint="10 to 15 digits, numbers only.">
                        <x-settings.input wire:model="shop_contact" numeric :disabled="!$canEditShop" placeholder="98XXXXXXXX" />
                    </x-settings.field>
                    <x-settings.field label="Province" name="shop_province_id" required>
                        <x-settings.select wire:model.live="shop_province_id" :disabled="!$canEditShop">
                            <option value="">Select province</option>
                            @foreach ($provinces as $province)<option value="{{ $province->id }}">{{ $province->name }}</option>@endforeach
                        </x-settings.select>
                    </x-settings.field>
                    <x-settings.field label="District" name="shop_district_id" required>
                        <x-settings.select wire:model="shop_district_id" :disabled="!$canEditShop || !$shop_province_id">
                            <option value="">Select district</option>
                            @foreach ($districts as $district)<option value="{{ $district->id }}">{{ $district->name }}</option>@endforeach
                        </x-settings.select>
                    </x-settings.field>
                    <x-settings.field label="City" name="shop_city" required>
                        <x-settings.input wire:model="shop_city" :disabled="!$canEditShop" placeholder="City" />
                    </x-settings.field>
                    <x-settings.field label="Tole / street" name="shop_tole" required>
                        <x-settings.input wire:model="shop_tole" :disabled="!$canEditShop" placeholder="Tole or street" />
                    </x-settings.field>
                    <x-settings.field label="PAN number" name="pan" hint="Verified by the platform. Contact an admin to change it.">
                        <x-settings.input :value="$shop?->pan_number ?: 'Not provided'" disabled />
                    </x-settings.field>
                    <x-settings.field label="Shop status" name="status">
                        <div class="flex h-[2.375rem] items-center">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $shop?->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ ucfirst($shop?->status ?? 'unknown') }}</span>
                        </div>
                    </x-settings.field>
                </div>
            </form>

            @if ($canEditShop)
                <x-slot:footer>
                    <x-settings.button type="submit" form="shop-form" target="updateShop">Save shop details</x-settings.button>
                </x-slot:footer>
            @endif
        </x-settings.card>
    @endif

    @if ($tab === 'ai')
        <x-settings.card title="AI assistant" description="Let the AI send a first reply to customers who message your shop and get no answer.">
            <label class="flex items-start gap-4 {{ $canEditShop ? 'cursor-pointer' : 'cursor-not-allowed opacity-70' }}">
                <input type="checkbox" wire:model="aiAutoReplyEnabled" @disabled(!$canEditShop) class="mt-1 h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-300">
                <span>
                    <span class="block text-sm font-semibold text-slate-900">Reply automatically when I am slow to respond</span>
                    <span class="mt-1 block text-sm text-slate-500">If a customer's message stays unanswered for a few minutes, the AI answers using your store policies. As soon as you or a teammate replies, the AI stops for that conversation.</span>
                </span>
            </label>
            @unless ($canEditShop)
                <p class="mt-4 text-sm text-amber-700">Only someone with permission to edit shop settings can change this.</p>
            @endunless

            @if ($canEditShop)
                <x-slot:footer>
                    <x-settings.button type="button" wire:click="updateAiAutoReply" target="updateAiAutoReply">Save AI settings</x-settings.button>
                </x-slot:footer>
            @endif
        </x-settings.card>
    @endif

    @if ($tab === 'security')
        <x-settings.card title="Change password" description="Use at least 8 characters. You will need your current password.">
            <form id="password-form" wire:submit="updatePassword" class="grid max-w-xl gap-5">
                <x-settings.field label="Current password" name="current_password" required>
                    <x-settings.input wire:model="current_password" type="password" autocomplete="current-password" />
                </x-settings.field>
                <x-settings.field label="New password" name="password" required>
                    <x-settings.input wire:model="password" type="password" autocomplete="new-password" />
                </x-settings.field>
                <x-settings.field label="Confirm new password" name="password_confirmation" required>
                    <x-settings.input wire:model="password_confirmation" type="password" autocomplete="new-password" />
                </x-settings.field>
            </form>

            <x-slot:footer>
                <x-settings.button type="submit" form="password-form" target="updatePassword">Update password</x-settings.button>
            </x-slot:footer>
        </x-settings.card>
    @endif
</x-settings.shell>
