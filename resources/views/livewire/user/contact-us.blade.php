<div class="bg-gray-50 min-h-screen py-12">
    <div class="container mx-auto px-4 max-w-5xl">
        <!-- Heading -->
        <div class="text-center mb-10">
            <h2 class="text-4xl font-bold text-blue-600 mb-3">Contact Us</h2>
            <p class="text-gray-600 max-w-xl mx-auto">
                Have any questions or feedback? We'd love to hear from you! Fill out the form below and we’ll get back
                to you soon.
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-10 bg-white p-8 rounded-2xl shadow-md">
            <!-- Left: Contact Info -->
            <div class="space-y-5">
                <h3 class="text-2xl font-semibold text-blue-600">Get in Touch</h3>
                <p class="text-gray-600 leading-relaxed">
                    Feel free to reach us at our office or contact us through email or phone.
                </p>

                <ul class="space-y-4 text-gray-700">
                    <li class="flex items-start space-x-3">
                        <span class="text-blue-600 text-xl">📍</span>
                        <span>Kathmandu, Nepal</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <span class="text-blue-600 text-xl">📞</span>
                        <span>+977-9800000000</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <span class="text-blue-600 text-xl">✉️</span>
                        <span>support@yoursite.com</span>
                    </li>
                </ul>

                <div class="mt-6">
                    <h4 class="font-semibold mb-2 text-blue-600">Follow us</h4>
                    <div class="flex space-x-4 text-gray-600 text-2xl">
                        <a href="#" class="hover:text-blue-600"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="hover:text-blue-600"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="hover:text-blue-600"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>

            <!-- Right: Contact Form -->
            <div>
                <form wire:submit.prevent="messageSubmit" class="space-y-5">
                    <div>
                        <label class="block mb-1 font-medium">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="name" placeholder="Enter your full name"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @error('name')
                            <small class="text-red-500">{{ $message }}</small>
                        @enderror
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" wire:model="email" placeholder="Enter your email"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @error('email')
                            <small class="text-red-500">{{ $message }}</small>
                        @enderror
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Subject <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="subject" placeholder="Enter subject"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @error('subject')
                            <small class="text-red-500">{{ $message }}</small>
                        @enderror
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Message <span class="text-red-500">*</span></label>
                        <textarea wire:model="message" rows="5" placeholder="Write your message here..."
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                        @error('message')
                            <small class="text-red-500">{{ $message }}</small>
                        @enderror
                    </div>

                    <button type="submit"
                        class="w-full cursor-pointer bg-blue-600 text-white py-2 rounded-lg font-semibold hover:bg-blue-700 transition duration-200">
                        Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
