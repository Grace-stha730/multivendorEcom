<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('password_reset_codes', function (Blueprint $table) {
            $table->id();
            $table->string('guard');
            $table->string('identifier')->comment('email (web/admin) or username (shop_user)');
            $table->string('code_hash');
            $table->timestamp('expires_at');
            $table->timestamp('sent_at');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamps();

            $table->unique(['guard', 'identifier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_codes');
    }
};
