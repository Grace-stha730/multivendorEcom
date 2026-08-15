<?php

namespace App\Livewire\Auth\User;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.user')]
#[Title('Verify email')]
class VerifyOtp extends Component
{
    public string $otp = '';

    public function mount()
    {
        if (!session()->has('pending_registration')) {
            $this->redirectRoute('user.register', navigate: true);
        }
    }

    public function verify()
    {
        $this->validate(['otp' => ['required', 'digits:6']]);
        $pending = session('pending_registration');

        if (!$pending || now()->timestamp > $pending['expires_at'] || !Hash::check($this->otp, $pending['otp'])) {
            $this->addError('otp', 'This verification code is invalid or has expired.');
            return;
        }

        $pending['verified'] = true;
        session()->put('pending_registration', $pending);

        return $this->redirectRoute('user.create-password', navigate: true);
    }

    public function resend()
    {
        $pending = session('pending_registration');
        if (!$pending) {
            return $this->redirectRoute('user.register', navigate: true);
        }

        $code = (string) random_int(100000, 999999);

        try {
            // Preserve the previous OTP if sending the replacement fails.
            Mail::mailer('smtp')->raw("Your new verification code is: {$code}\n\nIt expires in 10 minutes.", function ($message) use ($pending) {
                $message->to($pending['email'])->subject('Verify your account');
            });

            $pending['otp'] = Hash::make($code);
            $pending['expires_at'] = now()->addMinutes(10)->timestamp;
            $pending['verified'] = false;
            session()->put('pending_registration', $pending);

            Log::info('Registration OTP resend sent to SMTP transport.', ['email' => $pending['email']]);
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError('otp', 'We could not resend the OTP. Please try again.');

            return;
        }

        session()->flash('success', 'A new code was sent to your email.');
    }

    public function render()
    {
        return view('livewire.auth.user.verify-otp', ['email' => session('pending_registration.email')]);
    }
}
