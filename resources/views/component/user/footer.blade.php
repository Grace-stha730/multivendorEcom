<section class="bg-gray-800 text-white">
    <div class="w-[80%] mx-auto flex justify-between flex-wrap py-10">
        <div class="1/4">
            <h3 class="text-xl font-semibold ">Quick links</h3>
            <div class="flex flex-col mt-3 space-y-4">
                <a class="hover:text-gray-400" href="{{ route('home') }}">Home</a>
                <a class="hover:text-gray-400" href="{{ route('user.about-us') }}">About</a>
                <a class="hover:text-gray-400" href="{{ route('user.product') }}">Shop</a>
                <a class="hover:text-gray-400" href="{{route('user.contact-us')}}">Contact</a>
            </div>
        </div>

        <div class="1/4">
            <h3 class="text-xl font-semibold ">Extra links</h3>
            <div class="flex flex-col mt-3 space-y-4">
                <a class="hover:text-gray-400" href="{{route('user.login')}}">Login</a>
                <a class="hover:text-gray-400" href="{{ route('user.register') }}">Register</a>
                <a class="hover:text-gray-400" href="{{ route('user.cart') }}">Cart</a>
                <a class="hover:text-gray-400" href="{{ route('user.order') }}">Orders</a>
            </div>
        </div>

        <div class="1/4">
            <h3 class="text-xl font-semibold">Contact info</h3>
            <div class="flex flex-col mt-3 space-y-4">
                <a class="hover:text-gray-400" href="#"> <i class="fas fa-phone"></i> 01 - 4146257 </a>
                <a class="hover:text-gray-400" href="#"> <i class="fas fa-phone"></i> 01 - 4142285 </a>
                <a class="hover:text-gray-400" href="#"> <i class="fas fa-envelope"></i> Plantpals730@gmail.com </a>
                <a class="hover:text-gray-400" href="#"> <i class="fas fa-map-marker-alt"></i> Bhaktapur, Nepal </a>
            </div>
        </div>

        <div class="1/4">
            <h3 class="text-xl font-semibold">Follow us</h3>
            <div class="flex flex-col mt-3 space-y-4">
                <a class="hover:text-gray-400" href="#"> <i class="fab fa-facebook-f"></i> Facebook </a>
                <a class="hover:text-gray-400" href="#"> <i class="fab fa-twitter"></i> Twitter </a>
                <a class="hover:text-gray-400" href="#"> <i class="fab fa-instagram"></i> Instagram </a>
                <a class="hover:text-gray-400" href="#"> <i class="fab fa-linkedin"></i> Linkedin </a>
            </div>
        </div>
    </div>
</section>
