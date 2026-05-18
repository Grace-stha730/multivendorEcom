<?php

namespace App\Livewire\Auth\Admin;

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;

#[Layout('components.layouts.auth')]
#[Title('Register')]
class Register extends Component
{
    use WithFileUploads;
    public $name, $email, $password, $confirm_password, $image, $department, $phone, $address;

    public function register()
    {
        $validation = $this->validate([
            'name' => 'required|min:2|max:25',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|min:5|max:20',
            'confirm_password' => 'required|same:password',
            'image' => 'required|image',
            'department' => 'nullable',
            'phone' => 'required|digits:10',
            'address' => 'required|min:3|max:30',
        ]);
        DB::beginTransaction();
        try {
            $validation['image'] = $validation['image']->store('admins', 'public');
            $validation['password'] = Hash::make($validation['password']);
            Admin::create($validation);
            DB::commit();
            return redirect()->route('admin.login')->with('success','Admin registration successfull');


        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.register')->with('error', 'Something went wrong');
        }
    }
    public function render()
    {
        return view('livewire.auth.admin.register');
    }
}
