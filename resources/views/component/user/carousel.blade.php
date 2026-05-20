<div class="horizontal-slider w-[95%] mx-auto my-5 z-1">
    <div class="p-2">
        <img src="{{ asset('images/banner1.jpeg') }}" class="w-fullh-[500px]  object-cover rounded-lg ">
    </div>
    <div class="p-2">
        <img src="{{ asset('images/banner2.jpeg') }}" class="w-full h-[500px] object-cover rounded-lg">
    </div>
    <div class="p-2">
        <img src="{{ asset('images/banner3.jpeg') }}" class="w-full h-[500px] object-cover rounded-lg">
    </div>
    <div class="p-2">
        <img src="{{ asset('images/banner4.jpeg') }}" class="w-full h-[500px] object-cover rounded-lg">
    </div>
    <div class="p-2">
        <img src="{{ asset('images/banner5.jpeg') }}" class="w-full h-[500px] object-cover rounded-lg">
    </div>
</div>
<script>
    $(document).ready(function() {
        $('.horizontal-slider').slick({
            slidesToShow: 1, // Number of images visible
            slidesToScroll: 1, // How many to slide per click
            autoplay: true,
            autoplaySpeed: 2500,
            arrows: true, // Show left/right arrows
            dots: true, // Show pagination dots
            infinite: true,
           
        });
    });
</script>
