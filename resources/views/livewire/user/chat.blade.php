<div class="max-w-6xl mx-auto px-4 py-8" wire:poll.5s="markAsRead">
    <h1 class="text-3xl font-bold mb-6 text-gray-800 flex items-center gap-2">
        <i class="fa-solid fa-comments text-indigo-600"></i> Chat Messages
    </h1>

    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden flex flex-col md:flex-row h-[600px]">
        <!-- Conversations Sidebar -->
        <div class="w-full md:w-80 border-r border-gray-100 flex flex-col h-full bg-gray-50/50">
            <div class="p-4 border-b border-gray-100 bg-white">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Conversations</span>
            </div>
            
            <div class="flex-1 overflow-y-auto divide-y divide-gray-50">
                @forelse ($conversations as $conv)
                    @php
                        $unreadCount = $conv->messages->where('sender_type', 'vendor')->where('is_read', false)->count();
                    @endphp
                    <button wire:click="selectConversation({{ $conv->id }})"
                        class="w-full text-left p-4 flex items-start gap-3 transition cursor-pointer hover:bg-gray-50 {{ $activeConversationId === $conv->id ? 'bg-indigo-50/70 border-l-4 border-indigo-600' : '' }}">
                        
                        <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm flex-shrink-0">
                            {{ substr($conv->vendor->shop_name ?? 'V', 0, 2) }}
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-baseline mb-0.5">
                                <h4 class="text-sm font-semibold text-gray-900 truncate">
                                    {{ $conv->vendor->shop_name ?? 'Vendor' }}
                                </h4>
                                @if($conv->last_message_at)
                                    <span class="text-[10px] text-gray-400">
                                        {{ $conv->last_message_at->diffForHumans(null, true, true) }}
                                    </span>
                                @endif
                            </div>

                            @if($conv->product)
                                <div class="flex items-center gap-1 text-[11px] text-indigo-600 font-medium mb-1">
                                    <i class="fa-solid fa-bag-shopping text-[9px]"></i>
                                    <span class="truncate">{{ $conv->product->name }}</span>
                                </div>
                            @endif

                            <p class="text-xs text-gray-500 truncate">
                                @if($conv->latestMessage)
                                    {{ $conv->latestMessage->sender_type === 'user' ? 'You: ' : '' }}{{ $conv->latestMessage->message }}
                                @else
                                    No messages yet.
                                @endif
                            </p>
                        </div>

                        @if ($unreadCount > 0)
                            <span class="bg-indigo-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full mt-1.5">
                                {{ $unreadCount }}
                            </span>
                        @endif
                    </button>
                @empty
                    <div class="p-8 text-center text-gray-500 text-sm">
                        <i class="fa-regular fa-comments text-3xl text-gray-300 block mb-2"></i>
                        No conversations yet.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Chat Area -->
        <div class="flex-1 flex flex-col h-full bg-white relative">
            @if ($activeConversation)
                <!-- Chat Header -->
                <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-white z-10">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm">
                            {{ substr($activeConversation->vendor->shop_name ?? 'V', 0, 2) }}
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">
                                {{ $activeConversation->vendor->shop_name }}
                            </h3>
                            <p class="text-xs text-gray-400">Direct Vendor Inquiry</p>
                        </div>
                    </div>

                    @if ($activeConversation->product)
                        <a href="{{ route('product.detail', ['id' => $activeConversation->product->id]) }}" 
                            class="flex items-center gap-2 p-1.5 pr-3 bg-gray-50 border border-gray-100 hover:bg-gray-100 rounded-lg text-xs transition">
                            <img src="{{ asset('storage/' . ($activeConversation->product->firstImage->url ?? 'default/product.webp')) }}" 
                                class="w-6 h-6 rounded object-cover">
                            <span class="font-medium text-gray-700 max-w-[150px] truncate">
                                {{ $activeConversation->product->name }}
                            </span>
                        </a>
                    @endif
                </div>

                <!-- Messages List -->
                <div class="flex-1 p-6 overflow-y-auto bg-gray-50/30 flex flex-col gap-4" id="chat-messages" x-init="$el.scrollTop = $el.scrollHeight" x-effect="$nextTick(() => { $el.scrollTop = $el.scrollHeight })">
                    @forelse ($messages as $msg)
                        <div @class([
                            'flex flex-col max-w-[70%]',
                            'self-end' => $msg->sender_type === 'user',
                            'self-start' => $msg->sender_type === 'vendor',
                        ])>
                            <div @class([
                                'p-3.5 rounded-2xl text-sm shadow-sm',
                                'bg-indigo-600 text-white rounded-br-none' => $msg->sender_type === 'user',
                                'bg-white text-gray-800 border border-gray-100 rounded-bl-none' => $msg->sender_type === 'vendor',
                            ])>
                                {{ $msg->message }}
                            </div>
                            <span @class([
                                'text-[10px] text-gray-400 mt-1',
                                'text-right' => $msg->sender_type === 'user',
                                'text-left' => $msg->sender_type === 'vendor',
                            ])>
                                {{ $msg->created_at->format('g:i A') }}
                                @if ($msg->sender_type === 'user')
                                    <span class="ml-1 text-indigo-400">
                                        @if($msg->is_read)
                                            <i class="fa-solid fa-check-double"></i> Read
                                        @else
                                            <i class="fa-solid fa-check"></i> Sent
                                        @endif
                                    </span>
                                @endif
                            </span>
                        </div>
                    @empty
                        <div class="flex-1 flex flex-col items-center justify-center text-gray-500">
                            <p class="text-sm">Start your conversation. Type a message below!</p>
                        </div>
                    @endforelse
                </div>

                <!-- Chat Footer / Input Form -->
                <div class="p-4 border-t border-gray-100 bg-white">
                    <form wire:submit.prevent="sendMessage" class="flex gap-2">
                        <input type="text" wire:model.defer="messageText" placeholder="Type your message here..."
                            class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <button type="submit" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-1.5 shadow-md">
                            <span>Send</span>
                            <i class="fa-solid fa-paper-plane text-xs"></i>
                        </button>
                    </form>
                    @error('messageText')
                        <small class="text-red-500 mt-1 block">{{ $message }}</small>
                    @enderror
                </div>
            @else
                <div class="flex-1 flex flex-col items-center justify-center text-gray-400 p-8 text-center bg-gray-50/20">
                    <i class="fa-solid fa-comments text-5xl text-gray-200 mb-4"></i>
                    <h3 class="text-lg font-bold text-gray-700">No Chat Selected</h3>
                    <p class="text-sm mt-1 max-w-xs text-gray-500">
                        Choose a conversation from the sidebar or click "Ask Vendor a Question" on any product detail page.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
