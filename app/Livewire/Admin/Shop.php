<?php

namespace App\Livewire\Admin;

use App\Models\Shop as ShopModel;
use App\Models\ShopUser;
use App\Rules\PhoneNumber;
use App\Models\Province;
use App\Models\District;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

#[Layout('components.layouts.admin')]
#[Title('Shops')]
class Shop extends Component
{
    use \App\Livewire\Concerns\AuthorizesPermissions;
    use \App\Livewire\Concerns\PaginatesList;
    use WithFileUploads;
    use Toast;

    public $shopId, $name, $owner, $image, $contact_number, $pan_number, $province_id, $district_id, $city, $tole, $email, $status = 'active';
    public $shop_user_password;
    public $existingShopImage;
    public bool $shopModal = false;
    public bool $deleteModal = false;
    public ?int $shopToDelete = null;

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255', 'owner' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'contact_number' => ['required', new PhoneNumber()], 'pan_number' => 'nullable|string|max:50',
            'province_id' => 'required|exists:provinces,id', 'district_id' => 'required|exists:districts,id',
            'city' => 'required|string|max:255', 'tole' => 'required|string|max:255',
            'email' => 'required|email|max:255', 'status' => 'required|string|max:30',
        ];

        if (!$this->shopId) {
            $rules += [
                'shop_user_password' => 'required|string|min:8',
            ];
        }

        return $rules;
    }

    public function save(): void
    {
        $this->authorizeAdmin($this->shopId ? 'shop-edit' : 'shop-create');
        $validated = $this->validate();
        $isEditing = (bool) $this->shopId;
        $shopData = Arr::only($validated, [
            'name', 'owner', 'contact_number', 'pan_number', 'province_id', 'district_id',
            'city', 'tole', 'email', 'status',
        ]);

        if ($this->image) {
            $shopData['image'] = $this->image->store('shops', 'public');
        }

        if ($this->shopId) {
            ShopModel::findOrFail($this->shopId)->update($shopData);
        } else {
            DB::transaction(function () use ($shopData): void {
                $shop = ShopModel::create($shopData);

                ShopUser::create([
                    'name' => $shop->owner,
                    'username' => $this->uniqueUsername($shop->owner, $shop->name),
                    'password' => Hash::make($this->shop_user_password),
                    'shop_id' => $shop->id,
                ]);
            });
        }

        $this->resetForm();
        $this->shopModal = false;
        $this->success(
            $isEditing ? 'Shop updated' : 'Shop created',
            $isEditing ? 'The shop changes have been saved.' : 'The shop and its user account have been created.', 'toast-bottom'
        );
    }

    public function updatedProvinceId(): void
    {
        $this->district_id = null;
    }

    public function edit(int $id): void
    {
        $this->authorizeAdmin('shop-edit');
        $shop = ShopModel::with(['province', 'district'])->findOrFail($id);
        $this->resetValidation();
        $this->fill(Arr::except($shop->only(array_keys($this->rules())), ['image']));
        $this->shopId = $shop->id;
        $this->existingShopImage = $shop->image;
        $this->shopModal = true;

        $this->dispatch('shop-form-loaded',
            province: $shop->province ? ['id' => $shop->province->id, 'text' => $shop->province->name] : null,
            district: $shop->district ? ['id' => $shop->district->id, 'text' => $shop->district->name] : null,
        );
    }

    public function create(): void
    {
        $this->authorizeAdmin('shop-create');
        $this->resetForm();
        $this->shopModal = true;
        $this->dispatch('shop-form-loaded', province: null, district: null);
    }

    public function confirmDelete(int $id): void
    {
        $this->authorizeAdmin('shop-delete');
        $this->shopToDelete = $id;
        $this->deleteModal = true;
    }

    public function deleteShop(): void
    {
        $this->authorizeAdmin('shop-delete');
        ShopModel::findOrFail($this->shopToDelete)->delete();

        $this->deleteModal = false;
        $this->shopToDelete = null;
        $this->success('Shop deleted', 'The shop and its related records have been deleted.', 'toast-bottom');
    }

    public function closeShopModal(): void
    {
        $this->shopModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'shopId', 'name', 'owner', 'image', 'contact_number', 'pan_number',
            'province_id', 'district_id', 'city', 'tole', 'email', 'shop_user_password',
            'existingShopImage',
        ]);
        $this->status = 'active';
        $this->resetValidation();
    }

    public function getGeneratedUsernamePreviewProperty(): string
    {
        if (!$this->owner || !$this->name) {
            return 'Generated after shop details are entered';
        }

        return $this->uniqueUsername($this->owner, $this->name);
    }

    private function uniqueUsername(string $owner, string $shopName): string
    {
        $ownerWords = preg_split('/\s+/', trim(mb_strtolower($owner)), -1, PREG_SPLIT_NO_EMPTY);
        $ownerPart = count($ownerWords) <= 2
            ? implode('.', $ownerWords)
            : $ownerWords[0] . '.' . $ownerWords[array_key_last($ownerWords)];

        $ownerPart = preg_replace('/[^\pL\pN.]/u', '', $ownerPart);
        $shopTokens = preg_split('/[\s.]+/u', trim(mb_strtolower($shopName)), -1, PREG_SPLIT_NO_EMPTY);
        $filteredShopTokens = [];
        $brandNameStarted = false;

        foreach ($shopTokens as $token) {
            $isLegalToken = in_array($token, ['pvt', 'ltd'], true);

            // Leading Pvt/Ltd tokens are part of the brand name. Once a
            // non-legal name token appears, later Pvt/Ltd tokens are suffixes.
            if ($isLegalToken && $brandNameStarted) {
                continue;
            }

            $filteredShopTokens[] = $token;
            $brandNameStarted = !$isLegalToken;
        }

        $shopTokens = $filteredShopTokens;
        $shopPart = implode('', array_map(
            fn (string $token) => preg_replace('/[^\pL\pN]/u', '', $token),
            $shopTokens,
        ));
        $shopPart = $shopPart ?: 'shop';
        $ownerPart = $ownerPart ?: 'owner';
        $domain = $shopPart . '.com';

        $suffix = 0;
        do {
            $username = $ownerPart . ($suffix ?: '') . '@' . $domain;
            $suffix++;
        } while (ShopUser::where('username', $username)->exists());

        return $username;
    }

    public function render()
    {
        $shops = ShopModel::with(['province', 'district'])->latest()->paginate(10);
        $shops->getCollection()->each(function (ShopModel $shop): void {
            $shop->location = collect([$shop->district?->name, $shop->province?->name])->filter()->join(', ');
        });

        return view('livewire.admin.shop', [
            'shops' => $shops,
            'headers' => [
                ['key' => 'name', 'label' => 'Shop'],
                ['key' => 'owner', 'label' => 'Owner'],
                ['key' => 'location', 'label' => 'Location'],
                ['key' => 'status', 'label' => 'Status'],
                ['key' => 'actions', 'label' => 'Actions', 'class' => 'w-28 text-right'],
            ],
            'provinces' => Province::orderBy('name')->get(),
            'districts' => $this->province_id
                ? District::where('province_id', $this->province_id)->orderBy('name')->get()
                : collect(),
        ]);
    }
}
