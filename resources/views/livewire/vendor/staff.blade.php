<section class="max-w-7xl mx-auto space-y-7">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold tracking-[0.16em] text-emerald-700 uppercase">{{ $shopName }}</p>
            <h1 class="mt-1 text-3xl font-bold text-slate-900">Shop Staff</h1>
            <p class="mt-1 text-sm text-slate-500">Create accounts for the people who work in your shop and choose what each of them can do.</p>
        </div>
        @authorizeUser('staff-invite', 'shop_user')
            <x-button label="Add staff" icon="o-plus" class="btn-primary" wire:click="openCreate" />
        @endauthorizeUser
    </div>

    <x-card shadow>
        <div class="mb-4 flex items-center justify-between"><h2 class="font-semibold text-slate-800">Your team</h2><span class="text-sm text-slate-500">{{ $staff->total() }} {{ Str::plural('member', $staff->total()) }}</span></div>
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full text-sm">
                <thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                @foreach ($staff as $member)
                    <tr wire:key="staff-{{ $member->id }}">
                        <td class="font-medium text-slate-900">{{ $member->name }} @if ($member->id === $me->id)<span class="badge badge-ghost badge-sm">you</span>@endif</td>
                        <td>{{ $member->username }}</td>
                        <td>{{ $member->personal_email ?: '-' }}</td>
                        <td>@forelse ($member->roles as $r)<span class="badge badge-success badge-outline mr-1">{{ $r->name }}</span>@empty<span class="text-slate-400">No role</span>@endforelse</td>
                        <td class="text-right">
                            @authorizeUser('staff-assign-role', 'shop_user')
                                <x-button label="Change role" icon="o-pencil-square" wire:click="openRole({{ $member->id }})" class="btn-ghost btn-sm text-emerald-700" />
                            @endauthorizeUser
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $staff->links() }}</div>
        </div>
    </x-card>

    @php $input = 'w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm'; @endphp

    <x-modal wire:model="showForm" title="Add staff" subtitle="Their login details are emailed to them." separator box-class="max-w-2xl">
        <form wire:submit="save" class="grid gap-4 md:grid-cols-2">
            <label class="block"><span class="mb-1 block text-sm font-medium text-slate-700">Full name <span class="text-rose-600">*</span></span>
                <input wire:model="name" class="{{ $input }}" placeholder="Full name">@error('name')<small class="mt-1 block text-rose-600">{{ $message }}</small>@enderror</label>
            <label class="block"><span class="mb-1 block text-sm font-medium text-slate-700">Their email <span class="text-rose-600">*</span></span>
                <input wire:model="personal_email" type="email" class="{{ $input }}" placeholder="name@example.com">@error('personal_email')<small class="mt-1 block text-rose-600">{{ $message }}</small>@enderror</label>
            <label class="block"><span class="mb-1 block text-sm font-medium text-slate-700">Contact number</span>
                <input wire:model="contact" type="number" inputmode="numeric" min="0" step="1" x-data @keydown="['e','E','+','-','.',','].includes($event.key) && $event.preventDefault()" @wheel="$el.blur()" class="{{ $input }}" placeholder="98XXXXXXXX (optional)">@error('contact')<small class="mt-1 block text-rose-600">{{ $message }}</small>@enderror</label>
            <label class="block"><span class="mb-1 block text-sm font-medium text-slate-700">Role <span class="text-rose-600">*</span></span>
                <select wire:model="role" class="{{ $input }}"><option value="">Select a role</option>@foreach ($roles as $r)<option value="{{ $r->name }}">{{ $r->name }}</option>@endforeach</select>
                @error('role')<small class="mt-1 block text-rose-600">{{ $message }}</small>@enderror</label>
            <label class="block"><span class="mb-1 block text-sm font-medium text-slate-700">Password <span class="text-rose-600">*</span></span>
                <input wire:model="password" type="password" class="{{ $input }}" placeholder="Minimum 8 characters">@error('password')<small class="mt-1 block text-rose-600">{{ $message }}</small>@enderror</label>
            <label class="block"><span class="mb-1 block text-sm font-medium text-slate-700">Confirm password <span class="text-rose-600">*</span></span>
                <input wire:model="password_confirmation" type="password" class="{{ $input }}" placeholder="Re-enter password"></label>
            <p class="text-xs text-slate-500 md:col-span-2">Their username is created automatically and sent to them with the login link. They join <strong>{{ $shopName }}</strong> only. You can only give roles that are no stronger than your own.</p>
        </form>
        <x-slot:actions>
            <x-button label="Cancel" icon="o-x-mark" wire:click="closeForm" />
            <x-button label="Create staff account" icon="o-check" wire:click="save" class="btn-primary" spinner="save" />
        </x-slot:actions>
    </x-modal>

    <x-modal wire:model="roleModal" title="Change role" separator>
        <label class="block"><span class="mb-1 block text-sm font-medium text-slate-700">Role</span>
            <select wire:model="newRole" class="{{ $input }}"><option value="">Select a role</option>@foreach ($roles as $r)<option value="{{ $r->name }}">{{ $r->name }}</option>@endforeach</select>
            @error('newRole')<small class="mt-1 block text-rose-600">{{ $message }}</small>@enderror</label>
        <x-slot:actions>
            <x-button label="Cancel" icon="o-x-mark" wire:click="$set('roleModal', false)" />
            <x-button label="Save role" icon="o-check" wire:click="saveRole" class="btn-primary" spinner="saveRole" />
        </x-slot:actions>
    </x-modal>
</section>
