<?php

namespace App\Livewire\Auth;

use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

#[Layout('components.layouts.auth')]
#[Title('Register')]
class Register extends Component
{
    use withFileUploads;
    public $shop_name, $owner_name, $shop_province, $shop_city, $shop_tole, $shop_image, $shop_phone, $shop_email, $password, $confirm_password;

    public function register()
    {
        DB::beginTransaction();
        try {
            $validation = $this->validate([
                'shop_name' => 'required|min:2|max:20',
                'owner_name' => 'required|min:2|max:20',
                'shop_province' => 'required|min:2|max:20',
                'shop_city' => 'required|min:2|max:20',
                'shop_tole' => 'required|min:2|max:20',
                'shop_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5000',
                'shop_phone' => 'required|digits:10',
                'shop_email' => 'required|email',
                'password' => 'required|min:2|max:20',
                'confirm_password' => 'required|same:password',
            ]);
            // dd($validation);

            if ($validation['shop_image']) {
                $validation['shop_image'] = $validation['shop_image']->store('vendors', 'public');
            } else {
                $validation['image'] = null;
            }

            $validation['password'] = Hash::make($validation['password']);
            $validation['shop_name'] = ucwords(strtolower($validation['shop_name']));
            Vendor::create($validation);
            session()->flash('success', 'Vendor Registration Successfull');
            $this->reset();
            DB::commit();
            return redirect()->route('vendor.login')->with('success', 'Vendor Registration Successfull');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Something Went Wrong');
            return $this->redirectRoute('vendor.register');
        }
    }
    public function render()
    {
        return view('livewire.auth.register');
    }
}
