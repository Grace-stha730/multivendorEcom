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
    public $shop_email, $password;

    public function login()
    {
        $validation = $this->validate([
            'shop_email' => 'required|email',
            'password' => 'required|min:2|max:20',
        ]);

        if (
            Auth::guard('vendor')->attempt([
                'shop_email' => $validation['shop_email'],
                'password' => $validation['password'],
            ])
        ) {

            return redirect()->route('vendor.dashboard')->with('success', 'Login Successfull');
        } else {
            return redirect()->route('vendor.login')->with('error', 'Invalid Credential');
        }
    }


    public function render()
    {
        
        return view('livewire.auth.login');
    }
}
