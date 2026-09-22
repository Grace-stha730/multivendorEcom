<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * MySQL and SQLite both let a unique index have multiple rows where any indexed column is
 * NULL (each NULL is treated as distinct), so the composite unique(shop_id, name) added by
 * 2026_09_22_000002 does not actually stop two admin categories (shop_id IS NULL) from
 * sharing a name at the DB level - only app-level validation was covering that case.
 * A generated column that coalesces NULL to 0 (never a real shop id, since ids start at 1)
 * closes the gap without changing how shop_id itself is read or written anywhere. It's VIRTUAL,
 * not STORED - MySQL 8 refuses to add a STORED generated column that reads an FK-constrained
 * column ("Cannot add foreign key constraint", a known MySQL limitation), but a VIRTUAL one
 * works and can still carry a unique index.
 *
 * Written defensively (checks each step before doing it) because every step here is its own
 * non-transactional DDL statement on MySQL.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!$this->indexExists('categories', 'categories_shop_id_index') && !$this->hasColumn('categories', 'shop_uniq_key')) {
            Schema::table('categories', function (Blueprint $table) {
                // categories.shop_id has a foreign key, which MySQL requires an index to back.
                // The composite unique(shop_id, name) index was the only thing covering it.
                $table->index('shop_id');
            });
        }

        if ($this->indexExists('categories', 'categories_shop_id_name_unique')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropUnique(['shop_id', 'name']);
            });
        }

        if (!Schema::hasColumn('categories', 'shop_uniq_key')) {
            DB::statement('ALTER TABLE categories ADD COLUMN shop_uniq_key BIGINT UNSIGNED GENERATED ALWAYS AS (COALESCE(shop_id, 0)) VIRTUAL');
        }

        if (!$this->indexExists('categories', 'categories_shop_uniq_key_name_unique')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->unique(['shop_uniq_key', 'name']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['shop_uniq_key', 'name']);
            $table->dropColumn('shop_uniq_key');
            $table->unique(['shop_id', 'name']);
            $table->dropIndex(['shop_id']);
        });
    }

    private function hasColumn(string $table, string $column): bool
    {
        return Schema::hasColumn($table, $column);
    }

    private function indexExists(string $table, string $indexName): bool
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return collect(DB::select("PRAGMA index_list($table)"))->contains('name', $indexName);
        }

        return collect(DB::select('SHOW INDEX FROM ' . $table . ' WHERE Key_name = ?', [$indexName]))->isNotEmpty();
    }
};
