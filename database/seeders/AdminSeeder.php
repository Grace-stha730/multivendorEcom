<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Enums\DepartmentTypeState;
use App\Enums\RoleTypeState;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Admin::updateOrCreate(['email' => 'admin@gmail.com'], [
            'name' => 'Super Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => RoleTypeState::SUPER_ADMIN,
            'department' => DepartmentTypeState::ADMIN,
        ]);

        // Requires RolesAndPermissionsSeeder to have run first (see DatabaseSeeder).
        $admin->syncRoles('super-admin');
    }
}
