<?php

namespace App\Livewire\Auth\User;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Mary\Traits\Toast;

#[Layout('components.layouts.user')]
#[Title('Login')]
class Login extends Component
{
    use Toast;

    public $email, $password;

    public function mount(): void
    {
        $this->email = request('email', '');
    }

    public function login()
    {
        $validation = $this->validate([
            'email' => 'required|email',
            'password' => 'required|min:2|max:20',
        ]);

        if (Auth::guard('web')->attempt($validation)) {
            return $this->success('Login successful', position: 'toast-bottom toast-end', redirectTo: route('home'));
        } else {
            $this->error('Invalid credentials', 'Please check your email and password.', 'toast-bottom toast-end');
        }
    }
    public function render()
    {
        return view('livewire.auth.user.login');
    }
}
