<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Safety net: every table that holds shop-owned data must carry shop_id so shop-side
 * queries can be scoped. Most already do; this only adds the column where it is missing.
 */
return new class extends Migration {
    private array $tables = [
        'products', 'categories', 'coupons', 'vendor_orders', 'vendor_payouts',
        'conversations', 'ai_usage_logs',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (!Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'shop_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('shop_id')->nullable()->constrained('shops')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        // Intentionally a no-op: most of these columns pre-date this migration,
        // so dropping them here would destroy data this migration never created.
    }
};
