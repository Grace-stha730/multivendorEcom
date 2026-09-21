<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Removes province, city, tole and phone from users. The email column is NOT touched.
 * Refuses to run unless every user's old address data was archived by the previous migration.
 */
return new class extends Migration {
    private const COLUMNS = ['province', 'city', 'tole', 'phone'];

    public function up(): void
    {
        if (!Schema::hasTable('legacy_user_addresses') || !Schema::hasTable('user_addresses')) {
            throw new RuntimeException('Run the address data migration first: legacy_user_addresses / user_addresses are missing. Nothing was dropped.');
        }

        // Every user that still has ANY of these values must already be in the archive with the same values.
        $unarchived = DB::table('users')
            ->where(fn ($q) => $q->whereRaw("COALESCE(province, '') <> ''")->orWhereRaw("COALESCE(city, '') <> ''")
                ->orWhereRaw("COALESCE(tole, '') <> ''")->orWhereRaw("COALESCE(phone, '') <> ''"))
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('legacy_user_addresses')
                ->whereColumn('legacy_user_addresses.user_id', 'users.id')
                ->whereRaw("COALESCE(legacy_user_addresses.province, '') = COALESCE(users.province, '')")
                ->whereRaw("COALESCE(legacy_user_addresses.city, '') = COALESCE(users.city, '')")
                ->whereRaw("COALESCE(legacy_user_addresses.tole, '') = COALESCE(users.tole, '')")
                ->whereRaw("COALESCE(legacy_user_addresses.phone, '') = COALESCE(users.phone, '')"))
            ->count();

        if ($unarchived > 0) {
            throw new RuntimeException("{$unarchived} user(s) have address data that is not safely archived. Nothing was dropped.");
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(self::COLUMNS);
        });
    }

    /** Puts the four columns back and restores their values from the archive. */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (self::COLUMNS as $column) {
                if (!Schema::hasColumn('users', $column)) {
                    $table->string($column)->nullable();
                }
            }
        });

        DB::table('legacy_user_addresses')->orderBy('id')->each(function ($row) {
            DB::table('users')->where('id', $row->user_id)->update([
                'province' => $row->province, 'city' => $row->city, 'tole' => $row->tole, 'phone' => $row->phone,
            ]);
        });
    }
};
