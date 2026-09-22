<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (! Schema::hasTable('ai_usage_logs')) Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id(); $table->string('feature'); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shop_user_id')->nullable()->constrained('shop_users')->nullOnDelete();
            $table->unsignedInteger('tokens_used')->nullable(); $table->timestamp('created_at')->useCurrent();
            $table->index(['feature', 'created_at']);
        });
        if (! Schema::hasTable('store_policies')) Schema::create('store_policies', function (Blueprint $table) { $table->id(); $table->string('key')->unique(); $table->text('value'); $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate(); });
        if (! Schema::hasTable('product_recommendation_blurbs')) Schema::create('product_recommendation_blurbs', function (Blueprint $table) {
            $table->id(); $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_product_id')->constrained('products')->cascadeOnDelete();
            $table->string('blurb_text', 300); $table->timestamp('generated_at');
        });
        // MySQL limits identifiers to 64 characters; use an explicit compact name.
        // Schema::getIndexes() (not "SHOW INDEX", which is MySQL-only) so this also works on SQLite.
        $hasPairIndex = collect(Schema::getIndexes('product_recommendation_blurbs'))->contains('name', 'prd_rec_blurbs_pair_uq');
        if (! $hasPairIndex) Schema::table('product_recommendation_blurbs', fn (Blueprint $table) => $table->unique(['product_id', 'related_product_id'], 'prd_rec_blurbs_pair_uq'));
        if (!Schema::hasColumn('conversations', 'is_ai_handled')) Schema::table('conversations', fn (Blueprint $t) => $t->boolean('is_ai_handled')->default(false)->index());
        if (!Schema::hasColumn('shops', 'ai_auto_reply_enabled')) Schema::table('shops', fn (Blueprint $t) => $t->boolean('ai_auto_reply_enabled')->default(false));
        // ENUM is MySQL-only syntax; SQLite has no enum type, and any string column already
        // accepts these values without needing an equivalent constraint there.
        if (Schema::hasTable('chat_messages') && DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE chat_messages MODIFY sender_type ENUM('user', 'shop_user', 'ai_bot')");
        }
    }
    public function down(): void { Schema::dropIfExists('product_recommendation_blurbs'); Schema::dropIfExists('store_policies'); Schema::dropIfExists('ai_usage_logs'); }
};
