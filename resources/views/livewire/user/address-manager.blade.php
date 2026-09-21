<div>
    <div class="mb-4 flex items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">{{ $mode === 'select' ? 'Delivery address' : 'My addresses' }}</h2>
            @if ($mode === 'manage')
                <p class="text-sm text-gray-500">Your first address is your real (main) address. Add shipping addresses for anywhere else you want orders delivered.</p>
            @endif
        </div>
        @if ($addresses->isNotEmpty())
            <button type="button" wire:click="openCreate" class="rounded-lg border border-indigo-600 px-3 py-1.5 text-sm font-semibold text-indigo-600 hover:bg-indigo-50">+ Add address</button>
        @endif
    </div>

    @if ($addresses->isEmpty())
        <div class="rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-8 text-center">
            <p class="font-medium text-gray-700">
                {{ $mode === 'select' ? 'Please add a delivery address to continue.' : 'You have no saved addresses yet.' }}
            </p>
            <button type="button" wire:click="openCreate" class="mt-4 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Add Address</button>
        </div>
    @else
        <div class="grid gap-3 md:grid-cols-2">
            @foreach ($addresses as $address)
                @php $selected = $mode === 'select' && (int) $selectedId === $address->id; @endphp
                <div wire:key="address-{{ $address->id }}"
                    @if ($mode === 'select') wire:click="select({{ $address->id }})" @endif
                    class="relative rounded-xl border p-4 text-sm {{ $mode === 'select' ? 'cursor-pointer' : '' }} {{ $selected ? 'border-indigo-600 bg-indigo-50 ring-2 ring-indigo-200' : 'border-gray-200 bg-white hover:border-gray-300' }}">

                    <div class="mb-2 flex flex-wrap items-center gap-1.5">
                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $address->isReal() ? 'bg-emerald-100 text-emerald-700' : 'bg-sky-100 text-sky-700' }}">{{ $address->isReal() ? 'Real address' : 'Shipping address' }}</span>
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">{{ ucfirst($address->address_type) }}</span>
                        @if ($address->is_default)
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">Default</span>
                        @endif
                        @if ($selected)
                            <span class="ml-auto text-xs font-semibold text-indigo-600">✓ Selected</span>
                        @endif
                    </div>

                    <p class="font-semibold text-gray-900">{{ $address->receiver_name }}</p>
                    <p class="text-gray-600">{{ $address->fullAddress() }}</p>
                    <p class="text-gray-600">Contact: {{ $address->contact }}</p>
                    @if ($address->officeHours())
                        <p class="text-gray-600">Office hours: {{ $address->officeHours() }}</p>
                    @endif

                    @unless ($address->isComplete())
                        <p class="mt-2 rounded bg-amber-50 px-2 py-1 text-xs text-amber-800">Province and district are missing. Please complete this address before using it.</p>
                    @endunless

                    <div class="mt-3 flex flex-wrap gap-3 text-xs font-semibold" @if ($mode === 'select') wire:click.stop @endif>
                        <button type="button" wire:click="openEdit({{ $address->id }})" class="text-indigo-600 hover:underline">{{ $address->isComplete() ? 'Edit' : 'Complete address' }}</button>
                        @if ($mode === 'manage')
                            @unless ($address->is_default)
                                <button type="button" wire:click="makeDefault({{ $address->id }})" class="text-gray-600 hover:underline">Set as default</button>
                            @endunless
                            <button type="button" wire:click="confirmDelete({{ $address->id }})" class="text-red-600 hover:underline">Delete</button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Add / edit form (the same form on checkout and on the Settings page) --}}
    <x-modal wire:model="showForm" :title="$editingId ? 'Edit address' : 'Add a delivery address'" separator box-class="max-w-2xl">
        @php
            $editing = $editingId ? $addresses->firstWhere('id', $editingId) : null;
            $input = 'w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm';
        @endphp

        @if (!$editingId && $addresses->isEmpty())
            <p class="mb-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-800">This first address will be saved as your <strong>real address</strong>. Any address you add later is a shipping address.</p>
        @elseif ($editing?->isReal())
            <p class="mb-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-800">This is your <strong>real address</strong>.</p>
        @endif

        <form wire:submit="save" class="grid gap-4 md:grid-cols-2">
            <label class="block"><span class="mb-1 block text-sm font-medium text-slate-700">Receiver name <span class="text-rose-600">*</span></span>
                <input wire:model="receiver_name" class="{{ $input }}" placeholder="Who receives the order?">
                @error('receiver_name')<small class="mt-1 block text-rose-600">{{ $message }}</small>@enderror</label>

            <label class="block"><span class="mb-1 block text-sm font-medium text-slate-700">Contact number <span class="text-rose-600">*</span></span>
                <input wire:model="contact" type="number" inputmode="numeric" min="0" step="1" x-data @keydown="['e','E','+','-','.',','].includes($event.key) && $event.preventDefault()" @wheel="$el.blur()" class="{{ $input }}" placeholder="98XXXXXXXX (at least 10 digits)">
                @error('contact')<small class="mt-1 block text-rose-600">{{ $message }}</small>@enderror</label>

            <label class="block"><span class="mb-1 block text-sm font-medium text-slate-700">Province <span class="text-rose-600">*</span></span>
                <select wire:model.live="province_id" class="{{ $input }}"><option value="">Select province</option>@foreach ($provinces as $province)<option value="{{ $province->id }}">{{ $province->name }}</option>@endforeach</select>
                @error('province_id')<small class="mt-1 block text-rose-600">{{ $message }}</small>@enderror</label>

            <label class="block"><span class="mb-1 block text-sm font-medium text-slate-700">District <span class="text-rose-600">*</span></span>
                <select wire:model="district_id" class="{{ $input }}" @disabled(!$province_id)><option value="">Select district</option>@foreach ($districts as $district)<option value="{{ $district->id }}">{{ $district->name }}</option>@endforeach</select>
                @error('district_id')<small class="mt-1 block text-rose-600">{{ $message }}</small>@enderror</label>

            <label class="block"><span class="mb-1 block text-sm font-medium text-slate-700">City <span class="text-rose-600">*</span></span>
                <input wire:model="city" class="{{ $input }}" placeholder="City">
                @error('city')<small class="mt-1 block text-rose-600">{{ $message }}</small>@enderror</label>

            <label class="block"><span class="mb-1 block text-sm font-medium text-slate-700">Tole / street <span class="text-rose-600">*</span></span>
                <input wire:model="tole" class="{{ $input }}" placeholder="Tole or street">
                @error('tole')<small class="mt-1 block text-rose-600">{{ $message }}</small>@enderror</label>

            <div class="md:col-span-2">
                <span class="mb-1 block text-sm font-medium text-slate-700">Address type <span class="text-rose-600">*</span></span>
                <div class="flex gap-4 text-sm">
                    <label class="flex items-center gap-2"><input type="radio" wire:model.live="address_type" value="home"> Home</label>
                    <label class="flex items-center gap-2"><input type="radio" wire:model.live="address_type" value="office"> Office</label>
                </div>
                @error('address_type')<small class="mt-1 block text-rose-600">{{ $message }}</small>@enderror
            </div>

            @if ($address_type === 'office')
                <label class="block"><span class="mb-1 block text-sm font-medium text-slate-700">Office opens <span class="text-rose-600">*</span></span>
                    <input wire:model="office_start_time" type="time" class="{{ $input }}">
                    @error('office_start_time')<small class="mt-1 block text-rose-600">{{ $message }}</small>@enderror</label>
                <label class="block"><span class="mb-1 block text-sm font-medium text-slate-700">Office closes <span class="text-rose-600">*</span></span>
                    <input wire:model="office_end_time" type="time" class="{{ $input }}">
                    @error('office_end_time')<small class="mt-1 block text-rose-600">{{ $message }}</small>@enderror</label>
            @endif

            @if ($addresses->isNotEmpty())
                <label class="flex items-center gap-2 text-sm md:col-span-2"><input type="checkbox" wire:model="make_default"> Make this my default address</label>
            @endif
        </form>

        <x-slot:actions>
            <x-button label="Cancel" icon="o-x-mark" wire:click="closeForm" />
            <x-button label="{{ $editingId ? 'Save changes' : 'Save address' }}" icon="o-check" wire:click="save" class="btn-primary" spinner="save" />
        </x-slot:actions>
    </x-modal>

    <x-modal wire:model="deleteModal" title="Delete address" separator>
        <p class="text-slate-600">Delete this address? Orders you already placed keep their delivery address.</p>
        <x-slot:actions>
            <x-button label="Cancel" icon="o-x-mark" wire:click="$set('deleteModal', false)" />
            <x-button label="Delete" icon="o-trash" wire:click="deleteAddress" class="btn-error" spinner="deleteAddress" />
        </x-slot:actions>
    </x-modal>
</div>
