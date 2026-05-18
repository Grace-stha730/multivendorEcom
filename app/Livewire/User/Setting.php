<?php

namespace App\Livewire\User;

use App\Models\User;
use Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;

#[Title(content: 'Setting')]
#[Layout('components/layouts/user')]
class Setting extends Component
{
    use WithFileUploads;
    public $setting, $name, $email, $province, $city, $tole, $photo, $oldPhoto, $phone, $password, $newPassword;

    public function mount()
    {
        $setting = User::find(Auth::guard('web')->user()->id);
        $this->setting = $setting;
        $this->name = $setting->name;
        $this->email = $setting->email;
        $this->province = $setting->province;
        $this->city = $setting->city;
        $this->tole = $setting->tole;
        $this->oldPhoto = $setting->photo;
        $this->phone = $setting->phone;
    }

    public function updateProfile()
    {
        $rule = [
            'name' => 'required|string||min:3|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->setting->id),
            ],
            'province' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'tole' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'phone' => 'required|digits:10',
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
            if($validation['photo']){
                $validation['photo'] = $validation['photo']->store('users','public');
            } else {
                $validation['photo'] = $this->oldPhoto;
            }

            if($this->password){
                $validation['password'] = Hash::make($validation['password']);
            }
            $this->setting->update($validation);
            DB::commit();   
            return redirect()->route('user.setting')->with('success','profile update successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('user.setting')->with('error', 'something went wrong' . $e->getMessage());
        }
    }
    public function render()
    {
        return view('livewire.user.setting');
    }
}
