<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('shop_name');
            $table->string('owner_name');
            $table->string('shop_province');
            $table->string('shop_city');
            $table->string('shop_tole');
            $table->string('shop_image')->nullable();
            $table->string('shop_phone');
            $table->string('shop_email');
            $table->string('password');
            $table->string('token')->nullable();
            $table->string('status')->default(0)->comment('0=inactive, 1=active, 2=suspended');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
