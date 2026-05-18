<div class="max-w-5xl mx-auto py-8" x-data>
    <h2 class="text-2xl font-semibold mb-6 flex items-center gap-2">
        <i class="fa-solid fa-gear text-gray-700"></i>
        Vendor Settings
    </h2>

    {{-- Profile Settings --}}
    <div class="bg-white p-6 rounded-2xl shadow">
        <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
            <i class="fa-solid fa-user-cog text-gray-600"></i>
            Profile Settings
        </h3>

        <form wire:submit.prevent="updateProfile" class="space-y-4">
            {{-- Profile Image --}}
            <div class="flex flex-col md:flex-row items-center md:items-start gap-4">
                <div>
                    @if ($shop_image)
                        <img class="w-24 h-24 rounded-full object-cover border shadow-sm"
                            src="{{ $shop_image->temporaryUrl() }}" alt="Admin Image">
                    @elseif(!$oldImage)
                        <img class="w-24 h-24 rounded-full object-cover border shadow-sm"
                            src="{{ asset('storage/default/vendor.jpg') }}" alt="">
                    @else
                        <img class="w-24 h-24 rounded-full object-cover border shadow-sm"
                            src="{{ asset('storage/' . $oldImage) }}" alt="">
                    @endif
                </div>

                <div class="flex-1">
                    <label class="block mb-2 text-sm font-medium text-gray-600">Change Profile Image</label>
                    <input type="file" wire:model="shop_image"
                        class="w-full border rounded-md px-3 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-blue-500">
                    @error('shop_image')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- vendor Name --}}
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-600">Shop Name</label>
                <input type="text" wire:model.defer="shop_name"
                    class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500" placeholder="Shop Name">
                @error('shop_name')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-600">Owner Name</label>
                <input type="text" wire:model.defer="owner_name"
                    class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500"
                    placeholder="Admin Name">
                @error('owner_name')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            {{-- Email Address --}}
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-600">Email Address</label>
                <input type="email" wire:model.defer="shop_email"
                    class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500"
                    placeholder="vendor@example.com">
                @error('shop_email')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            {{-- province --}}
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-600">Province</label>
                <input type="text" wire:model.defer="shop_province"
                    class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500"
                    placeholder="Shop Province">
                @error('shop_province')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            {{-- city --}}
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-600">City</label>
                <input type="text" wire:model.defer="shop_city"
                    class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500" placeholder="Shop city">
                @error('shop_city')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            {{-- tole --}}
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-600">City</label>
                <input type="text" wire:model.defer="shop_tole"
                    class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500" placeholder="Shop tole">
                @error('shop_tole')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            {{-- tole --}}
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-600">phone</label>
                <input type="number" wire:model.defer="shop_phone"
                    class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500" placeholder="phone">
                @error('shop_phone')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            {{-- New Password --}}
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-600">New Password</label>
                <input type="password" wire:model.defer="password"
                    class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500" placeholder="••••••••">
                @error('password')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            {{-- Confirm new password --}}
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-600">Confirm New Password</label>
                <input type="password" wire:model.defer="newPassword"
                    class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500" placeholder="••••••••">
                @error('newPassword')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>


            {{-- Submit --}}
            <div class="text-end">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2 rounded-md transition cursor-pointer  ">
                    Update Profile
                </button>
            </div>

            {{-- Loading Indicator --}}
            <div wire:loading wire:target="updateProfile" class="text-gray-500 text-sm mt-2">
                Updating your profile, please wait...
            </div>
        </form>
    </div>
</div>
