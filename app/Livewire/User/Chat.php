<?php

namespace App\Livewire\User;

use App\Models\Conversation;
use App\Models\ChatMessage;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\AiSupportService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('My Messages')]
#[Layout('components.layouts.user')]
class Chat extends Component
{
    use Toast;

    public $activeConversationId = null;
    public $messageText = '';
    public bool $awaitingAi = false;

    protected $queryString = ['activeConversationId' => ['except' => null, 'as' => 'c']];

    public function getListeners()
    {
        if ($this->activeConversationId) {
            return [
                "echo-private:chat.{$this->activeConversationId},MessageSent" => 'onMessageReceived',
            ];
        }
        return [];
    }

    public function onMessageReceived($event)
    {
        $this->markAsRead();
    }

    public function selectConversation($id)
    {
        $this->activeConversationId = $id;
        $this->markAsRead();
        $this->resetErrorBag();
        $this->messageText = '';
    }

    public function sendMessage()
    {
        $this->validate([
            'messageText' => 'required|string|max:1000',
        ]);

        $userId = Auth::guard('web')->id();
        if (!$userId) return;

        $conversation = Conversation::where('user_id', $userId)
            ->findOrFail($this->activeConversationId);

        $chatMessage = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'user',
            'sender_id' => $userId,
            'message' => trim($this->messageText),
            'is_read' => false,
        ]);

        $conversation->update([
            'last_message_at' => now(),
        ]);

        $wantsHuman = str_contains(mb_strtolower($chatMessage->message), 'talk to a human');
        $aiShouldReply = false;

        if ($wantsHuman) {
            $conversation->isAiSupport()
                ? $this->escalateToHuman($conversation)                  // no vendor to hand over to: notify the admin
                : $conversation->update(['is_ai_handled' => false]);     // vendor chat: the vendor takes over
        } elseif ($conversation->is_ai_handled && $conversation->product_id === null) {
            $aiShouldReply = true;
        }

        try {
            broadcast(new MessageSent($chatMessage));
        } catch (\Throwable $e) {
            report($e);
        }

        $this->messageText = '';
        $this->dispatch('message-sent');

        if ($aiShouldReply) {
            // The customer's message is already saved and on screen. The slow AI call happens in a
            // second request (generateAiReply) so the page never freezes waiting for it.
            $this->awaitingAi = true;
            $this->dispatch('request-ai-reply');
        }
    }

    /** Second step of a chatbot turn: ask the AI to answer the customer's latest message. */
    public function generateAiReply(): void
    {
        $userId = Auth::guard('web')->id();
        $conversation = $userId && $this->activeConversationId
            ? Conversation::where('user_id', $userId)->find($this->activeConversationId)
            : null;

        $lastMessage = $conversation?->messages()->latest('id')->first();

        // Nothing to do unless this is an AI-handled chat whose latest message is the customer's.
        if (!$conversation || !$conversation->is_ai_handled || $conversation->product_id !== null || $lastMessage?->sender_type !== 'user') {
            $this->awaitingAi = false;

            return;
        }

        $key = "ai:chatbot:$userId";
        if (RateLimiter::tooManyAttempts($key, 30)) {
            $this->awaitingAi = false;
            $this->error('Slow down', 'You are sending messages too quickly. Please wait a moment.', 'toast-bottom toast-end');

            return;
        }

        // One AI answer per customer message, even if the browser fires the request twice.
        $lock = Cache::lock("ai-reply:{$conversation->id}", 60);
        if (!$lock->get()) {
            return;
        }

        try {
            RateLimiter::hit($key, 60);
            $reply = app(AiSupportService::class)->sendReply($conversation->fresh());

            try {
                broadcast(new MessageSent($reply));
            } catch (\Throwable $e) {
                report($e);
            }
        } finally {
            $lock->release();
            $this->awaitingAi = false;
        }

        $this->dispatch('message-sent');
    }

    /**
     * The AI-only chat has no vendor, so "talk to a human" goes to the admin's Messages inbox
     * (once per 24 hours), and the assistant tells the customer what happened.
     */
    private function escalateToHuman(Conversation $conversation): void
    {
        $alreadyNotified = $conversation->human_requested_at?->gt(now()->subDay());

        if (!$alreadyNotified) {
            $user = Auth::guard('web')->user();
            $transcript = $conversation->messages()->latest('id')->take(15)->get()->reverse()
                ->map(fn ($m) => ($m->sender_type === 'user' ? 'Customer' : 'AI Assistant') . ': ' . $m->message)
                ->implode("\n");

            ContactMessage::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'subject' => 'Support request from AI chat',
                'message' => "The customer asked to talk to a human.\n\nRecent conversation:\n{$transcript}",
                'is_read' => false,
            ]);

            $conversation->update(['human_requested_at' => now()]);
        }

        $reply = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'ai_bot',
            'sender_id' => 0,
            'message' => $alreadyNotified
                ? 'Our support team has already been notified and will contact you by email. I can keep helping in the meantime.'
                : 'I have passed your request to our support team. They will contact you by email soon. I can keep helping in the meantime.',
            'is_read' => false,
        ]);
        $conversation->update(['last_message_at' => now()]);

        try {
            broadcast(new MessageSent($reply));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function startAiSupport(): void
    {
        $userId = Auth::guard('web')->id();
        if (! $userId) { $this->error('Please log in', 'Log in to use AI support.', 'toast-bottom toast-end'); return; }

        // The AI Assistant chat belongs to the customer only: no vendor (shop_user_id NULL), no product.
        $conversation = Conversation::firstOrCreate(
            ['user_id' => $userId, 'shop_user_id' => null, 'product_id' => null],
            ['last_message_at' => now(), 'is_ai_handled' => true],
        );
        $conversation->update(['is_ai_handled' => true]);
        $this->selectConversation($conversation->id);
    }

    public function requestHuman(): void
    {
        $conversation = Conversation::where('user_id', Auth::guard('web')->id())->findOrFail($this->activeConversationId);

        if ($conversation->isAiSupport()) {
            $this->escalateToHuman($conversation);
            $this->success('Support notified', 'Our support team has been notified and will contact you by email.', 'toast-bottom toast-end');

            return;
        }

        $conversation->update(['is_ai_handled' => false]);
        $this->success('Request sent', 'A human support team member will assist you shortly.', 'toast-bottom toast-end');
    }

    public function markAsRead()
    {
        if ($this->activeConversationId) {
            ChatMessage::where('conversation_id', $this->activeConversationId)
                ->whereIn('sender_type', ['shop_user', 'ai_bot'])
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }
    }

    public function render()
    {
        $userId = Auth::guard('web')->id();

        $conversations = Conversation::where('user_id', $userId)
            ->with(['shopUser.shop', 'product.firstImage', 'latestMessage'])
            ->orderByRaw('last_message_at IS NULL, last_message_at DESC')
            ->orderBy('updated_at', 'desc')
            ->get();

        $messages = [];
        $activeConversation = null;

        if ($this->activeConversationId) {
            $activeConversation = $conversations->firstWhere('id', $this->activeConversationId);
            if ($activeConversation) {
                $messages = ChatMessage::where('conversation_id', $this->activeConversationId)
                    ->oldest()
                    ->get();
                $this->markAsRead();
            } else {
                $this->activeConversationId = null;
            }
        }

        return view('livewire.user.chat', [
            'conversations' => $conversations,
            'messages' => $messages,
            'activeConversation' => $activeConversation,
        ]);
    }
}
