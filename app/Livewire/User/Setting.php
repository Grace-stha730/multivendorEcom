<?php

namespace App\Livewire\User;

use App\Livewire\Concerns\ChangesPassword;
use App\Livewire\Concerns\StoresImages;
use App\Models\User;
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

#[Title('Settings')]
#[Layout('components.layouts.user')]
class Setting extends Component
{
    use ChangesPassword, StoresImages, Toast, WithFileUploads;

    private const TABS = [
        'profile' => ['label' => 'Profile', 'icon' => 'fa-user'],
        'addresses' => ['label' => 'Addresses', 'icon' => 'fa-location-dot'],
        'security' => ['label' => 'Password & security', 'icon' => 'fa-lock'],
    ];

    #[Url(as: 'tab')]
    public string $tab = 'profile';

    public string $name = '';
    public string $email = '';
    public $photo = null;
    public ?string $currentPhoto = null;

    public function mount(): void
    {
        $user = Auth::guard('web')->user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->currentPhoto = $user->photo;
    }

    public function updateProfile(): void
    {
        $user = User::findOrFail(Auth::guard('web')->id());

        $this->validate([
            'name' => 'required|string|min:2|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'photo' => 'nullable|image|max:2048',
        ]);

        $user->update([
            'name' => trim($this->name),
            'email' => strtolower(trim($this->email)),
            'photo' => $this->replaceImage($this->photo, 'users', $user->photo),
        ]);

        $this->currentPhoto = $user->fresh()->photo;
        $this->reset('photo');
        $this->success('Profile updated', 'Your changes were saved.', 'toast-bottom toast-end');
    }

    protected function passwordOwner(): Model
    {
        return User::findOrFail(Auth::guard('web')->id());
    }

    public function render()
    {
        if (!isset(self::TABS[$this->tab])) {
            $this->tab = 'profile';
        }

        return view('livewire.user.setting', [
            'tabs' => self::TABS,
            'photoUrl' => ImageUrl::for($this->currentPhoto),
            'photoPreview' => $this->previewUrl($this->photo),
            'initials' => strtoupper(mb_substr($this->name ?: 'U', 0, 1)),
        ]);
    }
}
