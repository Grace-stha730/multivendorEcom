<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\ShopUser;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeds the two independent role pools. Safe to re-run (idempotent).
 * This file only holds *data*; runtime authorization never reads these lists.
 * Add roles/permissions here, or create them straight in the DB - both work.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    /** Pool 1: platform staff (guard "admin"). '*' = every admin permission. */
    private const ADMIN_ROLES = [
        'super-admin' => '*',
        'shop-manager' => ['shop-view', 'shop-create', 'shop-edit', 'shop-approve', 'shop-delete'],
        'operator' => ['order-view', 'order-process', 'order-cancel'],
        'finance' => ['payment-view', 'payment-process', 'payout-view'],
        'delivery-manager' => ['delivery-view', 'delivery-assign', 'delivery-update-status'],
    ];

    /**
     * Admin permissions that guard pages beyond the core role list. Only super-admin ('*') holds them
     * by default; attach them to other roles from the database as needed.
     */
    private const ADMIN_EXTRA_PERMISSIONS = [
        'product-view', 'category-manage', 'coupon-manage', 'message-view', 'policy-manage', 'admin-user-manage',
    ];

    /** Pool 2: shop staff (guard "shop_user"). Only ever effective inside the user's own shop_id. */
    private const SHOP_ROLES = [
        'shop-owner' => [
            'product-view', 'product-create', 'product-edit', 'product-delete',
            'order-view', 'order-update-status', 'staff-invite', 'staff-remove', 'staff-assign-role',
            'earnings-view', 'shop-edit-settings',
            'category-manage', 'coupon-manage', 'chat-reply',
        ],
        'shop-staff' => ['product-create', 'product-edit', 'product-view'],
        'shop-salesman' => ['order-view', 'order-update-status', 'chat-reply'],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->seedPool(config('access.guards.admin'), self::ADMIN_ROLES, self::ADMIN_EXTRA_PERMISSIONS);
        $this->seedPool(config('access.guards.shop_user'), self::SHOP_ROLES);
        $this->backfillShopOwners();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function seedPool(string $guard, array $roles, array $extraPermissions = []): void
    {
        $permissionNames = collect($roles)
            ->flatten()
            ->reject(fn ($p) => $p === '*')
            ->merge($extraPermissions)
            ->unique();

        foreach ($permissionNames as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
        }

        foreach ($roles as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => $guard]);

            $role->syncPermissions(
                $permissions === '*'
                    ? Permission::where('guard_name', $guard)->get()
                    : $permissions
            );
        }
    }

    /** Shops that existed before roles: give each shop's first shop_user the owner role. */
    private function backfillShopOwners(): void
    {
        $ownerRole = Role::where('name', config('access.default_shop_owner_role'))
            ->where('guard_name', config('access.guards.shop_user'))
            ->first();

        if (!$ownerRole) {
            return;
        }

        Shop::query()->each(function (Shop $shop) use ($ownerRole): void {
            $first = ShopUser::where('shop_id', $shop->id)->orderBy('id')->first();

            if ($first && $first->roles()->doesntExist()) {
                $first->assignRole($ownerRole);
            }
        });
    }
}
