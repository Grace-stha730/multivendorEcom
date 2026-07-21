<div class="p-6 bg-gray-100 min-h-screen" wire:poll.5s="markAsRead">
    <div class="max-w-6xl mx-auto">
        <h2 class="text-2xl font-semibold text-gray-800 flex items-center gap-2 mb-6">
            <i class="fa-solid fa-comments text-emerald-600"></i> Customer Inquiries & Chats
        </h2>

        <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden flex flex-col md:flex-row h-[600px]">
            <!-- Conversations Sidebar -->
            <div class="w-full md:w-80 border-r border-gray-100 flex flex-col h-full bg-gray-50/50">
                <div class="p-4 border-b border-gray-100 bg-white">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Chats</span>
                </div>
                
                <div class="flex-1 overflow-y-auto divide-y divide-gray-50">
                    @forelse ($conversations as $conv)
                        @php
                            $unreadCount = $conv->messages->where('sender_type', 'user')->where('is_read', false)->count();
                        @endphp
                        <button wire:click="selectConversation({{ $conv->id }})"
                            class="w-full text-left p-4 flex items-start gap-3 transition cursor-pointer hover:bg-gray-50 {{ $activeConversationId === $conv->id ? 'bg-emerald-50/70 border-l-4 border-emerald-500' : '' }}">
                            
                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm flex-shrink-0">
                                {{ substr($conv->user->name ?? 'C', 0, 2) }}
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-baseline mb-0.5">
                                    <h4 class="text-sm font-semibold text-gray-900 truncate">
                                        {{ $conv->user->name ?? 'Customer' }}
                                    </h4>
                                    @if($conv->last_message_at)
                                        <span class="text-[10px] text-gray-400">
                                            {{ $conv->last_message_at->diffForHumans(null, true, true) }}
                                        </span>
                                    @endif
                                </div>

                                @if($conv->product)
                                    <div class="flex items-center gap-1 text-[11px] text-emerald-600 font-medium mb-1">
                                        <i class="fa-solid fa-bag-shopping text-[9px]"></i>
                                        <span class="truncate">{{ $conv->product->name }}</span>
                                    </div>
                                @endif

                                <p class="text-xs text-gray-500 truncate">
                                    @if($conv->latestMessage)
                                        {{ $conv->latestMessage->sender_type === 'vendor' ? 'You: ' : '' }}{{ $conv->latestMessage->message }}
                                    @else
                                        No messages yet.
                                    @endif
                                </p>
                            </div>

                            @if ($unreadCount > 0)
                                <span class="bg-emerald-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full mt-1.5">
                                    {{ $unreadCount }}
                                </span>
                            @endif
                        </button>
                    @empty
                        <div class="p-8 text-center text-gray-500 text-sm">
                            <i class="fa-regular fa-comments text-3xl text-gray-300 block mb-2"></i>
                            No customer inquiries yet.
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
                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                                {{ substr($activeConversation->user->name ?? 'C', 0, 2) }}
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">
                                    {{ $activeConversation->user->name }}
                                </h3>
                                <p class="text-xs text-gray-400">Customer Inquiry</p>
                            </div>
                        </div>

                        @if ($activeConversation->product)
                            <div class="flex items-center gap-2 p-1.5 pr-3 bg-gray-50 border border-gray-100 rounded-lg text-xs">
                                <img src="{{ asset('storage/' . ($activeConversation->product->firstImage->url ?? 'default/product.webp')) }}" 
                                    class="w-6 h-6 rounded object-cover">
                                <span class="font-medium text-gray-700 max-w-[150px] truncate">
                                    {{ $activeConversation->product->name }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <!-- Messages List -->
                    <div class="flex-1 p-6 overflow-y-auto bg-gray-50/30 flex flex-col gap-4" id="chat-messages" x-init="$el.scrollTop = $el.scrollHeight" x-effect="$nextTick(() => { $el.scrollTop = $el.scrollHeight })">
                        @forelse ($messages as $msg)
                            <div @class([
                                'flex flex-col max-w-[70%]',
                                'self-end' => $msg->sender_type === 'vendor',
                                'self-start' => $msg->sender_type === 'user',
                            ])>
                                <div @class([
                                    'p-3.5 rounded-2xl text-sm shadow-sm',
                                    'bg-emerald-600 text-white rounded-br-none' => $msg->sender_type === 'vendor',
                                    'bg-white text-gray-800 border border-gray-100 rounded-bl-none' => $msg->sender_type === 'user',
                                ])>
                                    {{ $msg->message }}
                                </div>
                                <span @class([
                                    'text-[10px] text-gray-400 mt-1',
                                    'text-right' => $msg->sender_type === 'vendor',
                                    'text-left' => $msg->sender_type === 'user',
                                ])>
                                    {{ $msg->created_at->format('g:i A') }}
                                    @if ($msg->sender_type === 'vendor')
                                        <span class="ml-1 text-emerald-400">
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
                                <p class="text-sm">No messages yet. Send a message to response.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Chat Footer / Input Form -->
                    <div class="p-4 border-t border-gray-100 bg-white">
                        <form wire:submit.prevent="sendMessage" class="flex gap-2">
                            <input type="text" wire:model.defer="messageText" placeholder="Type your reply here..."
                                class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            <button type="submit" 
                                class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-1.5 shadow-md">
                                <span>Reply</span>
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
                            Choose a conversation from the sidebar to view messages and respond to customer questions.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
