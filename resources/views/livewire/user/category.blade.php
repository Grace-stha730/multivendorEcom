<section class="w-[90%] md:w-[80%] mx-auto text-center my-8">
    <h2 class="text-2xl md:text-3xl font-semibold text-gray-800 mb-6 text-center">
        Categories
    </h2>
    <div class="grid grid-cols-3 md:grid-cols-5 justify-center">
        @foreach ($categories as $category)
            <div wire:click.prevent="catProduct({{ $category->id }})" class="bg-gray-800 px-3 py-2 m-1 text-white rounded-md hover:scale-105 duration-200 cursor-pointer">
                <button class="cursor-pointer">{{ $category->name }}</button>
            </div>
        @endforeach
    </div>
</section>