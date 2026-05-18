<?php

namespace App\Livewire\Vendor;

use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;

#[Title(content: 'Setting')]
class Setting extends Component
{
    use WithFileUploads;
    public $setting, $shop_name, $owner_name, $shop_province, $shop_city, $shop_tole, $shop_email, $oldImage, $shop_image, $shop_phone, $password, $newPassword;

    public function mount()
    {
        $setting = Vendor::find(Auth::guard('vendor')->user()->id);
        $this->setting = $setting;
        $this->shop_name = $setting->shop_name;
        $this->owner_name = $setting->owner_name;
        $this->shop_email = $setting->shop_email;
        $this->shop_province = $setting->shop_province;
        $this->shop_city = $setting->shop_city;
        $this->shop_tole = $setting->shop_tole;
        $this->shop_phone = $setting->shop_phone;
        $this->oldImage = $setting->shop_image;

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
            $setting = Vendor::find(Auth::guard('vendor')->user()->id);
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
            return redirect()->route('vendor.setting')->with('success','Profile update successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('vendor.setting')->with('error', 'Something went wrong' . $e->getMessage());
        }
    }

    public function render(
    ) {
        return view('livewire.vendor.setting');
    }
}
