<?php

namespace App\Livewire\User;

use App\Models\District;
use App\Models\Province;
use App\Models\ShopRegistration;
use App\Rules\PhoneNumber;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.user')]
#[Title('Register Shop')]
class RegisterShop extends Component
{
    private const CODE_TTL_MINUTES = 10;
    private const RESEND_COOLDOWN_SECONDS = 60;

    public string $shop_name = '', $owner = '', $email = '', $pan_number = '', $contact_number = '';
    public $province_id = null, $district_id = null;
    public string $city = '', $tole = '';

    public bool $verifyModal = false;
    public string $verifyEmail = '';
    public string $otp = '';
    public string $statusEmail = '';
    public ?string $verifiedStatus = null;
    public ?string $verifiedReason = null;

    protected function rules(): array
    {
        return [
            'shop_name' => 'required|string|max:255',
            'owner' => 'required|string|max:255',
            'email' => [
                'required', 'email:rfc,dns', 'max:255',
                Rule::unique('shops', 'email'),
                Rule::unique('shop_registrations', 'email')->where(
                    fn ($q) => $q->whereIn('status', [ShopRegistration::PENDING, ShopRegistration::APPROVED])
                ),
            ],
            'pan_number' => ['required', 'digits:9'],
            'contact_number' => ['required', new PhoneNumber()],
            'province_id' => 'required|exists:provinces,id',
            'district_id' => [
                'required',
                Rule::exists('districts', 'id')->where('province_id', $this->province_id),
            ],
            'city' => 'required|string|max:255',
            'tole' => 'required|string|max:255',
        ];
    }

    protected function messages(): array
    {
        return [
            'email.unique' => 'A shop or registration with this email already exists. Use "Check status" below to verify or track it.',
            'pan_number.digits' => 'The PAN number must be exactly 9 digits (numbers only).',
        ];
    }

    public function updatedProvinceId(): void
    {
        $this->district_id = null;
    }

    public function submit(): void
    {
        $data = $this->validate();
        $data['email'] = strtolower($data['email']);

        $registration = ShopRegistration::create($data + [
            'status' => ShopRegistration::PENDING,
            'is_email_verified' => false,
        ]);

        if (!$this->sendCode($registration)) {
            $registration->delete();
            $this->addError('email', 'We could not send the verification email. Please try again.');

            return;
        }

        $this->openVerification($registration->email);
        $this->reset(['shop_name', 'owner', 'email', 'pan_number', 'contact_number', 'province_id', 'district_id', 'city', 'tole']);
    }

    /** Guests have no account, so status is only revealed after proving email ownership with a fresh code. */
    public function requestStatusCode(): void
    {
        $this->validate(['statusEmail' => 'required|email|max:255']);
        $email = strtolower($this->statusEmail);

        $registration = ShopRegistration::where('email', $email)->latest('id')->first();

        if ($registration && !$this->sendCode($registration)) {
            $this->addError('statusEmail', 'We could not send the code. Please try again shortly.');

            return;
        }

        // Same response whether or not a registration exists, so emails can't be enumerated.
        $this->openVerification($email);
    }

    public function verify(): void
    {
        $this->validate(['otp' => ['required', 'digits:6']]);

        $registration = ShopRegistration::where('email', $this->verifyEmail)->latest('id')->first();

        if (
            !$registration
            || !$registration->email_verification_code
            || !$registration->email_verification_code_expires_at?->isFuture()
            || !Hash::check($this->otp, $registration->email_verification_code)
        ) {
            $this->addError('otp', 'Invalid or expired code, please request a new one.');

            return;
        }

        $registration->update([
            'is_email_verified' => true,
            'email_verification_code' => null,
            'email_verification_code_expires_at' => null,
        ]);

        $this->verifiedStatus = $registration->status;
        $this->verifiedReason = $registration->status === ShopRegistration::REJECTED ? $registration->rejection_reason : null;
        $this->otp = '';
        $this->resetValidation();
    }

    public function resend(): void
    {
        $registration = ShopRegistration::where('email', $this->verifyEmail)->latest('id')->first();

        if (!$registration) {
            $this->addError('otp', 'Invalid or expired code, please request a new one.');

            return;
        }

        $sentAt = $registration->email_verification_code_sent_at;
        $wait = $sentAt ? self::RESEND_COOLDOWN_SECONDS - (int) $sentAt->diffInSeconds(now(), true) : 0;
        if ($wait > 0) {
            $this->addError('otp', "Please wait {$wait} seconds before requesting a new code.");

            return;
        }

        if (!$this->sendCode($registration)) {
            $this->addError('otp', 'We could not resend the code. Please try again.');

            return;
        }

        $this->resetValidation();
        session()->flash('code-sent', 'A new code was sent to your email.');
    }

    public function closeVerifyModal(): void
    {
        $this->verifyModal = false;
        $this->reset(['otp', 'verifiedStatus', 'verifiedReason', 'statusEmail']);
        $this->resetValidation();
    }

    private function openVerification(string $email): void
    {
        $this->verifyEmail = $email;
        $this->otp = '';
        $this->verifiedStatus = null;
        $this->verifiedReason = null;
        $this->resetValidation();
        $this->verifyModal = true;
    }

    private function sendCode(ShopRegistration $registration): bool
    {
        $code = (string) random_int(100000, 999999);

        try {
            // Send first so a failed transport never leaves a code the vendor can't receive.
            Mail::mailer('smtp')->raw(
                "Your shop registration verification code is: {$code}\n\nIt expires in " . self::CODE_TTL_MINUTES . ' minutes.',
                fn ($message) => $message->to($registration->email)->subject('Verify your shop registration')
            );
        } catch (\Throwable $exception) {
            report($exception);

            return false;
        }

        $registration->update([
            'email_verification_code' => Hash::make($code),
            'email_verification_code_expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES),
            'email_verification_code_sent_at' => now(),
        ]);

        return true;
    }

    public function render()
    {
        return view('livewire.user.register-shop', [
            'provinces' => Province::orderBy('name')->get(),
            'districts' => $this->province_id
                ? District::where('province_id', $this->province_id)->orderBy('name')->get()
                : collect(),
        ]);
    }
}
