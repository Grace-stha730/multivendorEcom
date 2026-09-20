<?php

namespace App\Services;

use App\Models\ShopUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Models\Role;

/** Lets a shop user with `staff-assign-role` change another staff member's role in the SAME shop. */
class ShopStaffRoleService
{
    private const PERMISSION = 'staff-assign-role';

    public function changeRole(ShopUser $actor, ShopUser $target, string $roleName): ShopUser
    {
        $guard = config('access.guards.shop_user');

        abort_unless($actor->checkPermissionTo(self::PERMISSION, $guard), 403);
        abort_unless($actor->shop_id === $target->shop_id, 403, 'That user belongs to another shop.');

        // Looked up in the shop_user pool only, so an admin role name can never be assigned here.
        try {
            $role = Role::findByName($roleName, $guard);
        } catch (RoleDoesNotExist) {
            throw ValidationException::withMessages(['role' => 'Unknown shop role.']);
        }

        // No privilege escalation: you can't hand out a role stronger than your own permissions.
        if (!$actor->hasAllPermissions($role->permissions)) {
            throw ValidationException::withMessages(['role' => 'You cannot assign a role with more permissions than you have.']);
        }

        DB::transaction(function () use ($target, $role): void {
            $target->syncRoles($role);

            // A shop must always keep someone who can manage roles (permission-based, no role-name check).
            $managers = ShopUser::where('shop_id', $target->shop_id)->permission(self::PERMISSION)->count();
            if ($managers === 0) {
                throw ValidationException::withMessages(['role' => 'A shop must keep at least one user who can assign roles.']);
            }
        });

        return $target->fresh('roles');
    }
}
