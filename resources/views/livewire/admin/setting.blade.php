<x-settings.shell title="Settings" subtitle="Manage your admin profile, password and see what you can do in the console." :tabs="$tabs" :active="$tab">

    @if ($tab === 'profile')
        <x-settings.card title="Profile" description="How you appear to other admins, and the email you sign in with.">
            <form id="profile-form" wire:submit="updateProfile" class="space-y-6">
                <x-settings.avatar name="image" :current="$imageUrl" :preview="$imagePreview" :initials="$initials" />

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-settings.field label="Full name" name="name" required>
                        <x-settings.input wire:model="name" autocomplete="name" placeholder="Your full name" />
                    </x-settings.field>
                    <x-settings.field label="Email address" name="email" required hint="This is your login.">
                        <x-settings.input wire:model="email" type="email" autocomplete="email" placeholder="you@example.com" />
                    </x-settings.field>
                    <x-settings.field label="Phone" name="phone" hint="10 to 15 digits, numbers only.">
                        <x-settings.input wire:model="phone" numeric autocomplete="tel" placeholder="98XXXXXXXX" />
                    </x-settings.field>
                    <x-settings.field label="Address" name="address">
                        <x-settings.input wire:model="address" autocomplete="street-address" placeholder="Optional" />
                    </x-settings.field>
                    <x-settings.field label="Role" name="role" class="sm:col-span-2" hint="Your role is set by a super admin and cannot be changed here.">
                        <div class="flex flex-wrap gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5">
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

    @if ($tab === 'access')
        <x-settings.card title="Your access" description="What your role lets you do. Ask a super admin if you need something changed.">
            <div class="mb-5 flex flex-wrap items-center gap-2 text-sm text-slate-600">
                Role:
                @forelse ($roles as $role)
                    <span class="rounded-full bg-emerald-100 px-3 py-0.5 text-xs font-semibold text-emerald-800">{{ $role }}</span>
                @empty
                    <span class="text-slate-500">none</span>
                @endforelse
            </div>

            @forelse ($permissionGroups as $group => $permissions)
                <div class="mb-4 last:mb-0">
                    <h3 class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $group }}</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($permissions as $permission)
                            <span class="rounded-md border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs text-slate-700">{{ $permission }}</span>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500">Your account has no permissions yet. You can sign in, but cannot open any admin page.</p>
            @endforelse
        </x-settings.card>
    @endif
</x-settings.shell>
