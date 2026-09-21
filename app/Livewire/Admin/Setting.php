<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\ChangesPassword;
use App\Livewire\Concerns\StoresImages;
use App\Models\Admin;
use App\Rules\PhoneNumber;
use App\Support\ImageUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

#[Layout('components.layouts.admin')]
#[Title('Settings')]
class Setting extends Component
{
    use ChangesPassword, StoresImages, Toast, WithFileUploads;

    private const TABS = [
        'profile' => ['label' => 'Profile', 'icon' => 'fa-user'],
        'security' => ['label' => 'Password & security', 'icon' => 'fa-lock'],
        'access' => ['label' => 'Your access', 'icon' => 'fa-shield-halved'],
    ];

    #[Url(as: 'tab')]
    public string $tab = 'profile';

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public $image = null;
    public ?string $currentImage = null;

    public function mount(): void
    {
        $admin = Auth::guard('admin')->user();
        $this->name = $admin->name;
        $this->email = $admin->email;
        $this->phone = (string) $admin->phone;
        $this->address = (string) $admin->address;
        $this->currentImage = $admin->image;
    }

    public function updateProfile(): void
    {
        $admin = Admin::findOrFail(Auth::guard('admin')->id());

        $this->validate([
            'name' => 'required|string|min:2|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($admin->id)],
            'phone' => ['nullable', new PhoneNumber()],
            'address' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
        ]);

        // Only these fields. The legacy role/department columns are not editable here.
        $admin->update([
            'name' => trim($this->name),
            'email' => strtolower(trim($this->email)),
            'phone' => $this->phone ?: null,
            'address' => trim($this->address) ?: null,
            'image' => $this->replaceImage($this->image, 'admins', $admin->image),
        ]);

        $this->currentImage = $admin->fresh()->image;
        $this->reset('image');
        $this->success('Profile updated', 'Your changes were saved.', 'toast-bottom toast-end');
    }

    protected function passwordOwner(): Model
    {
        return Admin::findOrFail(Auth::guard('admin')->id());
    }

    public function render()
    {
        if (!isset(self::TABS[$this->tab])) {
            $this->tab = 'profile';
        }

        $admin = Auth::guard('admin')->user();

        return view('livewire.admin.setting', [
            'tabs' => self::TABS,
            'imageUrl' => ImageUrl::for($this->currentImage),
            'imagePreview' => $this->previewUrl($this->image),
            'initials' => strtoupper(mb_substr($this->name ?: 'A', 0, 1)),
            'roles' => $this->tab === 'profile' || $this->tab === 'access' ? $admin->getRoleNames() : collect(),
            // "shop-view", "shop-edit" ... grouped by what they are about ("shop", "order" ...).
            'permissionGroups' => $this->tab === 'access'
                ? $admin->getAllPermissions()->pluck('name')->sort()->groupBy(fn (string $p) => Str::before($p, '-'))
                : collect(),
        ]);
    }
}
