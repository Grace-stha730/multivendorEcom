<?php

use App\Models\Admin;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

/**
 * Pages are now protected by permissions, so accounts that existed before roles would be locked out.
 * Before this change every admin was a "Super Admin" (the only value of the old admins.role enum),
 * so existing admins without a role get super-admin. Shop owners are backfilled by the seeder.
 * New installs are unaffected (no admins yet, seeder is idempotent).
 */
return new class extends Migration {
    public function up(): void
    {
        (new RolesAndPermissionsSeeder())->run();

        $superAdmin = Role::where('name', 'super-admin')->where('guard_name', config('access.guards.admin'))->first();

        if ($superAdmin) {
            Admin::query()->each(function (Admin $admin) use ($superAdmin): void {
                if ($admin->roles()->doesntExist()) {
                    $admin->assignRole($superAdmin);
                }
            });
        }
    }

    public function down(): void
    {
        // Role assignments are left in place; removing them would lock accounts out again.
    }
};
