<?php

namespace App\Services;

use App\Models\AiUsageLog;
use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\StorePolicy;
use App\Exceptions\AiRequestException;

class AiSupportService
{
    public function __construct(private AiClientService $client) {}

    public function reply(Conversation $conversation): string
    {
        try {
            $conversation->loadMissing(['product', 'user', 'messages']);
            $context = StorePolicy::query()->pluck('value', 'key')->map(fn ($v, $k) => "$k: $v")->implode("\n");
            if ($conversation->product) {
                $p = $conversation->product;
                $context .= "\nProduct: {$p->name}; price: {$p->price}; stock: {$p->stock}; description: {$p->description}";
            }
            // Gemini free-tier content may be used for service improvement. Never
            // include orders, addresses, contacts, or other customer data here.
            $history = $conversation->messages->take(-12)->map(fn ($m) => "{$m->sender_type}: {$m->message}")->implode("\n");
            $result = $this->client->generate(
                'You are a marketplace support assistant. Use only supplied context. Be concise and helpful; never expose customer data. You may always explain this known ordering flow: open a product page, choose options and quantity, press “Order Now” (or add it to cart), then complete checkout. Do not claim an item is available unless its supplied context says so. Offer human support only when needed, not as the default response.',
                "Policies and context:\n$context\n\nConversation:\n$history", 400,
            );
            AiUsageLog::create(['feature' => 'chatbot', 'user_id' => $conversation->user_id, 'tokens_used' => $result['tokens'], 'created_at' => now()]);
            return $result['text'];
        } catch (AiRequestException $exception) {
            report($exception);
            return 'A team member will assist you shortly.';
        }
    }

    public function sendReply(Conversation $conversation): ChatMessage
    {
        $message = ChatMessage::create(['conversation_id' => $conversation->id, 'sender_type' => 'ai_bot', 'sender_id' => 0, 'message' => $this->reply($conversation), 'is_read' => false]);
        $conversation->update(['last_message_at' => now()]);
        return $message;
    }
}
