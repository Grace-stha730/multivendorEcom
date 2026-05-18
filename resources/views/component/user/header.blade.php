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

            <!-- Menu Links -->
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
                <a href="{{ route('user.order') }}" class="hover:border-b-3 {{ request()->is('order') || request()->is('review*') ? 'border-b-3' : '' }}"
                    wire:navigate>Order</a>
            </li>
            <li class="py-3 lg:py-0">
                <a href="{{ route('user.about-us') }}" class="hover:border-b-3 {{ request()->is('about-us') ? 'border-b-3' : '' }}"
                    wire:navigate>About Us</a>
            </li>
            <li class="py-3 lg:py-0">
                <a href="{{ route('user.contact-us') }}" class="hover:border-b-3 {{ request()->is('contact-us') ? 'border-b-3' : '' }}"
                    wire:navigate>Contact Us</a>
            </li>

            @if (Auth::guard('web')->check())
                <li class="py-3 lg:hidden">
                    <form action="{{ route('user.logout') }}" method="POST">
                        @csrf
                        <button class="space-x-1.5 hover:text-gray-400 block cursor-pointer">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </li>
            @else
                <li class="py-3 lg:hidden">
                    <a href="{{ route('user.login') }}" wire:navigate>Login</a>
                </li>
            @endif
        </ul>

        <!-- Right Icons -->
        <div class="flex space-x-6 items-center">
            <div class="cursor-pointer hover:text-gray-400">
                <a class="relative" href="{{ route('user.cart') }}">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <small
                        class="absolute top-[-10px] -right-4 bg-red-800 text-white px-[5px] py-0 rounded-full">{{ $cartCount }}</small>
                </a>
            </div>



            <div class="hidden lg:block cursor-pointer ">
                @if (Auth::guard('web')->user())
                    <div class="relative">
                        <button @click.prevent="popup = !popup"
                            class="hover:text-gray-400 cursor-pointer">{{ Auth::guard('web')->user()->name }}</button>

                        <div class="z-100" x-show="popup" @click.outside="popup = false" x-transition x-cloak>
                            <span
                                class="w-8 h-8 rotate-45 bg-gray-800 absolute bottom-[-54px] left-10 shadow-lg"></span>
                            <div
                                class="absolute bottom-[-130px] left-[-19px] bg-gray-800 px-3 py-4 space-y-4 w-[150px] rounded-lg z-100">
                                <span class="space-x-1.5 block hover:text-gray-400">
                                    <i class="fa-solid fa-gear"></i>
                                    <a href="{{ route('user.setting') }}">Setting</a>
                                </span>

                                <form action="{{ route('user.logout') }}" method="POST">
                                    @csrf
                                    <button class="space-x-1.5 hover:text-gray-400 block cursor-pointer">
                                        <i class="fa-solid fa-right-from-bracket"></i>
                                        <span>Logout</span>
                                    </button>
                                </form>
                            </div>
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
