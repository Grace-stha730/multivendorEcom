<?php

namespace App\Services\Accounts;

use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Models\Role;

/**
 * Creates ADMIN accounts only (the `admins` table / `admin` role pool). It cannot create shop users:
 * shop staff are created from the shop panel by the shop's own owner.
 */
class AdminAccountService
{
    public function __construct(private AccountMailer $mailer)
    {
    }

    /** Admin roles this admin is allowed to hand out: never a role stronger than their own permissions. */
    public function assignableRoles(Admin $actor)
    {
        return Role::where('guard_name', config('access.guards.admin'))->with('permissions')->orderBy('name')->get()
            ->filter(fn (Role $role) => $actor->hasAllPermissions($role->permissions))->values();
    }

    /** @return array{0: Admin, 1: bool} the new admin and whether the login email was sent */
    public function create(Admin $actor, array $data, string $plainPassword): array
    {
        abort_unless($actor->checkPermissionTo('admin-user-manage', config('access.guards.admin')), 403);

        $role = $this->resolveRole($actor, (string) $data['role']);

        $admin = DB::transaction(function () use ($data, $plainPassword, $role) {
            $admin = Admin::create([
                'name' => trim($data['name']),
                'email' => strtolower(trim($data['email'])),
                'password' => Hash::make($plainPassword),
                'phone' => $data['phone'] ?: null,
                'address' => $data['address'] ?: null,
                'is_active' => true,
                // role / department are legacy columns; the real role is the Spatie role below.
            ]);
            $admin->assignRole($role);

            return $admin;
        });

        $emailed = $this->mailer->sendLoginDetails($admin->email, $admin->name, 'admin', route('admin.login'), $admin->email, $plainPassword, $actor->name);

        return [$admin, $emailed];
    }

    private function resolveRole(Admin $actor, string $roleName): Role
    {
        // Looked up in the admin pool only, so a shop role name can never be assigned here.
        try {
            $role = Role::findByName($roleName, config('access.guards.admin'));
        } catch (RoleDoesNotExist) {
            throw ValidationException::withMessages(['role' => 'Choose a valid admin role.']);
        }

        if (!$actor->hasAllPermissions($role->permissions)) {
            throw ValidationException::withMessages(['role' => 'You cannot assign a role with more permissions than you have.']);
        }

        return $role;
    }
}
