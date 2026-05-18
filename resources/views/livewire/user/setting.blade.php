<div class="max-w-5xl mx-auto py-8" x-data>
    <h2 class="text-2xl font-semibold mb-6 flex items-center gap-2">
        <i class="fa-solid fa-gear text-gray-700"></i>
        Admin Settings
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
                    @if ($photo)
                        <img class="w-24 h-24 rounded-full object-cover border shadow-sm"
                            src="{{ $photo->temporaryUrl() }}" alt="Admin Image">
                    @elseif(!$oldPhoto)
                        <img class="w-24 h-24 rounded-full object-cover border shadow-sm"
                            src="{{ asset('storage/default/vendor.jpg') }}" alt="">
                    @else
                        <img class="w-24 h-24 rounded-full object-cover border shadow-sm"
                            src="{{ asset('storage/' . $oldPhoto) }}" alt="">
                    @endif
                </div>

                <div class="flex-1">
                    <label class="block mb-2 text-sm font-medium text-gray-600">Change Profile Image</label>
                    <input type="file" wire:model="photo"
                        class="w-full border rounded-md px-3 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-blue-500">
                    @error('photo')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Name --}}
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-600">Admin Name</label>
                <input type="text" wire:model.defer="name"
                    class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500"
                    placeholder="Admin Name">
                @error('name')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            {{-- Email Address --}}
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-600">Email Address</label>
                <input type="email" wire:model.defer="email"
                    class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500"
                    placeholder="admin@example.com">
                @error('email')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            {{-- province --}}
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-600">Province</label>
                <input type="text" wire:model.defer="province"
                    class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500" placeholder="province">
                @error('province')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            {{-- city --}}
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-600">City</label>
                <input type="text" wire:model.defer="city"
                    class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500" placeholder="city">
                @error('city')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            {{-- tole --}}
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-600">Tole</label>
                <input type="text" wire:model.defer="tole"
                    class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500" placeholder="tole">
                @error('tole')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            {{-- phone --}}
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-600">phone</label>
                <input type="text" wire:model.defer="phone"
                    class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500" placeholder="phone">
                @error('phone')
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
