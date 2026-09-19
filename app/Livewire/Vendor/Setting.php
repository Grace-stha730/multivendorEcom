<?php

namespace App\Livewire\Vendor;

use App\Models\ShopUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
#[Title(content: 'Setting')]
class Setting extends Component
{
    use WithFileUploads;
    public $setting, $shop_name, $owner_name, $shop_province, $shop_city, $shop_tole, $shop_email, $oldImage, $shop_image, $shop_phone, $password, $newPassword;
    public bool $aiAutoReplyEnabled = false;

    public function mount()
    {
        $setting = ShopUser::find(Auth::guard('shop_user')->id());
        $this->setting = $setting;
        $this->shop_name = $setting->shop_name;
        $this->owner_name = $setting->owner_name;
        $this->shop_email = $setting->shop_email;
        $this->shop_province = $setting->shop_province;
        $this->shop_city = $setting->shop_city;
        $this->shop_tole = $setting->shop_tole;
        $this->shop_phone = $setting->shop_phone;
        $this->oldImage = $setting->shop_image;
        $this->aiAutoReplyEnabled = (bool) $setting->shop?->ai_auto_reply_enabled;

    }

    public function updateAiAutoReply(): void
    {
        $shop = Auth::guard('shop_user')->user()?->shop;
        if (! $shop) return;
        $shop->update(['ai_auto_reply_enabled' => $this->aiAutoReplyEnabled]);
        session()->flash('success', 'AI auto-reply setting updated.');
    }

    public function updateProfile()
    {
        $rule = [
            'shop_name' => 'required|min:2|max:25',
            'owner_name' => 'required|min:2|max:25',
            'shop_province' => 'nullable|min:2|max:25',
            'shop_city' => 'nullable|min:2|max:25',
            'shop_tole' => 'nullable|min:2|max:25',
            'shop_email' => [
                'email',
                Rule::unique('vendors', 'shop_email')->ignore($this->setting->id),
            ],
            'shop_image' => 'nullable|image',
            'shop_phone' => 'digits:10',
        ];
        if ($this->password) {
            $rule['password'] = 'nullable|min:5|max:20';
            $rule['newPassword'] = 'required|same:password';
        } else {
            $rule['newPassword'] = 'nullable';
        }
        $validation = $this->validate($rule);
        DB::beginTransaction();
        try {
            $setting = ShopUser::find(Auth::guard('shop_user')->id());
            if ($validation['shop_image']) {
                $validation['shop_image'] = $validation['shop_image']->store('vendors', 'public');
            } else {
                $validation['shop_image'] = $this->oldImage;
            }

            if($this->password){
                $validation['password'] = Hash::make($validation['password']);
            }

            $setting->update($validation);
            DB::commit();
            return redirect()->route('shop-user.setting')->with('success','Profile update successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('shop-user.setting')->with('error', 'Something went wrong' . $e->getMessage());
        }
    }

    public function render(
    ) {
        return view('livewire.vendor.setting');
    }
}
