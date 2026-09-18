<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.auth')]
#[Title('Login')]
class Login extends Component
{
    public $username, $password;

    public function login()
    {
        $validation = $this->validate([
            'username' => 'required|string',
            'password' => 'required|min:2|max:20',
        ]);

        if (
            Auth::guard('shop_user')->attempt([
                'username' => $validation['username'],
                'password' => $validation['password'],
            ])
        ) {

            return redirect()->route('shop-user.dashboard')->with('success', 'Login successful');
        } else {
            return redirect()->route('shop-user.login')->with('error', 'Invalid credentials');
        }
    }


    public function render()
    {
        
        return view('livewire.auth.login');
    }
}
