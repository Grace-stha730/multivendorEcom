<?php

namespace App\Livewire\Vendor;

use App\Livewire\Concerns\AuthorizesPermissions;
use App\Livewire\Concerns\ChangesPassword;
use App\Livewire\Concerns\StoresImages;
use App\Models\District;
use App\Models\Province;
use App\Models\Shop;
use App\Models\ShopUser;
use App\Rules\PhoneNumber;
use App\Support\ImageUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

/**
 * Shop panel settings. "My profile" is the logged-in staff account (shop_users); "Shop details" and
 * "AI assistant" belong to the SHOP (shops) and need the shop-edit-settings permission to change.
 */
#[Layout('components.layouts.app')]
#[Title('Settings')]
class Setting extends Component
{
    use AuthorizesPermissions, ChangesPassword, StoresImages, Toast, WithFileUploads;

    private const TABS = [
        'profile' => ['label' => 'My profile', 'icon' => 'fa-user'],
        'shop' => ['label' => 'Shop details', 'icon' => 'fa-store'],
        'ai' => ['label' => 'AI assistant', 'icon' => 'fa-robot'],
        'security' => ['label' => 'Password & security', 'icon' => 'fa-lock'],
    ];

    #[Url(as: 'tab')]
    public string $tab = 'profile';

    // My profile (shop_users)
    public string $name = '';
    public string $personal_email = '';
    public string $contact = '';
    public string $address = '';
    public $photo = null;
    public ?string $currentPhoto = null;

    // Shop details (shops)
    public string $shop_name = '';
    public string $shop_owner = '';
    public string $shop_email = '';
    public string $shop_contact = '';
    public string $shop_city = '';
    public string $shop_tole = '';
    public $shop_province_id = null;
    public $shop_district_id = null;
    public $shop_logo = null;
    public ?string $currentLogo = null;

    public bool $aiAutoReplyEnabled = false;

    public function mount(): void
    {
        $me = Auth::guard('shop_user')->user();
        $this->name = $me->name;
        $this->personal_email = (string) $me->personal_email;
        $this->contact = (string) $me->contact;
        $this->address = (string) $me->address;
        $this->currentPhoto = $me->image;

        $shop = $me->shop;
        $this->shop_name = (string) $shop?->name;
        $this->shop_owner = (string) $shop?->owner;
        $this->shop_email = (string) $shop?->email;
        $this->shop_contact = (string) $shop?->contact_number;
        $this->shop_city = (string) $shop?->city;
        $this->shop_tole = (string) $shop?->tole;
        $this->shop_province_id = $shop?->province_id;
        $this->shop_district_id = $shop?->district_id;
        $this->currentLogo = $shop?->image;
        $this->aiAutoReplyEnabled = (bool) $shop?->ai_auto_reply_enabled;
    }

    public function updatedShopProvinceId(): void
    {
        $this->shop_district_id = null;
    }

    public function updateProfile(): void
    {
        $me = ShopUser::findOrFail(Auth::guard('shop_user')->id());

        $this->validate([
            'name' => 'required|string|min:2|max:255',
            'personal_email' => ['nullable', 'email', 'max:255', function (string $attribute, mixed $value, \Closure $fail) use ($me) {
                $email = strtolower(trim((string) $value));
                if ($email === '') {
                    return;
                }
                if (ShopUser::whereRaw('lower(personal_email) = ?', [$email])->where('id', '!=', $me->id)->exists()) {
                    $fail('This email is already used by another account.');
                }
                // Password reset looks a vendor up by personal email first, then by the shop email (-> the owner).
                if (Shop::whereRaw('lower(email) = ?', [$email])->exists()) {
                    $fail('This email belongs to a shop. Use your own email.');
                }
            }],
            'contact' => ['nullable', new PhoneNumber()],
            'address' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:2048',
        ]);

        $me->update([
            'name' => trim($this->name),
            'personal_email' => strtolower(trim($this->personal_email)) ?: null,
            'contact' => $this->contact ?: null,
            'address' => trim($this->address) ?: null,
            'image' => $this->replaceImage($this->photo, 'shop-users', $me->image),
        ]);

        $this->currentPhoto = $me->fresh()->image;
        $this->reset('photo');
        $this->success('Profile updated', 'Your changes were saved.', 'toast-bottom toast-end');
    }

