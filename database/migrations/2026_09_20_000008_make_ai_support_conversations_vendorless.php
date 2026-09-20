<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The AI Assistant chat used to be attached to whichever shop user had the lowest id, which put every
 * customer's AI conversation in that vendor's inbox. AI-only chats now have NO vendor (shop_user_id NULL).
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('conversations', 'human_requested_at')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->timestamp('human_requested_at')->nullable();
            });
        }

        // shop_user_id must allow NULL. Drop and re-add the FK around the change so MySQL accepts it.
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['shop_user_id']);
        });
        Schema::table('conversations', function (Blueprint $table) {
            $table->unsignedBigInteger('shop_user_id')->nullable()->change();
        });
        Schema::table('conversations', function (Blueprint $table) {
            $table->foreign('shop_user_id')->references('id')->on('shop_users')->cascadeOnDelete();
        });

        $this->detachLegacyAiConversations();
    }

    /** Old AI Assistant chats: no product, attached to the first shop user, and answered by the bot. */
    private function detachLegacyAiConversations(): void
    {
        $firstShopUserId = DB::table('shop_users')->min('id');
        if (!$firstShopUserId) {
            return;
        }

        DB::table('conversations')
            ->whereNull('product_id')
            ->where('shop_user_id', $firstShopUserId)
            ->whereExists(fn ($q) => $q->select(DB::raw(1))->from('chat_messages')
                ->whereColumn('chat_messages.conversation_id', 'conversations.id')
                ->where('chat_messages.sender_type', 'ai_bot'))
            ->update(['shop_user_id' => null, 'is_ai_handled' => true]);
    }

    public function down(): void
    {
        // Intentionally not reversible: vendor-less AI chats can't be given a vendor again without guessing one.
    }
};
