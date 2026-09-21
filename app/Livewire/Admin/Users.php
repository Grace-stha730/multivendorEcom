<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\AuthorizesPermissions;
use App\Models\Admin;
use App\Rules\PhoneNumber;
use App\Services\Accounts\AdminAccountService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

/** Admin panel: create ADMIN accounts. It never creates or lists shop users. */
#[Layout('components.layouts.admin')]
#[Title('Admin Users')]
class Users extends Component
{
    use AuthorizesPermissions, Toast, WithPagination;

    public bool $showForm = false;
    public string $name = '', $email = '', $phone = '', $address = '', $password = '', $password_confirmation = '', $role = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', function (string $attribute, mixed $value, \Closure $fail) {
                if (Admin::whereRaw('lower(email) = ?', [strtolower(trim((string) $value))])->exists()) {
                    $fail('An admin with this email already exists.');
                }
            }],
            'phone' => ['nullable', new PhoneNumber()],
            'address' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string',
        ];
    }

    public function openCreate(): void
    {
        $this->authorizeAdmin('admin-user-manage');
        $this->resetForm();
        $this->showForm = true;
    }

    public function save(AdminAccountService $service): void
    {
        $this->authorizeAdmin('admin-user-manage');
        $data = $this->validate();

        try {
            [$admin, $emailed] = $service->create(Auth::guard('admin')->user(), $data, $this->password);
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->errors());

            return;
        }

        $this->closeForm();

        if ($emailed) {
            $this->success('Admin account created', "Login details were emailed to {$admin->email}.", 'toast-bottom toast-end');
        } else {
            $this->warning('Account created, but the email failed', "Share the login details with {$admin->email} yourself.", 'toast-bottom toast-end', timeout: 10000);
        }
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'email', 'phone', 'address', 'password', 'password_confirmation', 'role']);
        $this->resetValidation();
    }

    public function render(AdminAccountService $service)
    {
        return view('livewire.admin.users', [
            'admins' => Admin::with('roles')->orderByDesc('id')->paginate(15),
            'roles' => $service->assignableRoles(Auth::guard('admin')->user()),
        ]);
    }
}
