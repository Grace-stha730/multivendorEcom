<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * DATA MIGRATION. Nothing is dropped here; the users columns are only removed by the NEXT migration,
 * and that one refuses to run unless everything below was safely archived.
 *
 *  1. Archive: copy the raw province/city/tole/phone of EVERY user that has any of them into
 *     legacy_user_addresses (a permanent backup table).
 *  2. Copy: users that have a city AND a tole AND a phone get one row in user_addresses, as their
 *     REAL address, marked default. Province is matched to provinces by name when it can be
 *     (otherwise province_id stays NULL); district is always NULL because users never had one.
 *     Users with only PART of an address are archived (step 1) but not copied: a half-empty
 *     address is unusable, so they simply add a new one.
 *  3. Verify: fail loudly if the numbers don't add up.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('legacy_user_addresses')) {
            Schema::create('legacy_user_addresses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();   // deliberately NOT a foreign key: it must survive user deletion
                $table->string('province')->nullable();
                $table->string('city')->nullable();
                $table->string('tole')->nullable();
                $table->string('phone')->nullable();
                $table->timestamp('archived_at')->nullable();
            });
        }

        // 1. archive everything that exists
        DB::table('users')
            ->where(fn ($q) => $q->whereRaw("COALESCE(province, '') <> ''")->orWhereRaw("COALESCE(city, '') <> ''")
                ->orWhereRaw("COALESCE(tole, '') <> ''")->orWhereRaw("COALESCE(phone, '') <> ''"))
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('legacy_user_addresses')->whereColumn('legacy_user_addresses.user_id', 'users.id'))
            ->orderBy('id')
            ->chunkById(500, function ($users) {
                DB::table('legacy_user_addresses')->insert($users->map(fn ($u) => [
                    'user_id' => $u->id, 'province' => $u->province, 'city' => $u->city,
                    'tole' => $u->tole, 'phone' => $u->phone, 'archived_at' => now(),
                ])->all());
            });

        // 2. copy complete addresses
        $provinces = DB::table('provinces')->pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name) => [$this->normalize($name) => $id]);

        DB::table('users')
            ->whereRaw("COALESCE(city, '') <> ''")->whereRaw("COALESCE(tole, '') <> ''")->whereRaw("COALESCE(phone, '') <> ''")
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('user_addresses')->whereColumn('user_addresses.user_id', 'users.id'))
            ->orderBy('id')
            ->chunkById(500, function ($users) use ($provinces) {
                DB::table('user_addresses')->insert($users->map(fn ($u) => [
                    'user_id' => $u->id,
                    'province_id' => $provinces[$this->normalize((string) $u->province)] ?? null,
                    'district_id' => null,
                    'city' => trim($u->city),
                    'tole' => trim($u->tole),
                    'contact' => trim($u->phone),
                    'receiver_name' => $u->name,
                    'address_type' => 'home',
                    'address_category' => 'real',
                    'is_default' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->all());
            });

        // 3. verify
        $expected = DB::table('users')->whereRaw("COALESCE(city, '') <> ''")->whereRaw("COALESCE(tole, '') <> ''")->whereRaw("COALESCE(phone, '') <> ''")->count();
        $copied = DB::table('user_addresses')->where('address_category', 'real')->count();
        if ($copied < $expected) {
            throw new RuntimeException("Address copy incomplete: expected at least {$expected} real addresses, found {$copied}. Nothing was dropped.");
        }
    }

    /** "Bagmati", " bagmati ", "Bagmati Province", "Bagmati Pradesh" all match "Bagmati". */
    private function normalize(string $name): string
    {
        $name = mb_strtolower(trim($name));

        return trim(preg_replace('/\s+(province|pradesh)$/u', '', $name));
    }

    public function down(): void
    {
        // Deliberately non-destructive: the archive table and the copied rows are kept.
    }
};
