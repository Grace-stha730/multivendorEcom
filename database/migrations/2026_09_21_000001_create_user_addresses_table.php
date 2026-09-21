<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Nullable ONLY so addresses migrated from the old users table (which had no district and a
            // free-text province) can exist. The app's forms always require both for new addresses.
            $table->foreignId('province_id')->nullable()->constrained('provinces');
            $table->foreignId('district_id')->nullable()->constrained('districts');

            $table->string('city');
            $table->string('tole');
            $table->string('contact');
            $table->string('receiver_name');

            // What kind of place: home | office (office hours only apply to office).
            $table->string('address_type', 10)->default('home');
            $table->time('office_start_time')->nullable();
            $table->time('office_end_time')->nullable();

            // Which address is it in the user's book: real (permanent, exactly one) | shipping (any number).
            $table->string('address_category', 10)->default('shipping');

            // The address checkout pre-selects. At most one per user (kept by the app, promoted on delete).
            $table->boolean('is_default')->default(false);

            // "Exactly one real address per user", enforced by the DATABASE, not just by app code:
            // this generated column equals user_id only for real addresses, and is UNIQUE (NULLs never clash).
            $table->unsignedBigInteger('real_owner_id')->nullable()
                ->virtualAs("CASE WHEN address_category = 'real' THEN user_id END")->unique();

            $table->timestamps();

            $table->index(['user_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};
