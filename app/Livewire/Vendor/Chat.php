<?php

namespace App\Livewire\Vendor;

use App\Models\Conversation;
use App\Models\ChatMessage;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Customer Chats')]
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

        $shopUserId = Auth::guard('shop_user')->id();
        if (!$shopUserId) return;

        $conversation = Conversation::where('shop_user_id', $shopUserId)
            ->findOrFail($this->activeConversationId);

        $chatMessage = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'shop_user',
            'sender_id' => $shopUserId,
            'message' => trim($this->messageText),
            'is_read' => false,
        ]);

        $conversation->update([
            'last_message_at' => now(),
            // A human reply permanently takes this thread out of AI auto-reply mode.
            'is_ai_handled' => false,
        ]);

        try {
            broadcast(new MessageSent($chatMessage));
        } catch (\Throwable $e) {
            report($e);
        }

        $this->messageText = '';
        $this->dispatch('message-sent');
    }

    public function markAsRead()
    {
        if ($this->activeConversationId) {
            ChatMessage::where('conversation_id', $this->activeConversationId)
                ->where('sender_type', 'user')
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }
    }

    public function render()
    {
        $shopUserId = Auth::guard('shop_user')->id();

        $conversations = Conversation::where('shop_user_id', $shopUserId)
            ->with(['user', 'product.firstImage', 'latestMessage'])
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

        return view('livewire.vendor.chat', [
            'conversations' => $conversations,
            'messages' => $messages,
            'activeConversation' => $activeConversation,
        ]);
    }
}
