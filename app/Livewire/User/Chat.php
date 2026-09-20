<?php

namespace App\Livewire\User;

use App\Models\Conversation;
use App\Models\ChatMessage;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\ShopUser;
use App\Services\AiSupportService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('My Messages')]
#[Layout('components.layouts.user')]
class Chat extends Component
{
    public $activeConversationId = null;
    public $messageText = '';

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

        if (str_contains(mb_strtolower($chatMessage->message), 'talk to a human')) {
            $conversation->update(['is_ai_handled' => false]);
        } elseif ($conversation->is_ai_handled && $conversation->product_id === null) {
            $key = "ai:chatbot:$userId";
            if (! RateLimiter::tooManyAttempts($key, 30)) {
                RateLimiter::hit($key, 60);
                app(AiSupportService::class)->sendReply($conversation->fresh());
            }
        }

        try {
            broadcast(new MessageSent($chatMessage));
        } catch (\Throwable $e) {
            report($e);
        }

        $this->messageText = '';
        $this->dispatch('message-sent');
    }

    public function startAiSupport(): void
    {
        $userId = Auth::guard('web')->id();
        $shopUser = ShopUser::query()->first();
        if (! $userId || ! $shopUser) { session()->flash('error', 'AI support is not available yet.'); return; }
        $conversation = Conversation::firstOrCreate(
            ['user_id' => $userId, 'shop_user_id' => $shopUser->id, 'product_id' => null],
            ['last_message_at' => now(), 'is_ai_handled' => true],
        );
        $conversation->update(['is_ai_handled' => true]);
        $this->selectConversation($conversation->id);
    }

    public function requestHuman(): void
    {
        $conversation = Conversation::where('user_id', Auth::guard('web')->id())->findOrFail($this->activeConversationId);
        $conversation->update(['is_ai_handled' => false]);
        session()->flash('success', 'A human support team member will assist you shortly.');
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
