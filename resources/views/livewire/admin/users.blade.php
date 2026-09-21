<section class="max-w-7xl mx-auto space-y-7">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold tracking-[0.16em] text-emerald-700 uppercase">Platform staff</p>
            <h1 class="mt-1 text-3xl font-bold text-slate-900">Admin Users</h1>
            <p class="mt-1 text-sm text-slate-500">Create accounts for people who work on the platform. Shop staff accounts are created by each shop's owner in the shop panel, not here.</p>
        </div>
        <x-button label="Add admin user" icon="o-plus" class="btn-primary" wire:click="openCreate" />
    </div>

    <x-card shadow>
        <div class="mb-4 flex items-center justify-between"><h2 class="font-semibold text-slate-800">Admin accounts</h2><span class="text-sm text-slate-500">{{ $admins->total() }} {{ Str::plural('account', $admins->total()) }}</span></div>
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full text-sm">
                <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Created</th></tr></thead>
                <tbody>
                @foreach ($admins as $admin)
                    <tr wire:key="admin-{{ $admin->id }}">
                        <td class="font-medium text-slate-900">{{ $admin->name }}</td>
                        <td>{{ $admin->email }}</td>
                        <td>{{ $admin->phone ?: '-' }}</td>
                        <td>@forelse ($admin->roles as $r)<span class="badge badge-success badge-outline mr-1">{{ $r->name }}</span>@empty<span class="text-slate-400">No role</span>@endforelse</td>
                        <td class="text-slate-500">{{ $admin->created_at?->format('M d, Y') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $admins->links() }}</div>
    </x-card>

    <x-modal wire:model="showForm" title="Add admin user" subtitle="Login details are emailed to the new admin." separator box-class="max-w-2xl">
        @php $input = 'w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm'; @endphp
        <form wire:submit="save" class="grid gap-4 md:grid-cols-2">
            <label class="block"><span class="mb-1 block text-sm font-medium text-slate-700">Full name <span class="text-rose-600">*</span></span>
                <input wire:model="name" class="{{ $input }}" placeholder="Full name">@error('name')<small class="mt-1 block text-rose-600">{{ $message }}</small>@enderror</label>
            <label class="block"><span class="mb-1 block text-sm font-medium text-slate-700">Email (also the login) <span class="text-rose-600">*</span></span>
                <input wire:model="email" type="email" class="{{ $input }}" placeholder="name@example.com">@error('email')<small class="mt-1 block text-rose-600">{{ $message }}</small>@enderror</label>
            <label class="block"><span class="mb-1 block text-sm font-medium text-slate-700">Phone</span>
                <input wire:model="phone" type="number" inputmode="numeric" min="0" step="1" x-data @keydown="['e','E','+','-','.',','].includes($event.key) && $event.preventDefault()" @wheel="$el.blur()" class="{{ $input }}" placeholder="98XXXXXXXX (optional)">@error('phone')<small class="mt-1 block text-rose-600">{{ $message }}</small>@enderror</label>
            <label class="block"><span class="mb-1 block text-sm font-medium text-slate-700">Address</span>
                <input wire:model="address" class="{{ $input }}" placeholder="Optional">@error('address')<small class="mt-1 block text-rose-600">{{ $message }}</small>@enderror</label>
            <label class="block"><span class="mb-1 block text-sm font-medium text-slate-700">Role <span class="text-rose-600">*</span></span>
                <select wire:model="role" class="{{ $input }}"><option value="">Select a role</option>@foreach ($roles as $r)<option value="{{ $r->name }}">{{ $r->name }}</option>@endforeach</select>
                @error('role')<small class="mt-1 block text-rose-600">{{ $message }}</small>@enderror</label>
            <div></div>
            <label class="block"><span class="mb-1 block text-sm font-medium text-slate-700">Password <span class="text-rose-600">*</span></span>
                <input wire:model="password" type="password" class="{{ $input }}" placeholder="Minimum 8 characters">@error('password')<small class="mt-1 block text-rose-600">{{ $message }}</small>@enderror</label>
            <label class="block"><span class="mb-1 block text-sm font-medium text-slate-700">Confirm password <span class="text-rose-600">*</span></span>
                <input wire:model="password_confirmation" type="password" class="{{ $input }}" placeholder="Re-enter password"></label>
            <p class="text-xs text-slate-500 md:col-span-2">You can only give roles that are no stronger than your own. The new admin is asked to change their password after logging in.</p>
        </form>
        <x-slot:actions>
            <x-button label="Cancel" icon="o-x-mark" wire:click="closeForm" />
            <x-button label="Create admin" icon="o-check" wire:click="save" class="btn-primary" spinner="save" />
        </x-slot:actions>
    </x-modal>
</section>
