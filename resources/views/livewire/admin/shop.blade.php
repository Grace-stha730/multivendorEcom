<section class="max-w-7xl mx-auto space-y-7">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold tracking-[0.16em] text-emerald-700 uppercase">Marketplace management</p>
            <h1 class="mt-1 text-3xl font-bold text-slate-900">Shops</h1>
            <p class="mt-1 text-sm text-slate-500">Create a shop and set its address before assigning shop users.</p>
        </div>
        <div class="rounded-lg bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-800">
            {{ $shops->count() }} {{ Str::plural('shop', $shops->count()) }} registered
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-slate-50 px-6 py-4">
            <h2 class="font-semibold text-slate-800">{{ $shopId ? 'Edit shop' : 'New shop' }}</h2>
        </div>
        <form wire:submit="save" class="grid gap-x-6 gap-y-5 p-6 md:grid-cols-2 xl:grid-cols-3" x-data>
            <label class="block"><span class="mb-1.5 block text-sm font-medium text-slate-700">Shop name <span
                        class="text-rose-600">*</span></span><input wire:model.live.debounce.300ms="name" class="form-input"
                                                                    placeholder="e.g. Himalayan Mart">@error('name')
                <small class="form-error">{{ $message }}</small> @enderror</label>
            <label class="block"><span class="mb-1.5 block text-sm font-medium text-slate-700">Owner <span
                        class="text-rose-600">*</span></span><input wire:model.live.debounce.300ms="owner" class="form-input"
                                                                    placeholder="Owner's full name">@error('owner')
                <small class="form-error">{{ $message }}</small> @enderror</label>
            <label class="block"><span class="mb-1.5 block text-sm font-medium text-slate-700">Primary contact <span
                        class="text-rose-600">*</span></span><input wire:model="contact_number" class="form-input"
                                                                    placeholder="98XXXXXXXX">@error('contact_number')
                <small class="form-error">{{ $message }}</small> @enderror</label>
            <label class="block"><span class="mb-1.5 block text-sm font-medium text-slate-700">PAN number</span><input
                    wire:model="pan_number" class="form-input" placeholder="Optional PAN number">@error('pan_number')
                <small class="form-error">{{ $message }}</small> @enderror</label>
            <div class="block">
                <span class="mb-1.5 block text-sm font-medium text-slate-700">Shop image</span>
                <div class="flex items-center gap-3">
                    <div class="h-16 w-16 shrink-0 overflow-hidden rounded-lg bg-slate-100">
                        @if ($image)
                            <img src="{{ $image->temporaryUrl() }}" class="h-full w-full object-cover" alt="Shop image preview">
                        @elseif ($existingShopImage)
                            <img src="{{ asset('storage/' . $existingShopImage) }}" class="h-full w-full object-cover" alt="Current shop image">
                        @else
                            <img src="{{ asset('default/vendor.svg') }}" class="h-full w-full object-cover" alt="Shop image placeholder">
                        @endif
                    </div>
                    <input wire:model="image" type="file" accept="image/*" class="block w-full text-sm text-slate-600">
                </div>
                @error('image') <small class="form-error">{{ $message }}</small> @enderror
            </div>
            <label class="block"><span class="mb-1.5 block text-sm font-medium text-slate-700">Province <span
                        class="text-rose-600">*</span></span><div wire:ignore>
                    <select class="province-select form-input"
                            @change.prevent="$store.adminShopSetup.updateSelectedData('province_id', $event.target.value)"
                            x-bind:id="'province_id'" x-bind:data-row-index="'province_id'"
                            tabindex="-1">
                        <option value="">Select province</option>
                        <template x-for="listItem in $store.adminShopSetup.provinces.map(listItem => JSON.parse(JSON.stringify(listItem)))" :key="listItem.id">
                            <option :value="listItem.id" x-text="listItem.text"
                                    :selected="listItem.id === $store.adminShopSetup?.shopData?.province_id || null"></option>
                        </template>
                    </select>
                </div>@error('province_id') <small class="form-error">{{ $message }}</small> @enderror
            </label>
            <label class="block"><span class="mb-1.5 block text-sm font-medium text-slate-700">District <span
                        class="text-rose-600">*</span></span><div wire:ignore>
                    <select class="district-select form-input"
                            @change.prevent="$store.adminShopSetup.updateSelectedData('district_id', $event.target.value)"
                            x-bind:id="'district_id'" x-bind:data-row-index="'district_id'"
                            tabindex="-1">
                        <option value="">Select district</option>
                        <template x-for="listItem in $store.adminShopSetup.districts.map(listItem => JSON.parse(JSON.stringify(listItem)))" :key="listItem.id">
                            <option :value="listItem.id" x-text="listItem.text"
                                    :selected="listItem.id === $store.adminShopSetup?.shopData?.district_id || null"></option>
                        </template>
                    </select>
                </div>@error('district_id') <small class="form-error">{{ $message }}</small> @enderror
            </label>
            <label class="block"><span class="mb-1.5 block text-sm font-medium text-slate-700">City <span
                        class="text-rose-600">*</span></span><input wire:model="city" class="form-input"
                                                                    placeholder="City">@error('city') <small
                    class="form-error">{{ $message }}</small> @enderror</label>
            <label class="block"><span class="mb-1.5 block text-sm font-medium text-slate-700">Tole / street <span
                        class="text-rose-600">*</span></span><input wire:model="tole" class="form-input"
                                                                    placeholder="Tole or street">@error('tole') <small
                    class="form-error">{{ $message }}</small> @enderror</label>
            <label class="block"><span class="mb-1.5 block text-sm font-medium text-slate-700">Email <span
                        class="text-rose-600">*</span></span><input wire:model="email" type="email" class="form-input"
                                                                    placeholder="shop@example.com">@error('email')<small
                    class="form-error">{{ $message }}</small> @enderror</label>
            <label class="block"><span class="mb-1.5 block text-sm font-medium text-slate-700">Phone <span
                        class="text-rose-600">*</span></span><input wire:model="phone" class="form-input"
                                                                    placeholder="Phone number">@error('phone') <small
                    class="form-error">{{ $message }}</small> @enderror</label>
            <label class="block"><span class="mb-1.5 block text-sm font-medium text-slate-700">Status <span
                        class="text-rose-600">*</span></span><select wire:model="status" class="form-input">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                </select></label>
            @if (!$shopId)
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/40 p-5 md:col-span-2 xl:col-span-3">
                    <div class="mb-4">
                        <h3 class="font-semibold text-slate-800">Shop User</h3>
                        <p class="mt-1 text-xs text-slate-500">A login account is created automatically with this shop.</p>
                    </div>
                    <div class="grid gap-x-6 gap-y-5 md:grid-cols-2 xl:grid-cols-3">
                        <label class="block"><span class="mb-1.5 block text-sm font-medium text-slate-700">Name</span>
                            <input value="{{ $owner }}" readonly class="form-input bg-slate-100!" placeholder="Copied from owner">
                        </label>
                        <label class="block"><span class="mb-1.5 block text-sm font-medium text-slate-700">Username</span>
                            <input value="{{ $this->generatedUsernamePreview }}" readonly class="form-input bg-slate-100! text-slate-500">
                            <small class="mt-1 block text-slate-500">Generated and checked for uniqueness when saved.</small>
                        </label>
                        <label class="block"><span class="mb-1.5 block text-sm font-medium text-slate-700">Personal email</span>
                            <input wire:model="shop_user_personal_email" type="email" class="form-input" placeholder="Optional personal email">
                            @error('shop_user_personal_email') <small class="form-error">{{ $message }}</small> @enderror
                        </label>
                        <label class="block"><span class="mb-1.5 block text-sm font-medium text-slate-700">Address <span class="text-rose-600">*</span></span>
                            <input wire:model="shop_user_address" class="form-input" placeholder="Home address">
                            @error('shop_user_address') <small class="form-error">{{ $message }}</small> @enderror
                        </label>
                        <label class="block"><span class="mb-1.5 block text-sm font-medium text-slate-700">Password <span class="text-rose-600">*</span></span>
                            <input wire:model="shop_user_password" type="password" class="form-input" placeholder="Minimum 8 characters">
                            @error('shop_user_password') <small class="form-error">{{ $message }}</small> @enderror
                        </label>
                        <label class="block"><span class="mb-1.5 block text-sm font-medium text-slate-700">Contact <span class="text-rose-600">*</span></span>
                            <input wire:model="shop_user_contact" class="form-input" placeholder="98XXXXXXXX">
                            @error('shop_user_contact') <small class="form-error">{{ $message }}</small> @enderror
                        </label>
                        <label class="block"><span class="mb-1.5 block text-sm font-medium text-slate-700">PAN number</span>
                            <input wire:model="shop_user_pan_number" class="form-input" placeholder="Optional PAN number">
                            @error('shop_user_pan_number') <small class="form-error">{{ $message }}</small> @enderror
                        </label>
                        <div class="block md:col-span-2">
                            <span class="mb-1.5 block text-sm font-medium text-slate-700">Shop user image</span>
                            <div class="flex items-center gap-3">
                                <div class="h-16 w-16 shrink-0 overflow-hidden rounded-lg bg-slate-100">
                                    @if ($shop_user_image)
                                        <img src="{{ $shop_user_image->temporaryUrl() }}" class="h-full w-full object-cover" alt="Shop user image preview">
                                    @else
                                        <img src="{{ asset('default/vendor.svg') }}" class="h-full w-full object-cover" alt="Shop user image placeholder">
                                    @endif
                                </div>
                                <input wire:model="shop_user_image" type="file" accept="image/*" class="block w-full text-sm text-slate-600">
                            </div>
                            @error('shop_user_image') <small class="form-error">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>
            @endif
            <div class="flex items-end">
                <button
                    class="w-full rounded-lg bg-slate-900 px-4 py-2.5 font-semibold text-white transition hover:bg-slate-700 disabled:opacity-60"
                    wire:loading.attr="disabled"><span
                        wire:loading.remove>{{ $shopId ? 'Save changes' : 'Create shop' }}</span><span wire:loading>Saving…</span>
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-6 py-4"><h2 class="font-semibold text-slate-800">Registered shops</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-6 py-3">Shop</th>
                    <th class="px-6 py-3">Owner</th>
                    <th class="px-6 py-3">Location</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3"></th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">@forelse($shops as $shop)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4 font-medium text-slate-900">
                            <div>{{ $shop->name }}</div>
                            <small class="font-normal text-slate-500">{{ $shop->email }}</small></td>
                        <td class="px-6 py-4 text-slate-700">{{ $shop->owner }}<br><small
                                class="text-slate-500">{{ $shop->contact_number }}</small></td>
                        <td class="px-6 py-4 text-slate-600">{{ $shop->district?->name }}, {{ $shop->province?->name }}
                            <br><small>{{ $shop->city }}, {{ $shop->tole }}</small></td>
                        <td class="px-6 py-4"><span
                                class="rounded-full px-2.5 py-1 text-xs font-medium {{ $shop->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">{{ ucfirst($shop->status) }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button wire:click="edit({{ $shop->id }})"
                                    class="font-medium text-emerald-700 hover:text-emerald-900">Edit
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-500">No shops have been created yet.
                        </td>
                    </tr>
                @endforelse</tbody>
            </table>
        </div>
    </div>
</section>

@script
<script>
    Alpine.store('adminShopSetup', {
        shopData: @json([
            'province_id' => $province_id,
            'district_id' => $district_id,
        ]),
        provinces: @json($provinces->map(fn ($province) => ['id' => $province->id, 'text' => $province->name])->values()),
        districts: @json($districts->map(fn ($district) => ['id' => $district->id, 'text' => $district->name])->values()),
        select2Instances: [],

        init() {
            Alpine.nextTick(() => this.initializeSelect2());

            window.addEventListener('shop-form-loaded', (event) => {
                this.loadShopLocation(event.detail);
            });
        },

        initializeSelect2() {
            Alpine.nextTick(() => {
                this.select2Configs = [
                    {
                        selector: '.province-select',
                        key: 'province_id',
                        placeholder: 'Select province',
                        module_type: 'get_province',
                    },
                    {
                        selector: '.district-select',
                        key: 'district_id',
                        placeholder: 'Select district',
                        module_type: 'get_district',
                    },
                ];

                this.select2Configs.forEach(config => this.initSelect2Element(config));
            });
        },

        initSelect2Element(config) {
            const element = $(config.selector);

            if (element.data('select2')) {
                element.select2('destroy');
            }

            element.trigger('blur');

            const url = `{{ route('searchSelect2', ['module' => 'MODULE']) }}`
                .replace('MODULE', config.module_type);

            element.select2({
                placeholder: config.placeholder,
                allowClear: true,
                // Keep the absolutely positioned results inside the form field
                // instead of appending them to body, which causes page overflow.
                dropdownParent: element.closest('.modal').length ? element.closest('.modal') : element.parent(),
                ajax: {
                    url: url,
                    delay: 250,
                    data: params => ({
                        term: params.term,
                        selected_id: this.shopData.province_id || null,
                    }),
                    // The endpoint may return either the Select2 envelope
                    // ({ results: [...] }) or the results array itself.
                    processResults: data => ({
                        results: Array.isArray(data) ? data : (data.results || []),
                    }),
                },
            })
                .on('select2:select', (event) => this.updateSelectedData(config.key, event.params.data.id))
                .on('select2:clear', () => this.updateSelectedData(config.key, null));
        },

        updateSelectedData(key, value) {
            this.shopData[key] = value || null;

            if (key === 'province_id') {
                this.shopData.district_id = null;
                $('.district-select').val(null).trigger('change.select2');
                $wire.set('district_id', null);
            }

            $wire.set(key, this.shopData[key]);
        },

        loadShopLocation({ province, district }) {
            this.shopData.province_id = province?.id || null;
            this.shopData.district_id = district?.id || null;

            this.setSelect2Value('.province-select', province);
            this.setSelect2Value('.district-select', district);
        },

        setSelect2Value(selector, option) {
            const element = $(selector);

            if (option && !element.find(`option[value="${option.id}"]`).length) {
                element.append(new Option(option.text, option.id, true, true));
            }

            element.val(option?.id || null).trigger('change.select2');
        },
    });
</script>
@endscript

<style>
    .form-input {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: .5rem;
        background: #fff;
        padding: .625rem .75rem;
        color: #0f172a;
        outline: none;
    }

    .form-input:focus {
        border-color: #059669;
        box-shadow: 0 0 0 3px rgb(16 185 129 / .12);
    }

    .form-error {
        display: block;
        margin-top: .35rem;
        color: #e11d48;
    }

    /* Match Select2's generated control to the regular form inputs. */
    .select2-container {
        width: 100% !important;
    }

    .select2-container--default .select2-selection--single {
        position: relative;
        display: flex;
        align-items: center;
        height: 42px;
        border: 1px solid #cbd5e1;
        border-radius: .5rem;
        background: #fff;
        padding: 0 .75rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        width: 100%;
        padding: 0 2rem 0 0;
        color: #0f172a;
        line-height: normal;
    }

    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #94a3b8;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        display: none;
    }

    .select2-container--default .select2-selection--single .select2-selection__clear {
        position: absolute;
        top: 50%;
        right: .75rem;
        z-index: 1;
        margin: 0;
        color: #64748b;
        font-size: 1.25rem;
        line-height: 1;
        transform: translateY(-50%);
    }

    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #059669;
        box-shadow: 0 0 0 3px rgb(16 185 129 / .12);
    }

    .select2-dropdown {
        overflow: hidden;
        border: 1px solid #cbd5e1;
        border-radius: .5rem;
        box-shadow: 0 12px 24px rgb(15 23 42 / .12);
    }

    .select2-results__options {
        max-height: 280px !important;
    }

    .select2-container--open {
        z-index: 50;
    }

    /* Select2 measures its dropdown outside the normal grid flow. */
    html:has(.province-select),
    body:has(.province-select) {
        max-width: 100%;
        overflow-x: hidden;
    }

    .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background: #059669;
    }
</style>
