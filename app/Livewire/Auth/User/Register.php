<?php

namespace App\Livewire\Auth\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;

#[Layout('components.layouts.user')]
#[Title('Register')]
class Register extends Component
{
    use WithFileUploads;
    public $name, $email, $province, $city, $tole, $photo, $phone, $password, $confirm_password;

    public function register()
    {
        $validation = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'province' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'tole' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048', // Optional profile photo
            'phone' => 'required|digits:10',
            'password' => 'required|string|min:5',
            'confirm_password' => 'required|same:password',
        ]);
        DB::beginTransaction();
        try {


            if ($validation['photo']) {
                $validation['photo'] = $validation['photo']->store('users', 'public');
            } else {
                $validation['photo'] = null;
            }

            $validation['password'] = Hash::make($validation['password']);
            $validation['name'] = ucwords(strtolower($validation['name']));
            User::create($validation);
            $this->reset();
            DB::commit();
            return redirect()->route('user.login')->with('success', 'User Registration Successful');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Something Went Wrong');
            return $this->redirectRoute('user.register');
        }

    }
    public function render()
    {
        return view('livewire.auth.user.register');
    }
}
