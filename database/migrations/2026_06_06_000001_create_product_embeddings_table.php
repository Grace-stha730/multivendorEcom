<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_vectors', function (Blueprint $table) {
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
        Schema::dropIfExists('product_vectors');
    }
};
