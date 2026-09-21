<x-settings.shell title="Settings" subtitle="Manage your profile, delivery addresses and password." :tabs="$tabs" :active="$tab" accent="indigo">

    @if ($tab === 'profile')
        <x-settings.card title="Profile" description="Your name and email are used on your orders and to sign in.">
            <form id="profile-form" wire:submit="updateProfile" class="space-y-6">
                <x-settings.avatar name="photo" :current="$photoUrl" :preview="$photoPreview" :initials="$initials" accent="indigo" />

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-settings.field label="Full name" name="name" required>
                        <x-settings.input wire:model="name" accent="indigo" autocomplete="name" placeholder="Your full name" />
                    </x-settings.field>
                    <x-settings.field label="Email address" name="email" required>
                        <x-settings.input wire:model="email" type="email" accent="indigo" autocomplete="email" placeholder="you@example.com" />
                    </x-settings.field>
                </div>
            </form>

            <x-slot:footer>
                <x-settings.button type="submit" form="profile-form" target="updateProfile" accent="indigo">Save changes</x-settings.button>
            </x-slot:footer>
        </x-settings.card>
    @endif

    @if ($tab === 'addresses')
        <x-settings.card title="Address book" description="Choose where your orders are delivered. You can pick any of these at checkout.">
            <livewire:user.address-manager mode="manage" />
        </x-settings.card>
    @endif

    @if ($tab === 'security')
        <x-settings.card title="Change password" description="Use at least 8 characters. You will need your current password.">
            <form id="password-form" wire:submit="updatePassword" class="grid max-w-xl gap-5">
                <x-settings.field label="Current password" name="current_password" required>
                    <x-settings.input wire:model="current_password" type="password" accent="indigo" autocomplete="current-password" />
                </x-settings.field>
                <x-settings.field label="New password" name="password" required>
                    <x-settings.input wire:model="password" type="password" accent="indigo" autocomplete="new-password" />
                </x-settings.field>
                <x-settings.field label="Confirm new password" name="password_confirmation" required>
                    <x-settings.input wire:model="password_confirmation" type="password" accent="indigo" autocomplete="new-password" />
                </x-settings.field>
            </form>

            <x-slot:footer>
                <x-settings.button type="submit" form="password-form" target="updatePassword" accent="indigo">Update password</x-settings.button>
            </x-slot:footer>
        </x-settings.card>
    @endif
</x-settings.shell>
