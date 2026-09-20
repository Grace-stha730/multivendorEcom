<?php

namespace App\Livewire\Admin;

use App\Models\Shop;
use App\Models\ShopRegistration;
use App\Models\ShopUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

#[Layout('components.layouts.admin')]
#[Title('Shop Registrations')]
class ShopRegistrations extends Component
{
    use WithPagination;
    use Toast;

    public string $statusFilter = '';
    public string $sortDirection = 'desc';

    public ?int $registrationId = null;
    public bool $detailModal = false;
    public bool $approveModal = false;
    public bool $rejectModal = false;
    public bool $deleteModal = false;

    public $password;
    public $password_confirmation;
    public $rejection_reason = '';

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function toggleSort(): void
    {
        $this->sortDirection = $this->sortDirection === 'desc' ? 'asc' : 'desc';
    }

    public function view(int $id): void
    {
        $this->registrationId = $this->findVerified($id)->id;
        $this->detailModal = true;
    }

    public function confirmApprove(int $id): void
    {
        $this->authorizeAction('shop-approve');
        $registration = $this->findVerified($id);

        if (!in_array($registration->status, [ShopRegistration::PENDING, ShopRegistration::REJECTED], true)) {
            $this->error('Already approved', 'Only pending or rejected registrations can be approved.', 'toast-bottom');

            return;
        }

        $this->reset(['password', 'password_confirmation']);
        $this->resetValidation();
        $this->registrationId = $registration->id;
        $this->detailModal = false;
        $this->approveModal = true;
    }

    /** Creates the shop and its login account (mirrors Admin\Shop::save) and marks the request approved. */
    public function approve(): void
    {
        $this->authorizeAction('shop-approve');
        $this->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $registration = $this->findVerified($this->registrationId);

        if (!in_array($registration->status, [ShopRegistration::PENDING, ShopRegistration::REJECTED], true)) {
            $this->error('Already approved', 'Only pending or rejected registrations can be approved.', 'toast-bottom');

            return;
        }

        if (Shop::where('email', $registration->email)->exists()) {
            $this->addError('password', 'A shop with this email already exists, so this registration cannot be approved.');

            return;
        }

        $username = '';

        DB::transaction(function () use ($registration, &$username): void {
            $shop = Shop::create([
                'name' => $registration->shop_name,
                'owner' => $registration->owner,
                'contact_number' => $registration->contact_number,
                'pan_number' => $registration->pan_number,
                'province_id' => $registration->province_id,
                'district_id' => $registration->district_id,
                'city' => $registration->city,
                'tole' => $registration->tole,
                'email' => $registration->email,
                'status' => 'active',
            ]);

            $username = $this->uniqueUsername($shop->owner, $shop->name);

            ShopUser::create([
                'name' => $shop->owner,
                'username' => $username,
                'password' => Hash::make($this->password),
                'shop_id' => $shop->id,
            ]);

            $registration->update(['status' => ShopRegistration::APPROVED, 'rejection_reason' => null]);
        });

        // Capture before closeModals() clears the form.
        $emailed = $this->notifyApproved($registration, $username, (string) $this->password);

        $this->closeModals();

        if ($emailed) {
            $this->success('Registration approved', "Shop and user account created. Login details were emailed to {$registration->email}.", 'toast-bottom');
        } else {
            // The account exists either way; don't let the credentials get lost.
            $this->warning('Approved, but the email failed', "Share these manually. Username: {$username}, and the password you just set. Email: {$registration->email}", 'toast-bottom', timeout: 15000);
        }
    }

    public function confirmReject(int $id): void
    {
        $this->authorizeAction('shop-approve');
        $registration = $this->findVerified($id);

        if ($registration->status !== ShopRegistration::PENDING) {
            $this->error('Not pending', 'Only pending registrations can be rejected.', 'toast-bottom');

            return;
        }

        $this->registrationId = $registration->id;
        $this->rejection_reason = '';
        $this->resetValidation();
        $this->detailModal = false;
        $this->rejectModal = true;
    }

