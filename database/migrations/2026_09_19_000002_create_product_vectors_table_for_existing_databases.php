<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Compatibility migration for databases whose historical vector migration
     * is marked as run but whose product_vectors table was never created.
     */
    public function up(): void
    {
        if (Schema::hasTable('product_vectors')) {
            return;
        }

        Schema::create('product_vectors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained('products')->cascadeOnDelete();
            // Sparse TF-IDF map, e.g. {"gaming": 0.071921, "laptop": 0.071921}.
            $table->json('vector');
            $table->string('text_hash', 64);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Deliberately non-destructive; this table may be owned by an older migration.
    }
};
