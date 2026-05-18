<?php

namespace App\Livewire\Admin;

use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;

#[Layout('components.layouts.admin')]
#[Title('Setting')]
class Setting extends Component
{
    use WithFileUploads;
    public $setting, $name, $email, $oldImage, $image, $department, $phone, $address, $is_active, $password, $newPassword;
    public function mount()
    {
        $setting = Admin::find(Auth::guard('admin')->user()->id);
        $this->setting = $setting;
        $this->name = $setting->name;
        $this->email = $setting->email;
        $this->oldImage = $setting->image;
        $this->department = $setting->department;
        $this->phone = $setting->phone;
        $this->address = $setting->address;
        $this->is_active = $setting->is_active;
    }

    public function updateProfile()
    {


        $rules = [
            'name' => 'required|min:2|max:25',
            'email' => [
                'required',
                'email',
                Rule::unique('admins', 'email')->ignore($this->setting->id),
            ],
            'image' => 'nullable|image',
            'department' => 'nullable',
            'phone' => 'required|digits:10',
            'address' => 'required|min:3|max:30',
        ];

        // Only validate password if it is filled
        if ($this->password) {
            $rules['password'] = 'nullable|min:5|max:20';
            $rules['newPassword'] = 'required|same:password';
        } else {
            // Password not changing, leave newPassword nullable
            $rules['newPassword'] = 'nullable';
        }
        $validation = $this->validate($rules);

        DB::beginTransaction();
        try {
            $setting = Admin::find(Auth::guard('admin')->user()->id);
            // unset($validation[''])
            if ($validation['image']) {
                $validation['image'] = $validation['image']->store('admins', 'public');
            } else {
                $validation['image'] = $this->oldImage;
            }

            if ($this->password) {
                $validation['password'] = Hash::make($validation['password']);
            }
            $setting->update($validation);
            DB::commit();
            return redirect()->route('admin.setting')->with('success', 'Admin information update successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.setting')->with('error', 'Something went wrong' . $e->getMessage());
        }
    }
    public function render()
    {
        return view('livewire.admin.setting');
    }
}
