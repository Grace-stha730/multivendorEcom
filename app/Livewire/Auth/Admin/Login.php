<?php

namespace App\Livewire\Auth\Admin;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.auth')]
#[Title('Login')]
class Login extends Component
{
    public $email,$password;
    public function login(){
        $validation = $this->validate([
            'email' => 'required|email',
            'password' => 'required|min:5',
        ]);

        if(Auth::guard('admin')->attempt($validation)){
            return redirect()->route('admin.dashboard')->with('success','Login successsfull');
        }
        else{
            return redirect()->route('admin.login')->with('error',"Invalid Credential");
        }
    }
    public function render()
    {
        return view('livewire.auth.admin.login');
    }
}
