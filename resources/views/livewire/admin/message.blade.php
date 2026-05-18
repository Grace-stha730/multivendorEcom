<div class="min-h-screen bg-gray-100 p-6">
    <div class="container mx-auto">
        <h2 class="text-3xl font-bold text-gray-800 mb-6">User Messages</h2>

        @if ($messages->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Message Card 1 -->
                @foreach ($messages as $message)
                    <div class="bg-white shadow-md rounded-lg p-5">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="text-lg font-semibold text-gray-800">{{ $message->name }}</h3>
                            @if ($message->is_read)
                                <span class="text-sm text-green-600 font-medium">Read</span>
                            @else
                                <span class="text-sm text-red-600 font-medium">New</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500 mb-2">{{ $message->email }}</p>
                        <p class="text-sm text-gray-700 mb-3">
                            <span class="font-semibold">User Type:</span>
                            @if ($message->user_id)
                                Logged in User
                            @else
                                Guest User
                            @endif
                        </p>

                        <p class="text-gray-700 text-sm mb-3"><span class="font-semibold">Subject:</span>
                            {{ $message->subject }}</p>
                        <p class="text-gray-700 text-sm mb-3">
                            {{ \Illuminate\Support\Str::limit($message->message, 100, '...') }}</p>
                        <p class="text-xs text-gray-400">{{ $message->created_at->format('Y-m-d') }}</p>
                        @if ($message->is_read)
                            <button class=" cursor-pointer mt-3 w-full bg-gray-400 text-white py-1 rounded hover:bg-gray-500 text-sm" wire:click='markAsUnread({{ $message->id }})'>
                                Mark as Unread
                            </button>
                        @else
                            <button class=" cursor-pointer mt-3 w-full bg-blue-600 text-white py-1 rounded hover:bg-blue-700 text-sm" wire:click='markAsRead({{$message->id}})'>
                                Mark as Read
                            </button>
                        @endif
                        <a href="{{ route('admin.message-datail',['id' => $message->id]) }}" class="block text-center cursor-pointer mt-3 w-full bg-green-700 text-white py-1 rounded hover:bg-green-800 text-sm">
                            View Full Mesage
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center w-full text-gray-500 text-xl">No Message</div>
        @endif


    </div>
</div>
