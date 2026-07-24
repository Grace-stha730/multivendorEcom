<div class="flex justify-between px-5 items-center " >
    <div class="text-xl cursor-pointer" @click.prevent="toggle">
        <i class="fa-solid fa-bars-staggered"></i>
    </div>

    <div class="text-sm">
        <p>{{ Auth::guard('admin')->user()->name }}</p>
    </div>
    @if (Auth::guard('web')->check())
                <li class="py-3 lg:hidden">
                    <a href="{{ route('user.chat') }}" wire:navigate>Messages</a>
                </li>
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
</div>
