<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An order keeps its OWN copy of the delivery address, so editing or deleting a saved address later
 * never changes a past order. Existing columns are reused for the snapshot:
 *   name (account holder), phone (= contact), province (province NAME text), city, tole.
 * This migration only adds what is missing. Old orders simply keep NULLs in the new columns.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Traceability only. If the saved address is deleted this becomes NULL; the snapshot stays.
            $table->foreignId('user_address_id')->nullable()->after('user_id')
                ->constrained('user_addresses')->nullOnDelete();

            $table->string('receiver_name')->nullable()->after('name');

            // Reference data (provinces/districts) is static, so plain restricted FKs are fine.
            $table->foreignId('province_id')->nullable()->constrained('provinces');
            $table->foreignId('district_id')->nullable()->constrained('districts');

            $table->string('address_type', 10)->nullable();
            $table->time('office_start_time')->nullable();
            $table->time('office_end_time')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_address_id');
            $table->dropConstrainedForeignId('province_id');
            $table->dropConstrainedForeignId('district_id');
            $table->dropColumn(['receiver_name', 'address_type', 'office_start_time', 'office_end_time']);
        });
    }
};
