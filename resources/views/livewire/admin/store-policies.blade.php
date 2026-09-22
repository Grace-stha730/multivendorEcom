<div class="mx-auto max-w-3xl p-6">
    <h1 class="text-2xl font-bold text-gray-800">AI Support Policies</h1>
    <p class="mt-1 text-sm text-gray-500">These are supplied to the support assistant. Keep them accurate and customer-safe.</p>
    {{-- The admin layout already includes common.message, which shows session('success') as a Mary toast. --}}
    <form wire:submit="save" class="mt-6 space-y-5 rounded-xl bg-white p-6 shadow">
        @foreach (['shipping' => 'Shipping policy', 'returns' => 'Returns policy', 'general' => 'General store policy'] as $field => $label)
            <div><label class="mb-1 block text-sm font-semibold text-gray-700">{{ $label }}</label><textarea wire:model="{{ $field }}" rows="5" class="w-full rounded border-gray-300"></textarea>@error($field)<p class="text-sm text-red-600">{{ $message }}</p>@enderror</div>
        @endforeach
        <button class="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Save policies</button>
    </form>
</div>
