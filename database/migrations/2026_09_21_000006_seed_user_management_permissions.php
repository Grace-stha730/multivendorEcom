<?php

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds the new `admin-user-manage` permission (the seeder is idempotent). Super-admin receives it automatically.
 * Attach it to other admin roles from the database if you want them to create admin accounts too.
 */
return new class extends Migration {
    public function up(): void
    {
        (new RolesAndPermissionsSeeder())->run();
    }

    public function down(): void
    {
    }
};
