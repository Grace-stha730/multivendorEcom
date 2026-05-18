<?php

namespace App\Livewire\Auth\User;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.user')]
#[Title('Login')]
class Login extends Component
{
    public $email, $password;

    public function login()
    {
        $validation = $this->validate([
            'email' => 'required|email',
            'password' => 'required|min:2|max:20',
        ]);

        if (Auth::guard('web')->attempt($validation)) {
            return redirect()->route('home')->with('success', 'Login Successfully');
        } else {
            return redirect()->route('user.login')->with('error', 'Invalid Credential');
        }
    }
    public function render()
    {
        return view('livewire.auth.user.login');
    }
}
