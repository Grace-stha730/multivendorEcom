<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('order_number')->unique()->autoIncrement();
            $table->string('name');
            $table->string('email');
            $table->string('province');
            $table->string('city');
            $table->string('tole');
            $table->string('phone');
            $table->string('price');
            $table->integer('quantity');
            $table->decimal('wallet_discount', 10, 2)->default(0);
            $table->integer('redeemed_points')->default(0);
            $table->decimal('coupon_discount', 10, 2)->default(0);
            $table->string('payment_status');
            $table->string('order_status')->comment('pending,processing,warehouse,shipped,delivered');
            $table->string('payment_method');
            $table->boolean('is_shipped')->default(false);
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