    public function updateShop(): void
    {
        $this->authorizeShop('shop-edit-settings');
        $shop = $this->ownShop();

        $this->validate([
            'shop_name' => 'required|string|max:255',
            'shop_owner' => 'required|string|max:255',
            'shop_email' => ['required', 'email', 'max:255', function (string $attribute, mixed $value, \Closure $fail) use ($shop) {
                $email = strtolower(trim((string) $value));
                if (Shop::whereRaw('lower(email) = ?', [$email])->where('id', '!=', $shop->id)->exists()) {
                    $fail('Another shop already uses this email.');
                }
                // A staff member's personal email must never equal a shop email (it would break password reset).
                if (ShopUser::whereRaw('lower(personal_email) = ?', [$email])->exists()) {
                    $fail('This email is already used by a staff account.');
                }
            }],
            'shop_contact' => ['required', new PhoneNumber()],
            'shop_province_id' => 'required|exists:provinces,id',
            'shop_district_id' => ['required', Rule::exists('districts', 'id')->where('province_id', $this->shop_province_id)],
            'shop_city' => 'required|string|max:255',
            'shop_tole' => 'required|string|max:255',
            'shop_logo' => 'nullable|image|max:2048',
        ], [
            'shop_province_id.required' => 'Please select a province.',
            'shop_district_id.required' => 'Please select a district.',
        ]);

        // PAN and status are not editable here: they identify the shop and are managed by the platform admins.
        $shop->update([
            'name' => trim($this->shop_name),
            'owner' => trim($this->shop_owner),
            'email' => strtolower(trim($this->shop_email)),
            'contact_number' => $this->shop_contact,
            'province_id' => $this->shop_province_id,
            'district_id' => $this->shop_district_id,
            'city' => trim($this->shop_city),
            'tole' => trim($this->shop_tole),
            'image' => $this->replaceImage($this->shop_logo, 'shops', $shop->image),
        ]);

        $this->currentLogo = $shop->fresh()->image;
        $this->reset('shop_logo');
        $this->success('Shop details updated', 'Your changes were saved.', 'toast-bottom toast-end');
    }

    public function updateAiAutoReply(): void
    {
        $this->authorizeShop('shop-edit-settings');
        $this->ownShop()->update(['ai_auto_reply_enabled' => $this->aiAutoReplyEnabled]);
        $this->success('AI assistant updated', $this->aiAutoReplyEnabled ? 'Auto-replies are on.' : 'Auto-replies are off.', 'toast-bottom toast-end');
    }

    protected function passwordOwner(): Model
    {
        return ShopUser::findOrFail(Auth::guard('shop_user')->id());
    }

    /** Always the logged-in user's own shop. Never an id from the browser. */
    private function ownShop(): Shop
    {
        return Shop::findOrFail(Auth::guard('shop_user')->user()->shop_id);
    }

    public function render()
    {
        if (!isset(self::TABS[$this->tab])) {
            $this->tab = 'profile';
        }

        $me = Auth::guard('shop_user')->user();
        $shop = $me->shop;

        return view('livewire.vendor.setting', [
            'tabs' => self::TABS,
            'me' => $me,
            'shop' => $shop,
            'canEditShop' => authorizeUserCheck('shop-edit-settings', 'shop_user'),
            'roles' => $me->getRoleNames(),
            'photoUrl' => ImageUrl::for($this->currentPhoto),
            'photoPreview' => $this->previewUrl($this->photo),
            'logoUrl' => ImageUrl::for($this->currentLogo),
            'logoPreview' => $this->previewUrl($this->shop_logo),
            'provinces' => $this->tab === 'shop' ? Province::orderBy('name')->get() : collect(),
            'districts' => $this->tab === 'shop' && $this->shop_province_id
                ? District::where('province_id', $this->shop_province_id)->orderBy('name')->get()
                : collect(),
        ]);
    }
}
