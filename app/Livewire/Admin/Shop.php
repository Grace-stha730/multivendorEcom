<?php

namespace App\Livewire\Admin;

use App\Models\Shop as ShopModel;
use App\Models\ShopUser;
use App\Models\Province;
use App\Models\District;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
#[Title('Shops')]
class Shop extends Component
{
    use WithFileUploads;

    public $shopId, $name, $owner, $image, $contact_number, $pan_number, $province_id, $district_id, $city, $tole, $email, $phone, $status = 'active';
    public $shop_user_personal_email, $shop_user_address, $shop_user_password, $shop_user_contact, $shop_user_image, $shop_user_pan_number;
    public $existingShopImage;

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255', 'owner' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'contact_number' => 'required|string|max:30', 'pan_number' => 'nullable|string|max:50',
            'province_id' => 'required|exists:provinces,id', 'district_id' => 'required|exists:districts,id',
            'city' => 'required|string|max:255', 'tole' => 'required|string|max:255',
            'email' => 'required|email|max:255', 'phone' => 'required|string|max:30', 'status' => 'required|string|max:30',
        ];

        if (!$this->shopId) {
            $rules += [
                'shop_user_personal_email' => 'nullable|email|max:255',
                'shop_user_address' => 'required|string|max:255',
                'shop_user_password' => 'required|string|min:8',
                'shop_user_contact' => 'required|string|max:30',
                'shop_user_image' => 'nullable|image|max:2048',
                'shop_user_pan_number' => 'nullable|string|max:50',
            ];
        }

        return $rules;
    }

    public function save(): void
    {
        $validated = $this->validate();
        $shopData = Arr::only($validated, [
            'name', 'owner', 'contact_number', 'pan_number', 'province_id', 'district_id',
            'city', 'tole', 'email', 'phone', 'status',
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
                    'personal_email' => $this->shop_user_personal_email,
                    'address' => $this->shop_user_address,
                    'password' => Hash::make($this->shop_user_password),
                    'contact' => $this->shop_user_contact,
                    'image' => $this->shop_user_image?->store('shop-users', 'public'),
                    'pan_number' => $this->shop_user_pan_number,
                    'shop_id' => $shop->id,
                ]);
            });
        }

        $this->reset();
        $this->status = 'active';
        session()->flash('success', 'Shop saved successfully.');
    }

    public function updatedProvinceId(): void
    {
        $this->district_id = null;
    }

    public function edit(int $id): void
    {
        $shop = ShopModel::with(['province', 'district'])->findOrFail($id);
        $this->fill(Arr::except($shop->only(array_keys($this->rules())), ['image']) + ['shopId' => $shop->id]);
        $this->existingShopImage = $shop->image;

        $this->dispatch('shop-form-loaded',
            province: $shop->province ? ['id' => $shop->province->id, 'text' => $shop->province->name] : null,
            district: $shop->district ? ['id' => $shop->district->id, 'text' => $shop->district->name] : null,
        );
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
        return view('livewire.admin.shop', [
            'shops' => ShopModel::with(['province', 'district'])->latest()->get(),
            'provinces' => Province::orderBy('name')->get(),
            'districts' => $this->province_id
                ? District::where('province_id', $this->province_id)->orderBy('name')->get()
                : collect(),
        ]);
    }
}
