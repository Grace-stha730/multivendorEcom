<?php

namespace App\Livewire\Auth;

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

            return $this->success('Login successful', position: 'toast-bottom toast-end', redirectTo: route('shop-user.dashboard'));
        } else {
            $this->error('Invalid credentials', 'Please check your username and password.', 'toast-bottom toast-end');
        }
    }


    public function render()
    {
        
        return view('livewire.auth.login');
    }
}
