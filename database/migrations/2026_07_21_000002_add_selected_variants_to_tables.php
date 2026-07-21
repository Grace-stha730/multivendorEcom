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
        Schema::table('cart_items', function (Blueprint $table) {
            $table->text('selected_variants')->nullable()->after('price');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->text('selected_variants')->nullable()->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn('selected_variants');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('selected_variants');
        });
    }
};
