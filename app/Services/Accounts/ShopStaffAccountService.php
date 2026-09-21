<?php

namespace App\Services\Accounts;

use App\Models\ShopUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Models\Role;

/**
 * Creates shop staff accounts (the `shop_users` table / `shop_user` role pool) inside the CREATOR'S OWN shop.
 * The shop always comes from the logged-in creator, never from the request, so a shop can never add staff to another shop.
 */
class ShopStaffAccountService
{
    public function __construct(private AccountMailer $mailer, private ShopUsernameGenerator $usernames)
    {
    }

    /** Shop roles this user may hand out: never a role stronger than their own permissions. */
    public function assignableRoles(ShopUser $actor)
    {
        return Role::where('guard_name', config('access.guards.shop_user'))->with('permissions')->orderBy('name')->get()
            ->filter(fn (Role $role) => $actor->hasAllPermissions($role->permissions))->values();
    }

    /** @return array{0: ShopUser, 1: bool, 2: string} the new staff member, whether the email was sent, and the username */
    public function create(ShopUser $actor, array $data, string $plainPassword): array
    {
        abort_unless($actor->checkPermissionTo('staff-invite', config('access.guards.shop_user')), 403);

        $role = $this->resolveRole($actor, (string) $data['role']);
        $shop = $actor->shop;

        $staff = DB::transaction(function () use ($actor, $shop, $data, $plainPassword, $role) {
            $staff = ShopUser::create([
                'name' => trim($data['name']),
                'username' => $this->usernames->generate($data['name'], $shop->name),
                'personal_email' => strtolower(trim($data['personal_email'])),
                'contact' => $data['contact'] ?: null,
                'password' => Hash::make($plainPassword),
                'shop_id' => $actor->shop_id,
            ]);
            $staff->assignRole($role);

            return $staff;
        });

        $emailed = $this->mailer->sendLoginDetails($staff->personal_email, $staff->name, "{$shop->name} shop staff", route('shop-user.login'), $staff->username, $plainPassword, $actor->name);

        return [$staff, $emailed, $staff->username];
    }

    private function resolveRole(ShopUser $actor, string $roleName): Role
    {
        // Looked up in the shop pool only, so an admin role name can never be assigned here.
        try {
            $role = Role::findByName($roleName, config('access.guards.shop_user'));
        } catch (RoleDoesNotExist) {
            throw ValidationException::withMessages(['role' => 'Choose a valid shop role.']);
        }

        if (!$actor->hasAllPermissions($role->permissions)) {
            throw ValidationException::withMessages(['role' => 'You cannot assign a role with more permissions than you have.']);
        }

        return $role;
    }
}
