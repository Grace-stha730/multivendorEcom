<?php

use App\Http\Controllers\AuthController;
use App\Livewire\Admin\Message;
use App\Livewire\Admin\Products;
use App\Livewire\Admin\Vendor;
use App\Livewire\Admin\ViewMessage;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\User\Login as UserLogin;
use App\Livewire\Auth\User\Register as UserRegister;
use App\Livewire\User\AboutUs;
use App\Livewire\User\Cart;
use App\Livewire\User\ContactUs;
use App\Livewire\User\Coupons as UserCoupons;
use App\Livewire\User\Order;
use App\Livewire\User\Review;
use App\Livewire\User\Setting;
use App\Livewire\User\VendorInfo;
use App\Livewire\User\Wishlist;
use App\Livewire\Vendor\Order as VendorOrder;
use App\Livewire\User\Product as UserProduct;
use App\Livewire\Auth\Register as VendorRegister;
use App\Livewire\User\Home;
use App\Livewire\User\ProductDetail;
use App\Livewire\Vendor\Category;
use App\Livewire\Vendor\Dashboard;
use App\Livewire\Vendor\OrderDetail;
use App\Livewire\Vendor\Product\Product;
use App\Livewire\Vendor\ProductReview;
use App\Livewire\Vendor\Setting as VendorSetting;
use App\Livewire\Auth\Admin\Register as AdminRegister;
use App\Livewire\Auth\Admin\Login as AdminLogin;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\ProductDetail as AdminProductDetail;
use App\Livewire\Admin\Category as AdminCategory;
use App\Livewire\Admin\Order as AdminOrder;
use App\Livewire\Admin\OrderDetail as AdminOrderDetail;
use App\Livewire\Admin\Setting as AdminSetting;
use App\Livewire\Vendor\Earnings as VendorEarnings;
use App\Livewire\Vendor\Chat as VendorChat;
use App\Livewire\Admin\Coupons as AdminCoupons;
use App\Livewire\Admin\Payouts as AdminPayouts;
use App\Livewire\User\Chat as UserChat;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');
Route::get('product', UserProduct::class)->name('user.product');
Route::get('/product-detail/{id}', ProductDetail::class)->name('product.detail');
Route::get('/contact-us', ContactUs::class)->name('user.contact-us');
Route::get('/about-us', AboutUs::class)->name('user.about-us');
Route::get('/vendor-info/{id}', VendorInfo::class)->name('user.vendorInfo');
Route::get('/coupons', UserCoupons::class)->name('user.coupons');

Route::middleware('guest')->group(function () {
    Route::get('/login', UserLogin::class)->name('user.login');
    Route::get('/register', UserRegister::class)->name('user.register');
    Route::get('/register/verify-otp', \App\Livewire\Auth\User\VerifyOtp::class)->name('user.verify-otp');
    Route::get('/register/create-password', \App\Livewire\Auth\User\CreatePassword::class)->name('user.create-password');
    Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('user.google.redirect');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('user.google.callback');
});

Route::middleware('web')->group(function () {
    Route::post('/logout', [AuthController::class, 'userlogout'])->name('user.logout');
    Route::get('/cart', Cart::class)->name('user.cart');
    Route::get('/wishlist', Wishlist::class)->name('user.wishlist');
    Route::get('/order', Order::class)->name('user.order');
    Route::get('/review/{id}', Review::class)->name('user.review');
    Route::get('/setting', Setting::class)->name('user.setting');
    Route::get('/chat', UserChat::class)->name('user.chat');
});

Route::prefix('vendor')->name('vendor.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/register', VendorRegister::class)->name('register');
        Route::get('/login', Login::class)->name('login');
    });

    Route::middleware('vendor')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', Dashboard::class)->name('dashboard');
        Route::get('category', Category::class)->name('category');
        Route::get('/product', Product::class)->name('product');
        Route::get('/order', VendorOrder::class)->name('order');
        Route::get('/order-detail/{id}', OrderDetail::class)->name('orderDetail');
        Route::get('/setting', VendorSetting::class)->name('setting');
        Route::get('product-review', ProductReview::class)->name('product-review');
        Route::get('/invoice/{id}', [\App\Http\Controllers\InvoiceController::class, 'vendorInvoice'])->name('invoice');
        Route::get('/earnings', VendorEarnings::class)->name('earnings');
        Route::get('/chat', VendorChat::class)->name('chat');
    });
});

Route::prefix('admin')->group(function () {
    Route::middleware('guest')->group(function () {
//        Route::get('/register', AdminRegister::class)->name('admin.register');
        Route::get('/login', AdminLogin::class)->name('admin.login');
    });

    Route::middleware('admin')->group(function () {
        Route::post('/logout', [AuthController::class, 'adminLogout'])->name('admin.logout');
        Route::get('/dashboard', AdminDashboard::class)->name('admin.dashboard');
        Route::get('/vendors', Vendor::class)->name('admin.vendor');
        Route::get('/products', Products::class)->name('admin.product');
        Route::get('/product-detail/{id}', AdminProductDetail::class)->name('admin.product-detail');
        Route::get('/category', AdminCategory::class)->name('admin.category');
        Route::get('/order', AdminOrder::class)->name('admin.order');
        Route::get('/order-detail/{id}', AdminOrderDetail::class)->name('admin.order-detail');
        Route::get('/invoice/{id}', [\App\Http\Controllers\InvoiceController::class, 'adminInvoice'])->name('admin.invoice');
        Route::get('/setting', AdminSetting::class)->name('admin.setting');
        Route::get('/message', Message::class)->name('admin.message');
        Route::get('message-datail/{id}', ViewMessage::class)->name('admin.message-datail');
        Route::get('/coupons', AdminCoupons::class)->name('admin.coupons');
        Route::get('/payouts', AdminPayouts::class)->name('admin.payouts');
    });
});