    public function reject(): void
    {
        $this->authorizeAction('shop-approve');
        $this->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'rejection_reason.required' => 'Please tell the vendor why the registration was rejected.',
            'rejection_reason.min' => 'The reason must be at least 5 characters.',
        ]);

        $registration = $this->findVerified($this->registrationId);

        if ($registration->status !== ShopRegistration::PENDING) {
            $this->error('Not pending', 'Only pending registrations can be rejected.', 'toast-bottom');

            return;
        }

        $registration->update(['status' => ShopRegistration::REJECTED, 'rejection_reason' => trim($this->rejection_reason)]);

        $emailed = $this->notifyRejected($registration);

        $this->closeModals();

        if ($emailed) {
            $this->success('Registration rejected', "The vendor was emailed the reason at {$registration->email}.", 'toast-bottom');
        } else {
            $this->warning('Rejected, but the email failed', "The vendor was not notified. Email: {$registration->email}", 'toast-bottom', timeout: 12000);
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->authorizeAction('shop-delete');
        $this->registrationId = $this->findVerified($id)->id;
        $this->detailModal = false;
        $this->deleteModal = true;
    }

    public function delete(): void
    {
        $this->authorizeAction('shop-delete');
        $this->findVerified($this->registrationId)->delete();

        $this->closeModals();
        $this->success('Registration deleted', 'The registration request was deleted.', 'toast-bottom');
    }

    public function closeModals(): void
    {
        $this->detailModal = $this->approveModal = $this->rejectModal = $this->deleteModal = false;
        $this->reset(['registrationId', 'password', 'password_confirmation', 'rejection_reason']);
        $this->resetValidation();
    }

    // Livewire actions are separate requests, so re-check the permission on every action.
    private function authorizeAction(string $permission): void
    {
        abort_unless(authorizeUserCheck($permission, 'admin'), 403);
    }

    /** Only email-verified requests are visible to (and actionable by) admins. */
    private function findVerified(?int $id): ShopRegistration
    {
        return ShopRegistration::with(['province', 'district'])
            ->where('is_email_verified', true)
            ->findOrFail($id);
    }

    /** Emails the vendor their login details. Returns false if the mail could not be sent. */
    private function notifyApproved(ShopRegistration $registration, string $username, string $password): bool
    {
        $loginUrl = route('shop-user.login');

        $body = <<<TEXT
Hello {$registration->owner},

Good news! Your shop "{$registration->shop_name}" has been registered and approved.

You can now log in to your shop dashboard:

  Login page: {$loginUrl}
  Username:   {$username}
  Password:   {$password}

For your security, please change your password after you log in (Settings > Password).
Do not share these details with anyone.

Thank you for joining us.
TEXT;

        try {
            Mail::mailer('smtp')->raw(
                $body,
                fn ($message) => $message->to($registration->email)->subject('Your shop is registered - login details')
            );
        } catch (\Throwable $exception) {
            report($exception);
            Log::warning('Shop approval email failed.', ['registration_id' => $registration->id]);

            return false;
        }

        return true;
    }

    /** Tells the vendor why they were rejected and how to apply again. Returns false if the mail could not be sent. */
    private function notifyRejected(ShopRegistration $registration): bool
    {
        $registerUrl = route('user.register-shop');
        $contactUrl = route('user.contact-us');
        $reason = $registration->rejection_reason;

        $body = <<<TEXT
Hello {$registration->owner},

Thank you for applying to sell on our marketplace.

Unfortunately, your shop "{$registration->shop_name}" could not be registered at this time.

Reason:
{$reason}

What you can do next:
  1. Fix the issue mentioned above.
  2. Submit a new registration using the same email address: {$registerUrl}
     (You do not need to log in. You will be asked to confirm your email with a 6-digit code.)
  3. If you think this was a mistake or need help, contact us: {$contactUrl}

You can also check the status of your request at any time on the same registration page.
TEXT;

        try {
            Mail::mailer('smtp')->raw(
                $body,
                fn ($message) => $message->to($registration->email)->subject('Your shop registration was not approved')
            );
        } catch (\Throwable $exception) {
            report($exception);
            Log::warning('Shop rejection email failed.', ['registration_id' => $registration->id]);

            return false;
        }

        return true;
    }

    public function getGeneratedUsernamePreviewProperty(): string
    {
        if (!$this->approveModal || !$this->registrationId) {
            return '';
        }

        $registration = ShopRegistration::find($this->registrationId);

        return $registration ? $this->uniqueUsername($registration->owner, $registration->shop_name) : '';
    }

    // Copied from Admin\Shop::uniqueUsername so the existing component stays untouched.
    private function uniqueUsername(string $owner, string $shopName): string
    {
        $ownerWords = preg_split('/\s+/', trim(mb_strtolower($owner)), -1, PREG_SPLIT_NO_EMPTY);
        $ownerPart = count($ownerWords) <= 2
            ? implode('.', $ownerWords)
            : $ownerWords[0] . '.' . $ownerWords[array_key_last($ownerWords)];

        $ownerPart = preg_replace('/[^\pL\pN.]/u', '', $ownerPart);
        $shopTokens = preg_split('/[\s.]+/u', trim(mb_strtolower($shopName)), -1, PREG_SPLIT_NO_EMPTY);
        $filteredShopTokens = [];
        $brandNameStarted = false;

        foreach ($shopTokens as $token) {
            $isLegalToken = in_array($token, ['pvt', 'ltd'], true);

            if ($isLegalToken && $brandNameStarted) {
                continue;
            }

            $filteredShopTokens[] = $token;
            $brandNameStarted = !$isLegalToken;
        }

        $shopPart = implode('', array_map(
            fn (string $token) => preg_replace('/[^\pL\pN]/u', '', $token),
            $filteredShopTokens,
        ));
        $shopPart = $shopPart ?: 'shop';
        $ownerPart = $ownerPart ?: 'owner';
        $domain = $shopPart . '.com';

        $suffix = 0;
        do {
            $username = $ownerPart . ($suffix ?: '') . '@' . $domain;
            $suffix++;
        } while (ShopUser::where('username', $username)->exists());

        return $username;
    }

    public function render()
    {
        $registrations = ShopRegistration::with(['province', 'district'])
            ->where('is_email_verified', true)
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderBy('created_at', $this->sortDirection)
            ->paginate(10);

        $registrations->getCollection()->each(function (ShopRegistration $r): void {
            $r->location = collect([$r->district?->name, $r->province?->name])->filter()->join(', ');
        });

        return view('livewire.admin.shop-registrations', [
            'registrations' => $registrations,
            'selected' => $this->registrationId ? ShopRegistration::with(['province', 'district'])->find($this->registrationId) : null,
            'headers' => [
                ['key' => 'shop_name', 'label' => 'Shop', 'sortable' => false],
                ['key' => 'owner', 'label' => 'Owner', 'sortable' => false],
                ['key' => 'location', 'label' => 'Location', 'sortable' => false],
                ['key' => 'status', 'label' => 'Status', 'sortable' => false],
                ['key' => 'created_at', 'label' => 'Submitted', 'sortable' => false],
                ['key' => 'actions', 'label' => 'Actions', 'class' => 'w-40 text-right', 'sortable' => false],
            ],
        ]);
    }
}
