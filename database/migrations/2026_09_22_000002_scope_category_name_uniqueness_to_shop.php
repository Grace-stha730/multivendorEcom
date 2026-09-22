<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * categories.name was unique across the whole table, so one shop taking a name (e.g. "Shoes")
 * permanently blocked every other shop from ever using it. Categories are shop-owned data
 * (shop_id), so the name only needs to be unique within a shop. Admin-created categories
 * (shop_id NULL) keep their own global uniqueness via app-level validation, unchanged here.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_name_unique');
            $table->unique(['shop_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['shop_id', 'name']);
            $table->unique('name');
        });
    }
};
