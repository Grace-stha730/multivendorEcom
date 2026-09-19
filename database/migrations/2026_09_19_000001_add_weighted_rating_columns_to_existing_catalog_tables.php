<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Existing installations have already run the original create-table
     * migrations, so changing those files alone cannot change their schema.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'weighted_rating')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->decimal('weighted_rating', 4, 2)->nullable()->index();
            });
        }

        if (! Schema::hasColumn('shops', 'weighted_rating')) {
            Schema::table('shops', function (Blueprint $table): void {
                $table->decimal('weighted_rating', 4, 2)->nullable()->index();
            });
        }
    }

    /**
     * Kept intentionally non-destructive: a fresh installation may already
     * receive the columns from its create-table migrations.
     */
    public function down(): void
    {
        // Do not drop columns that may be owned by the original migrations.
    }
};
