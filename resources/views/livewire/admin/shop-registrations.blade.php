<section class="max-w-7xl mx-auto space-y-7">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-xs font-semibold tracking-[0.16em] text-emerald-700 uppercase">Marketplace management</p><h1 class="mt-1 text-3xl font-bold text-slate-900">Shop Registrations</h1><p class="mt-1 text-sm text-slate-500">Review shop requests submitted from the public site. Only email-verified requests appear here.</p></div>
        <div class="flex items-center gap-2">
            <select wire:model.live="statusFilter" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">All statuses</option>
                <option value="PENDING">Pending</option>
                <option value="APPROVED">Approved</option>
                <option value="REJECTED">Rejected</option>
            </select>
            <x-button icon="{{ $sortDirection === 'desc' ? 'o-arrow-down' : 'o-arrow-up' }}" label="{{ $sortDirection === 'desc' ? 'Newest first' : 'Oldest first' }}" wire:click="toggleSort" class="btn-sm" />
        </div>
    </div>

    <x-card shadow>
        <div class="mb-4 flex items-center justify-between"><h2 class="font-semibold text-slate-800">Registration requests</h2><span class="text-sm text-slate-500">{{ $registrations->total() }} {{ Str::plural('request', $registrations->total()) }}</span></div>
        <x-table :headers="$headers" :rows="$registrations" striped with-pagination>
            @scope('cell_shop_name', $r)<div class="font-medium text-slate-900">{{ $r->shop_name }}</div><small class="text-slate-500">{{ $r->email }}</small>@endscope
            @scope('cell_owner', $r)<div>{{ $r->owner }}</div><small class="text-slate-500">{{ $r->contact_number }}</small>@endscope
            @scope('cell_location', $r)<div>{{ $r->location }}</div><small class="text-slate-500">{{ $r->city }}, {{ $r->tole }}</small>@endscope
            @scope('cell_status', $r)<span class="badge {{ ['APPROVED' => 'badge-success', 'REJECTED' => 'badge-error', 'PENDING' => 'badge-warning'][$r->status] ?? '' }}">{{ ucfirst(strtolower($r->status)) }}</span>@endscope
            @scope('cell_created_at', $r)<span class="text-sm text-slate-500">{{ $r->created_at->format('M d, Y') }}</span>@endscope
            @scope('actions', $r)<div class="flex justify-end gap-1">
                <x-button icon="o-eye" tooltip-left="View details" wire:click="view({{ $r->id }})" class="btn-ghost btn-sm text-slate-600" />
                @authorizeUser('shop-approve', 'admin')
                    @if (in_array($r->status, ['PENDING', 'REJECTED']))
                        <x-button icon="o-check-circle" tooltip-left="{{ $r->status === 'REJECTED' ? 'Approve anyway' : 'Approve' }}" wire:click="confirmApprove({{ $r->id }})" class="btn-ghost btn-sm text-emerald-700" />
                    @endif
                    @if ($r->status === 'PENDING')
                        <x-button icon="o-x-circle" tooltip-left="Reject" wire:click="confirmReject({{ $r->id }})" class="btn-ghost btn-sm text-warning" />
                    @endif
                @endauthorizeUser
                @authorizeUser('shop-delete', 'admin')
                <x-button icon="o-trash" tooltip-left="Delete" wire:click="confirmDelete({{ $r->id }})" class="btn-ghost btn-sm text-error" />
                @endauthorizeUser
            </div>@endscope
        </x-table>
    </x-card>

    <x-modal wire:model="detailModal" title="Registration details" separator box-class="max-w-2xl" @close="closeModals">
        @if ($selected)
            <dl class="grid gap-x-6 gap-y-4 text-sm sm:grid-cols-2">
                <div><dt class="text-slate-500">Shop name</dt><dd class="font-medium text-slate-900">{{ $selected->shop_name }}</dd></div>
                <div><dt class="text-slate-500">Owner</dt><dd class="font-medium text-slate-900">{{ $selected->owner }}</dd></div>
                <div><dt class="text-slate-500">Email</dt><dd class="font-medium text-slate-900">{{ $selected->email }}</dd></div>
                <div><dt class="text-slate-500">PAN number</dt><dd class="font-medium text-slate-900">{{ $selected->pan_number }}</dd></div>
                <div><dt class="text-slate-500">Contact number</dt><dd class="font-medium text-slate-900">{{ $selected->contact_number }}</dd></div>
                <div><dt class="text-slate-500">Status</dt><dd><span class="badge {{ ['APPROVED' => 'badge-success', 'REJECTED' => 'badge-error', 'PENDING' => 'badge-warning'][$selected->status] ?? '' }}">{{ ucfirst(strtolower($selected->status)) }}</span></dd></div>
                <div><dt class="text-slate-500">Province / District</dt><dd class="font-medium text-slate-900">{{ $selected->province?->name }} / {{ $selected->district?->name }}</dd></div>
                <div><dt class="text-slate-500">City / Tole</dt><dd class="font-medium text-slate-900">{{ $selected->city }}, {{ $selected->tole }}</dd></div>
                <div><dt class="text-slate-500">Submitted</dt><dd class="font-medium text-slate-900">{{ $selected->created_at->format('M d, Y h:i A') }}</dd></div>
            </dl>
            @if ($selected->status === 'REJECTED' && $selected->rejection_reason)
                <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800"><strong>Rejection reason:</strong> {{ $selected->rejection_reason }}</div>
            @endif
            <x-slot:actions>
                <x-button label="Close" wire:click="closeModals" />
                @authorizeUser('shop-approve', 'admin')
                    @if ($selected->status === 'PENDING')
                        <x-button label="Reject" icon="o-x-circle" wire:click="confirmReject({{ $selected->id }})" class="btn-warning" />
                    @endif
                    @if (in_array($selected->status, ['PENDING', 'REJECTED']))
                        <x-button label="{{ $selected->status === 'REJECTED' ? 'Approve anyway' : 'Approve' }}" icon="o-check-circle" wire:click="confirmApprove({{ $selected->id }})" class="btn-primary" />
                    @endif
                @endauthorizeUser
            </x-slot:actions>
        @endif
    </x-modal>

    <x-modal wire:model="approveModal" title="Approve registration" subtitle="Set a password for the new shop user account." separator @close="closeModals">
        @if ($selected)
            <div class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50/40 p-4 text-sm">
                <p><span class="text-slate-500">Shop:</span> <strong>{{ $selected->shop_name }}</strong> ({{ $selected->owner }})</p>
                <p class="mt-1"><span class="text-slate-500">Username:</span> <strong>{{ $this->generatedUsernamePreview }}</strong></p>
            </div>
        @endif
        <form wire:submit="approve" class="space-y-4">
            <label class="block"><span class="mb-1 block text-sm font-medium text-slate-700">Password <span class="text-rose-600">*</span></span><input wire:model="password" type="password" class="w-full rounded-lg border border-slate-300 px-3 py-2.5" placeholder="Minimum 8 characters">@error('password')<small class="mt-1 block text-rose-600">{{ $message }}</small>@enderror</label>
            <label class="block"><span class="mb-1 block text-sm font-medium text-slate-700">Confirm password <span class="text-rose-600">*</span></span><input wire:model="password_confirmation" type="password" class="w-full rounded-lg border border-slate-300 px-3 py-2.5" placeholder="Re-enter password"></label>
        </form>
        <x-slot:actions><x-button label="Cancel" icon="o-x-mark" wire:click="closeModals" /><x-button label="Approve & create shop" icon="o-check" wire:click="approve" class="btn-primary" spinner="approve" /></x-slot:actions>
    </x-modal>

    <x-modal wire:model="rejectModal" title="Reject registration" subtitle="The vendor will be emailed this reason and told how to apply again." separator @close="closeModals">
        <label class="block"><span class="mb-1 block text-sm font-medium text-slate-700">Reason for rejection <span class="text-rose-600">*</span></span>
            <textarea wire:model="rejection_reason" rows="4" maxlength="500" class="w-full rounded-lg border border-slate-300 px-3 py-2.5" placeholder="e.g. The PAN number does not match the shop name. Please correct it and apply again."></textarea>
            @error('rejection_reason')<small class="mt-1 block text-rose-600">{{ $message }}</small>@enderror</label>
        <x-slot:actions><x-button label="Cancel" icon="o-x-mark" wire:click="closeModals" /><x-button label="Reject and email vendor" icon="o-x-circle" wire:click="reject" class="btn-warning" spinner="reject" /></x-slot:actions>
    </x-modal>

    <x-modal wire:model="deleteModal" title="Delete registration" separator @close="closeModals"><p class="text-slate-600">Are you sure? This action cannot be undone.</p><x-slot:actions><x-button label="Cancel" icon="o-x-mark" wire:click="closeModals" /><x-button label="Delete" icon="o-trash" wire:click="delete" class="btn-error" spinner="delete" /></x-slot:actions></x-modal>
</section>
