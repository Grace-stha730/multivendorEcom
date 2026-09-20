<?php

namespace App\Console\Commands;

use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Services\AiSupportService;
use Illuminate\Console\Command;

class ReplyToPendingAiSupport extends Command
{
    protected $signature = 'ai:reply-to-pending-support';
    protected $description = 'Send delayed AI first responses for opted-in shops';
    public function handle(AiSupportService $ai): int
    {
        $cutoff = now()->subMinutes(config('ai.auto_reply_delay_minutes'));
        Conversation::with('shopUser.shop')->where('is_ai_handled', true)->where('last_message_at', '<=', $cutoff)->eachById(function (Conversation $conversation) use ($ai) {
            if (! $conversation->shopUser?->shop?->ai_auto_reply_enabled) return;
            $last = ChatMessage::where('conversation_id', $conversation->id)->latest()->first();
            if ($last?->sender_type === 'user') $ai->sendReply($conversation);
        });
        return self::SUCCESS;
    }
}
