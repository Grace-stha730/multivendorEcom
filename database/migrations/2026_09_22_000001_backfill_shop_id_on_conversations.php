<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * conversations.shop_id existed but was never populated (added as a safety-net column by
 * 2026_09_20_000003_add_shop_id_to_shop_scoped_tables). The vendor chat inbox was scoped to
 * shop_user_id instead, which meant a second staff member of the same shop saw zero
 * conversations. This backfills shop_id from the conversation's shop_user so every staff
 * member with chat-reply access shares the same shop inbox.
 */
return new class extends Migration {
    public function up(): void
    {
        // A correlated subquery (rather than a join+update) so this runs the same on both
        // MySQL and SQLite — the two drivers this project actually uses.
        DB::statement('
            update conversations
            set shop_id = (select shop_id from shop_users where shop_users.id = conversations.shop_user_id)
            where shop_id is null and shop_user_id is not null
        ');
    }

    public function down(): void
    {
        // Intentionally a no-op: this only fills in previously-NULL data, nothing to undo.
    }
};
