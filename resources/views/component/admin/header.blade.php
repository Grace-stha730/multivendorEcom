<header class="my-13 flex justify-center text-white ">
    <div class="w-full px-3">
        {{-- <h1 class="text-2xl mt-5 border-b font-bold flex justify-center pb-4">
            {{ Auth::guard('vendor')->user()->shop_name }}</h1> --}}
        <div class="flex justify-center items-center space-y-1 flex-col">
            @if (Auth::guard('admin')->user()->image)
                <img class="w-20 h-20 rounded-full object-cover"
                    src="{{ asset('storage/' . Auth::guard('admin')->user()->image) }}" alt="">
            @else
                <img class="w-20 h-20 rounded-full object-cover" src="{{ asset('default/vendor.jpg') }}" alt="">
            @endif
            <h1 class="text-base">{{ Auth::guard('admin')->user()->name }}</h1>
        </div>

        <nav class="mt-10">
            <ul class="space-y-4 text-sm ">
                <li>
                    <a class=" py-2 px-4 hover:duration-200 flex items-center gap-2 {{ request()->is('admin/dashboard') ? 'bg-white text-black rounded-lg' : 'text-white hover:bg-white hover:text-black transition rounded-lg' }}"
                        wire:navigate href="{{ route('admin.dashboard') }}">
                        <span :class="open ? 'block' : 'hidden duration-0'"><i class="fas fa-tachometer-alt"></i>
                            Dashboard</span>
                    </a>
                </li>
                <li>
                    <a class=" py-2 px-4 hover:duration-200 flex items-center gap-2 {{ request()->is('admin/products') || request()->is('admin/product-detail*') ? 'bg-white text-black rounded-lg' : 'text-white hover:bg-white hover:text-black transition rounded-lg' }}"
                        wire:navigate href=" {{ route('admin.product') }}">
                        <span :class="open ? 'block' : 'hidden duration-0'"><i class="fas fa-box"></i> Products</span>
                    </a>
                </li>
                <li>
                    <a class=" py-2 px-4 hover:duration-200 flex items-center gap-2 {{ request()->is('admin/category') ? 'bg-white text-black rounded-lg' : 'text-white hover:bg-white hover:text-black transition rounded-lg' }}"
                        wire:navigate href="{{ route('admin.category') }}">
                        <span :class="open ? 'block' : 'hidden duration-0'"><i class="fas fa-tags"></i> Category</span>
                    </a>
                </li>
                <li>
                    <a class="py-2 px-4 hover:duration-200 flex items-center gap-2 {{ request()->is('admin/order') || request()->is('admin/order-detail*') ? 'bg-white text-black rounded-lg' : 'text-white hover:bg-white hover:text-black transition rounded-lg' }}"
                        wire:navigate href="{{ route('admin.order') }}">
                        <span :class="open ? 'block' : 'hidden duration-0'"><i class="fas fa-shopping-cart"></i>
                            Orders</span>
                    </a>
                </li>

                <li>
                    <a class="py-2 px-4 hover:duration-200 flex items-center gap-2 {{ request()->is('admin/vendors') || request()->is('vendor/order-detail*') ? 'bg-white text-black rounded-lg' : 'text-white hover:bg-white hover:text-black transition rounded-lg' }}"
                        wire:navigate href="{{ route('admin.vendor') }}">
                        <span :class="open ? 'block' : 'hidden duration-0'"><i class="fa-solid fa-shop"></i>
                            Vendors</span>
                    </a>
                </li>


                <li>
                    <a class="py-2 px-4 hover:duration-200 flex items-center gap-2 {{ request()->is('admin/message') || request()->is('admin/message-datail*') ? 'bg-white text-black rounded-lg' : 'text-white hover:bg-white hover:text-black transition rounded-lg' }}"
                        wire:navigate href="{{ route('admin.message') }}">
                        <span :class="open ? 'block' : 'hidden duration-0'"><i class="fa-solid fa-message"></i>
                            Mesasge</span>
                    </a>
                </li>

                <li>
                    <a class="py-2 px-4 hover:duration-200 flex items-center gap-2 {{ request()->is('admin/setting') ? 'bg-white text-black rounded-lg' : 'text-white hover:bg-white hover:text-black transition rounded-lg' }}"
                        wire:navigate href="{{ route('admin.setting') }}">
                        <span :class="open ? 'block' : 'hidden duration-0'"><i class="fas fa-cog"></i> Setting</span>
                    </a>
                </li>
            </ul>
        </nav>

    </div>
    <form action="{{ route('admin.logout') }}" method="POST">
        @csrf
        <button class="absolute bottom-18 hover:text-gray-400 cursor-pointer left-10">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </button>
    </form>
</header>
