<div class="horizontal-slider w-[95%] mx-auto my-5 z-1">
    <div class="p-2">
        <img src="{{ asset('storage/banner/a.avif') }}" class="w-full  object-cover rounded-lg">
    </div>
    <div class="p-2">
        <img src="{{ asset('storage/banner/b.avif') }}" class="w-full object-cover rounded-lg">
    </div>
    <div class="p-2">
        <img src="{{ asset('storage/banner/c.avif') }}" class="w-full object-cover rounded-lg">
    </div>
    <div class="p-2">
        <img src="{{ asset('storage/banner/d.avif') }}" class="w-full object-cover rounded-lg">
    </div>
    <div class="p-2">
        <img src="{{ asset('storage/banner/e.avif') }}" class="w-full object-cover rounded-lg">
    </div>
     <div class="p-2">
        <img src="{{ asset('storage/banner/f.avif') }}" class="w-full object-cover rounded-lg">
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
