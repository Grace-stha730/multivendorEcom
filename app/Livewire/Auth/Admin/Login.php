<?php

namespace App\Livewire\Auth\Admin;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Layout('components.layouts.auth')]
#[Title('Login')]
class Login extends Component
{
    use Toast;

    public $email,$password;
    public function login(){
        $validation = $this->validate([
            'email' => 'required|email',
            'password' => 'required|min:5',
        ]);

        if(Auth::guard('admin')->attempt($validation)){
            return $this->success('Login successful', position: 'toast-bottom toast-end', redirectTo: route('admin.dashboard'));
        }
        else{
            $this->error('Invalid credentials', 'Please check your email and password.', 'toast-bottom toast-end');
        }
    }
    public function render()
    {
        return view('livewire.auth.admin.login');
    }
}
