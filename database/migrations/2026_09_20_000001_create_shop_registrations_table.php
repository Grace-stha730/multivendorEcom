<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shop_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('shop_name');
            $table->string('owner');
            $table->string('email')->index();
            $table->string('pan_number', 9);
            $table->string('contact_number', 30);
            $table->foreignId('province_id')->constrained('provinces');
            $table->foreignId('district_id')->constrained('districts');
            $table->string('city');
            $table->string('tole');
            $table->string('status')->default('PENDING')->index()->comment('PENDING, APPROVED, REJECTED');
            $table->boolean('is_email_verified')->default(false);
            $table->string('email_verification_code')->nullable()->comment('Hashed 6-digit code');
            $table->timestamp('email_verification_code_expires_at')->nullable();
            $table->timestamp('email_verification_code_sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_registrations');
    }
};
