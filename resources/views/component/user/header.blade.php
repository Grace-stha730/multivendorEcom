<header class="bg-gray-800 text-white sticky-top" x-data="{ open: false, popup: false, }">
    <div class="flex justify-between items-center w-[90%] lg:w-[80%] mx-auto py-2">
        <h1 class="user-header font-normal text-3xl cursor-pointer">
            <a href="{{ route('home') }}">Ecommerce</a>
        </h1>

        <!-- Menu -->
        <ul class="text-sm fixed top-0 z-50 bg-gray-800 px-4 py-5 w-[60%] h-100 duration-300 lg:static lg:flex lg:w-auto lg:h-auto lg:bg-transparent lg:p-0 lg:space-x-6"
            :class="open ? 'right-0' : 'right-[-100%]'">

            <!-- Close Button (visible only on mobile) -->
            <span class="absolute top-3 right-3 cursor-pointer hover:text-gray-400 lg:hidden"
                @click.prevent="open = false">
                <i class="fa-solid fa-xmark"></i>
            </span>

            <!-- User -->
            <div class="cursor-pointer w-full text-center my-5 lg:hidden">
                @if (Auth::guard('web')->check())
                    {{ Auth::guard('web')->user()->name }}
                @else
                    Guest
                @endif
            </div>

            {{-- Main menu: the few pages shoppers use constantly.
                 Collections / AI Chat live in the account menu; About, Contact and Register Shop live in the footer. --}}
            <li class="py-3 lg:py-0">
                <a href="{{ route('home') }}" class="hover:border-b-3 {{ request()->is('/') ? 'border-b-3' : '' }}"
                    wire:navigate>
                    Home
                </a>
            </li>
            <li class="py-3 lg:py-0">
                <a href="{{ route('user.product') }}"
                    class="hover:border-b-3 {{ request()->is('product') || request()->is('product-detail*') ? 'border-b-3' : '' }}"
                    wire:navigate>Product</a>
            </li>
            <li class="py-3 lg:py-0">
                <a href="{{ route('user.coupons') }}" class="hover:border-b-3 {{ request()->is('coupons') ? 'border-b-3' : '' }}"
                    wire:navigate>Coupons</a>
            </li>
            <li class="py-3 lg:py-0">
                <a href="{{ route('user.order') }}" class="hover:border-b-3 {{ request()->is('order') || request()->is('review*') ? 'border-b-3' : '' }}"
                    wire:navigate>Order</a>
            </li>

            <!-- Account links inside the slide-out menu (the desktop dropdown is hidden on mobile) -->
            <li class="lg:hidden mt-3 border-t border-gray-600 pt-3"></li>
            @if (Auth::guard('web')->check())
                <li class="py-3 lg:hidden">
                    <a href="{{ route('user.collections') }}" class="hover:text-gray-400" wire:navigate><i class="fa-solid fa-list text-cyan-400 mr-1"></i>Collections</a>
                </li>
                <li class="py-3 lg:hidden">
                    <a href="{{ route('user.chat') }}" class="hover:text-gray-400" wire:navigate><i class="fa-solid fa-message text-indigo-400 mr-1"></i>AI Chat & Messages</a>
                </li>
                <li class="py-3 lg:hidden">
                    <a href="{{ route('user.setting') }}" class="hover:text-gray-400" wire:navigate><i class="fa-solid fa-gear mr-1"></i>Setting</a>
                </li>
                <li class="py-3 lg:hidden">
                    <form action="{{ route('user.logout') }}" method="POST">
                        @csrf
                        <button class="hover:text-gray-400 cursor-pointer"><i class="fa-solid fa-right-from-bracket mr-1"></i>Logout</button>
                    </form>
                </li>
            @else
                <li class="py-3 lg:hidden">
                    <a href="{{ route('user.login') }}" class="hover:text-gray-400" wire:navigate>Login</a>
                </li>
                <li class="py-3 lg:hidden">
                    <a href="{{ route('user.register') }}" class="hover:text-gray-400" wire:navigate>Register</a>
                </li>
            @endif
        </ul>

        <!-- Right Icons -->
        <div class="flex space-x-6 items-center">
            <div class="cursor-pointer hover:text-gray-400">
                <a class="relative" href="{{ route('user.wishlist') }}" title="Wishlist">
                    <i class="fa-solid fa-heart text-red-500"></i>
                    <small
                        class="absolute top-[-10px] -right-4 bg-red-600 text-white px-[5px] py-0 rounded-full">{{ $wishlistCount }}</small>
                </a>
            </div>

            <div class="cursor-pointer hover:text-gray-400">
                <a class="relative" href="{{ route('user.cart') }}" title="Shopping Cart">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <small
                        class="absolute top-[-10px] -right-4 bg-red-800 text-white px-[5px] py-0 rounded-full">{{ $cartCount }}</small>
                </a>
            </div>

            <div class="hidden lg:block cursor-pointer ">
                @if (Auth::guard('web')->user())
                    <div class="relative">
                        <button @click.prevent="popup = !popup"
                            class="hover:text-gray-400 cursor-pointer flex items-center gap-1.5">
                            {{ Auth::guard('web')->user()->name }}
                            <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="popup ? 'rotate-180' : ''"></i>
                        </button>

                        <!-- Anchored under the button (no hardcoded pixel offsets), so any number of items fits -->
                        <div x-show="popup" @click.outside="popup = false" x-transition x-cloak
                            class="absolute right-0 top-full mt-3 w-56 rounded-lg bg-gray-800 py-2 shadow-lg ring-1 ring-white/10 z-100">
                            <span class="absolute -top-1.5 right-6 h-3 w-3 rotate-45 bg-gray-800 ring-1 ring-white/10 [clip-path:polygon(0_0,100%_0,0_100%)]"></span>

                            <a href="{{ route('user.wishlist') }}" wire:navigate @click="popup = false"
                                class="flex items-center gap-2.5 px-4 py-2 hover:bg-gray-700">
                                <i class="fa-solid fa-heart w-4 text-red-400"></i> Wishlist
                            </a>
                            <a href="{{ route('user.collections') }}" wire:navigate @click="popup = false"
                                class="flex items-center gap-2.5 px-4 py-2 hover:bg-gray-700">
                                <i class="fa-solid fa-list w-4 text-cyan-400"></i> Collections
                            </a>
                            <a href="{{ route('user.chat') }}" wire:navigate @click="popup = false"
                                class="flex items-center gap-2.5 px-4 py-2 hover:bg-gray-700">
                                <i class="fa-solid fa-message w-4 text-indigo-400"></i> AI Chat & Messages
                            </a>
                            <a href="{{ route('user.setting') }}" wire:navigate @click="popup = false"
                                class="flex items-center gap-2.5 px-4 py-2 hover:bg-gray-700">
                                <i class="fa-solid fa-gear w-4"></i> Setting
                            </a>

                            <div class="my-1 border-t border-gray-600"></div>

                            <form action="{{ route('user.logout') }}" method="POST">
                                @csrf
                                <button class="flex w-full items-center gap-2.5 px-4 py-2 text-left hover:bg-gray-700 cursor-pointer">
                                    <i class="fa-solid fa-right-from-bracket w-4"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('user.login') }}" wire:navigate>Login</a>
                @endif
            </div>

            <!-- Hamburger for Mobile -->
            <div class="cursor-pointer lg:hidden hover:text-gray-400" @click.prevent="open = true">
                <i class="fa-solid fa-bars"></i>
            </div>
        </div>
    </div>
</header>
