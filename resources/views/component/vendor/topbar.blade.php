<div class="flex justify-between px-5 items-center " >
    <div class="text-xl cursor-pointer" @click.prevent="toggle">
        <i class="fa-solid fa-bars-staggered"></i>
    </div>

    <div class="text-sm">
        <p>{{ Auth::guard('vendor')->user()->owner_name }}</p>
    </div>
</div>
