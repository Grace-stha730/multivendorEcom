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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('name')->after('order_number');
            $table->string('province')->after('name');
            $table->string('city')->after('province');
            $table->string('tole')->after('city');
            $table->string('phone')->after('tole');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'province',
                'city',
                'tole',
                'phone'
            ]);
        });
    }
};
