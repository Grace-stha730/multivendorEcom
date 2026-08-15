<?php

namespace App\Livewire\Auth\User;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.user')]
#[Title('Create password')]
class CreatePassword extends Component
{
    public string $password = '';
    public string $password_confirmation = '';

    public function mount()
    {
        if (!session('pending_registration.verified')) {
            $this->redirectRoute('user.register', navigate: true);
        }
    }

    public function createAccount()
    {
        $this->validate(['password' => ['required', 'string', 'min:8', 'confirmed']]);
        $pending = session('pending_registration');

        if (!$pending || !$pending['verified']) {
            return $this->redirectRoute('user.register', navigate: true);
        }

        if (User::where('email', $pending['email'])->exists()) {
            session()->forget('pending_registration');
            return $this->redirectRoute('user.login', ['email' => $pending['email']], navigate: true);
        }

        $user = DB::transaction(fn () => User::create([
            'name' => $pending['name'],
            'email' => $pending['email'],
            'photo' => $pending['photo'] ?? null,
            'status' => 1,
            'password' => Hash::make($this->password),
        ]));

        session()->forget('pending_registration');
        Auth::guard('web')->login($user);
        session()->regenerate();

        return $this->redirectRoute('home', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.user.create-password', [
            'profile' => session('pending_registration'),
        ]);
    }
}
