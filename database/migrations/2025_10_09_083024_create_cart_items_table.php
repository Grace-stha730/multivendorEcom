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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('quantity');
            $table->string('price');
            $table->string('sub_total');
            $table->text('selected_variants')->nullable();
            // Coupons are created by a later migration, so this cannot be a
            // foreign key in the initial cart-items-table migration.
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->decimal('coupon_discount', 10, 2)->default(0);
            $table->unique(['cart_id', 'product_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
