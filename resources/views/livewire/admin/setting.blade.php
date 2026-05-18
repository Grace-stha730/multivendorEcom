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
                    @if ($image)
                        <img class="w-24 h-24 rounded-full object-cover border shadow-sm"
                            src="{{ $image->temporaryUrl() }}" alt="Admin Image">
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
                    <input type="file" wire:model="image"
                        class="w-full border rounded-md px-3 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-blue-500">
                    @error('newImage')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Admin Name --}}
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

            {{-- Department --}}
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-600">Department</label>
                <input type="text" wire:model.defer="department"
                    class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500"
                    placeholder="department">
                @error('department')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-600">phone</label>
                <input type="text" wire:model.defer="phone"
                    class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500" placeholder="phone">
                @error('phone')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-600">address</label>
                <input type="text" wire:model.defer="address"
                    class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500" placeholder="address">
                @error('address')
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
