<?php

namespace App\Livewire\Auth;

use App\Services\PasswordResetService;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

/** One component for all three logins; the route default `guard` decides which account type. */
#[Title('Forgot password')]
class ForgotPassword extends Component
{
    #[Locked]
    public string $guard = 'web';

    public string $step = 'request';
    public string $identifier = '';
    public string $code = '';
    public string $password = '';
    public string $password_confirmation = '';

    private const LOGIN_ROUTES = ['web' => 'user.login', 'admin' => 'admin.login', 'shop_user' => 'shop-user.login'];

    public function mount(string $guard = 'web'): void
    {
        abort_unless(isset(PasswordResetService::GUARDS[$guard]), 404);
        $this->guard = $guard;
    }

    public function sendCode(PasswordResetService $service): void
    {
        $rules = $this->guard === 'shop_user' ? ['required', 'string', 'max:255'] : ['required', 'email', 'max:255'];
        $this->validate(['identifier' => $rules]);

        $key = "password-reset:{$this->guard}:" . strtolower($this->identifier) . '|' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('identifier', 'Too many attempts. Please try again in a few minutes.');

            return;
        }
        RateLimiter::hit($key, 900);

        if (!$service->sendCode($this->guard, $this->identifier)) {
            $this->addError('identifier', 'We could not send the code. Please try again shortly.');

            return;
        }

        // Same message whether or not the account exists.
        $this->step = 'reset';
    }

    public function resetPassword(PasswordResetService $service)
    {
        $this->validate([
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!$service->reset($this->guard, $this->identifier, $this->code, $this->password)) {
            $this->addError('code', 'Invalid or expired code. Request a new one and try again.');

            return;
        }

        session()->flash('success', 'Your password was reset. Please log in.');

        return $this->redirectRoute(self::LOGIN_ROUTES[$this->guard], navigate: true);
    }

    public function backToRequest(): void
    {
        $this->reset(['step', 'code', 'password', 'password_confirmation']);
        $this->resetValidation();
    }

    public function render()
    {
        $layout = $this->guard === 'web' ? 'components.layouts.user' : 'components.layouts.auth';

        return view('livewire.auth.forgot-password', [
            'identifierLabel' => PasswordResetService::label($this->guard),
            'loginUrl' => route(self::LOGIN_ROUTES[$this->guard]),
        ])->layout($layout);
    }
}
