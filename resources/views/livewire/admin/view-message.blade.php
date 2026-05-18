<div class="p-6">
    <div class="container mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-3xl font-bold text-gray-800">Message Detail</h2>
            <a href="{{ route('admin.message') }}"
                class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Back to Messages</a>
        </div>

        <!-- Message Card -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-2xl font-semibold text-gray-800">{{ $message->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $message->email }}</p>
                </div>
                @if ($message->is_read)
                    <span class="text-sm text-green-600 font-medium">Read</span>
                @else
                    <span class="text-sm text-red-600 font-medium">New</span>
                @endif
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-700">
                    <span class="font-semibold">User Type:</span>
                    @if ($message->user_id)
                        Logged in User
                    @else
                        Guess User
                    @endif
                </p>
                <p class="text-sm text-gray-400"><span class="font-semibold">Date:</span>
                    {{ $message->created_at->format('Y-m-d') }}</p>
            </div>

            <div>
                <p class="text-gray-700">
                    <span class="font-semibold text-lg me-2">Subject:</span>
                    {{ $message->subject }}
                </p>
            </div>
            <div class="mb-4 border-t pt-4">
                <h4 class="text-lg font-semibold text-gray-800 mb-2">Message</h4>
                <p class="text-gray-700 leading-relaxed">
                    {{ $message->message }}
                </p>
            </div>
            <div>
                @if ($message->is_read)
                    <button
                        class=" cursor-pointer mt-3 w-full bg-gray-400 text-white py-1 rounded hover:bg-gray-500 text-sm"
                        wire:click='markAsUnread'>
                        Mark as Unread
                    </button>
                @else
                    <button
                        class=" cursor-pointer mt-3 w-full bg-blue-600 text-white py-1 rounded hover:bg-blue-700 text-sm"
                        wire:click='markAsRead'>
                        Mark as Read
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
