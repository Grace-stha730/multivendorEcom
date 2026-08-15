<?php

namespace App\Livewire\Auth\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.user')]
#[Title('Create account')]
class Register extends Component
{
    public string $name = '';
    public string $email = '';

    public function continueWithEmail()
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
        ]);

        $data['email'] = strtolower($data['email']);

        if (User::where('email', $data['email'])->exists()) {
            session()->flash('error', 'This email is already registered. Please enter your password.');

            return $this->redirectRoute('user.login', ['email' => $data['email']], navigate: true);
        }

        $profile = [
            'name' => ucwords(strtolower($data['name'])),
            'email' => $data['email'],
            'photo' => null,
        ];

        try {
            $this->startVerification($profile);
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError('email', 'We could not send the OTP email. Please check the mail configuration and try again.');

            return;
        }

        session()->flash('success', "A 6-digit OTP was sent to {$profile['email']}.");

        return $this->redirectRoute('user.verify-otp', navigate: true);
    }

    private function startVerification(array $profile): void
    {
        $otp = (string) random_int(100000, 999999);

        // Send first.  Do not create a usable pending-registration session
        // when the mail transport rejects the message.
        Mail::mailer('smtp')->raw("Your verification code is: {$otp}\n\nIt expires in 10 minutes.", function ($message) use ($profile) {
            $message->to($profile['email'])->subject('Verify your account');
        });

        session()->put('pending_registration', array_merge($profile, [
            'otp' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10)->timestamp,
            'verified' => false,
        ]));

        Log::info('Registration OTP sent to SMTP transport.', ['email' => $profile['email']]);
    }

    public function render()
    {
        return view('livewire.auth.user.register');
    }
}
