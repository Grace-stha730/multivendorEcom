<?php

namespace App\Livewire\Vendor;

use App\Livewire\Concerns\AuthorizesPermissions;
use App\Models\Shop;
use App\Models\ShopUser;
use App\Rules\PhoneNumber;
use App\Services\Accounts\ShopStaffAccountService;
use App\Services\ShopStaffRoleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

/** Shop panel: create and manage staff accounts of the LOGGED-IN user's own shop. It never touches admin accounts. */
#[Layout('components.layouts.app')]
#[Title('Shop Staff')]
class Staff extends Component
{
    use AuthorizesPermissions, Toast;
    use \App\Livewire\Concerns\PaginatesList;

    public bool $showForm = false;
    public string $name = '', $personal_email = '', $contact = '', $password = '', $password_confirmation = '', $role = '';

    public bool $roleModal = false;
    public ?int $roleTargetId = null;
    public string $newRole = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'personal_email' => ['required', 'email', 'max:255', function (string $attribute, mixed $value, \Closure $fail) {
                $email = strtolower(trim((string) $value));
                if (ShopUser::whereRaw('lower(personal_email) = ?', [$email])->exists()) {
                    $fail('This email is already used by another staff account.');
                }
                // Password reset finds a vendor by personal email first, then by the shop email (-> the owner).
                // A staff email equal to a shop email would break the owner's password reset.
                if (Shop::whereRaw('lower(email) = ?', [$email])->exists()) {
                    $fail('This email belongs to a shop. Use the staff member\'s own email.');
                }
            }],
            'contact' => ['nullable', new PhoneNumber()],
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string',
        ];
    }

    public function openCreate(): void
    {
        $this->authorizeShop('staff-invite');
        $this->resetForm();
        $this->showForm = true;
    }

    public function save(ShopStaffAccountService $service): void
    {
        $this->authorizeShop('staff-invite');
        $data = $this->validate();

        try {
            [$staff, $emailed, $username] = $service->create(Auth::guard('shop_user')->user(), $data, $this->password);
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->errors());

            return;
        }

        $this->closeForm();

        if ($emailed) {
            $this->success('Staff account created', "Login details were emailed to {$staff->personal_email}.", 'toast-bottom toast-end');
        } else {
            $this->warning('Account created, but the email failed', "Share the login details yourself. Username: {$username}", 'toast-bottom toast-end', timeout: 12000);
        }
    }

    public function openRole(int $id): void
    {
        $this->authorizeShop('staff-assign-role');
        $target = $this->ownStaff($id);
        $this->roleTargetId = $target->id;
        $this->newRole = (string) $target->roles->first()?->name;
        $this->resetValidation();
        $this->roleModal = true;
    }

    public function saveRole(ShopStaffRoleService $service): void
    {
        $this->authorizeShop('staff-assign-role');
        $this->validate(['newRole' => 'required|string']);

        try {
            $service->changeRole(Auth::guard('shop_user')->user(), $this->ownStaff($this->roleTargetId), $this->newRole);
        } catch (ValidationException $e) {
            $this->addError('newRole', collect($e->errors())->flatten()->first());

            return;
        }

        $this->roleModal = false;
        $this->success('Role updated', null, 'toast-bottom toast-end');
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'personal_email', 'contact', 'password', 'password_confirmation', 'role']);
        $this->resetValidation();
    }

    /** Only ever a member of the logged-in user's own shop. */
    private function ownStaff(?int $id): ShopUser
    {
        return ShopUser::with('roles')->where('shop_id', currentShopId())->findOrFail($id);
    }

    public function render(ShopStaffAccountService $service)
    {
        $me = Auth::guard('shop_user')->user();

        return view('livewire.vendor.staff', [
            'staff' => ShopUser::with('roles')->where('shop_id', $me->shop_id)->orderBy('id')->paginate(10),
            'roles' => $service->assignableRoles($me),
            'me' => $me,
            'shopName' => $me->shop?->name,
        ]);
    }
}
