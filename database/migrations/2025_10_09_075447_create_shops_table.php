<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('owner');
            $table->string('image')->nullable();
            $table->string('contact_number');
            $table->string('pan_number')->nullable();
            $table->foreignId('province_id')->constrained('provinces');
            $table->foreignId('district_id')->constrained('districts');
            $table->string('city');
            $table->string('tole');
            $table->string('email')->unique();
            $table->string('status')->default('active');
            $table->decimal('weighted_rating', 4, 2)->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
