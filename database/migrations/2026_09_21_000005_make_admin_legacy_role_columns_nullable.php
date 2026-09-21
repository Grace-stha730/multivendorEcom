<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * admins.role / admins.department are legacy columns: each accepts a single enum value ("Super Admin" / "ADMIN"),
 * so they cannot describe a finance or delivery admin. Real roles now live in the Spatie role tables.
 * New admin accounts leave these two NULL. The original admins migration is not touched.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->string('role')->nullable()->change();
            $table->string('department')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Not reversible: accounts created after this migration have NULL here, and NOT NULL would fail.
    }
};
