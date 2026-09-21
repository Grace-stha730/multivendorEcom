<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SearchSelect2Controller;
use App\Livewire\Admin\Message;
use App\Livewire\Admin\Products;
use App\Livewire\Admin\ViewMessage;
use App\Livewire\Auth\User\Login as UserLogin;
use App\Livewire\Auth\User\Register as UserRegister;
use App\Livewire\User\AboutUs;
use App\Livewire\User\Cart;
use App\Livewire\User\Checkout;
use App\Livewire\User\ContactUs;
use App\Livewire\User\Coupons as UserCoupons;
use App\Livewire\User\Collections;
use App\Livewire\User\Order;
use App\Livewire\User\Review;
use App\Livewire\User\Setting;
use App\Livewire\User\VendorInfo;
use App\Livewire\User\Wishlist;
use App\Livewire\Vendor\Order as VendorOrder;
use App\Livewire\User\Product as UserProduct;
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
use App\Livewire\Vendor\Coupons as VendorCoupons;
use App\Livewire\Admin\Coupons as AdminCoupons;
use App\Livewire\Admin\Payouts as AdminPayouts;
use App\Livewire\Admin\Shop as AdminShop;
use App\Livewire\User\Chat as UserChat;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');
Route::get('/searchSelect2', SearchSelect2Controller::class)->name('searchSelect2');
Route::get('product', UserProduct::class)->name('user.product');
Route::get('/product-detail/{id}', ProductDetail::class)->name('product.detail');
Route::get('/contact-us', ContactUs::class)->name('user.contact-us');
Route::get('/about-us', AboutUs::class)->name('user.about-us');
Route::get('/shop/{id}', VendorInfo::class)->name('user.shop');
Route::get('/coupons', UserCoupons::class)->name('user.coupons');
Route::get('/register-shop', \App\Livewire\User\RegisterShop::class)->name('user.register-shop');

Route::middleware('guest')->group(function () {
    Route::get('/login', UserLogin::class)->name('user.login');
    Route::get('/register', UserRegister::class)->name('user.register');
    Route::get('/forgot-password', \App\Livewire\Auth\ForgotPassword::class)->defaults('guard', 'web')->name('user.password.forgot');
    Route::get('/register/verify-otp', \App\Livewire\Auth\User\VerifyOtp::class)->name('user.verify-otp');
    Route::get('/register/create-password', \App\Livewire\Auth\User\CreatePassword::class)->name('user.create-password');
    Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('user.google.redirect');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('user.google.callback');
});

Route::middleware('web')->group(function () {
    Route::get('/payment/esewa/success', [\App\Http\Controllers\EsewaPaymentController::class, 'success'])->name('user.payment.esewa.success');
    Route::get('/payment/esewa/failure', [\App\Http\Controllers\EsewaPaymentController::class, 'failure'])->name('user.payment.esewa.failure');
    Route::get('/payment/esewa/{order}', [\App\Http\Controllers\EsewaPaymentController::class, 'pay'])->whereNumber('order')->name('user.payment.esewa');
    Route::post('/logout', [AuthController::class, 'userlogout'])->name('user.logout');
    Route::get('/cart', Cart::class)->name('user.cart');
    Route::get('/checkout', Checkout::class)->name('user.checkout');
    Route::get('/wishlist', Wishlist::class)->name('user.wishlist');
    Route::get('/order', Order::class)->name('user.order');
    Route::get('/review/{id}', Review::class)->name('user.review');
    Route::get('/setting', Setting::class)->name('user.setting');
    Route::get('/chat', UserChat::class)->name('user.chat');
    Route::get('/collections', Collections::class)->middleware('auth')->name('user.collections');
});

Route::prefix('shop-user')->name('shop-user.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', \App\Livewire\Auth\Login::class)->name('login');
        Route::get('/forgot-password', \App\Livewire\Auth\ForgotPassword::class)->defaults('guard', 'shop_user')->name('password.forgot');
    });

    Route::middleware(['shop_user', 'shop.context'])->group(function () {
        Route::post('/logout', [AuthController::class, 'shopUserLogout'])->name('logout');
        Route::get('/dashboard', Dashboard::class)->name('dashboard');
        Route::get('category', Category::class)
            ->middleware('authorize:category-manage,shop_user')->name('category');
        Route::get('/product', Product::class)
            ->middleware('authorize:product-view,shop_user')->name('product');
        Route::get('/order', VendorOrder::class)
            ->middleware('authorize:order-view,shop_user')->name('order');
        Route::get('/order-detail/{id}', OrderDetail::class)
            ->middleware('authorize:order-view,shop_user')->name('orderDetail');
        Route::get('/setting', VendorSetting::class)->name('setting');
        Route::get('product-review', ProductReview::class)
            ->middleware('authorize:product-view,shop_user')->name('product-review');
        Route::get('/invoice/{id}', [\App\Http\Controllers\InvoiceController::class, 'vendorInvoice'])
            ->middleware('authorize:order-view,shop_user')->name('invoice');
        Route::get('/earnings', VendorEarnings::class)
            ->middleware('authorize:earnings-view,shop_user')->name('earnings');
        Route::get('/chat', VendorChat::class)
            ->middleware('authorize:chat-reply,shop_user')->name('chat');
        Route::get('/coupons', VendorCoupons::class)
            ->middleware('authorize:coupon-manage,shop_user')->name('coupons');
        Route::get('/staff', \App\Livewire\Vendor\Staff::class)
            ->middleware('authorize:staff-invite,shop_user')->name('staff');
        Route::patch('/staff/{id}/role', [\App\Http\Controllers\ShopStaffController::class, 'updateRole'])
            ->middleware('authorize:staff-assign-role,shop_user')->name('staff.role');
    });
});

Route::prefix('admin')->group(function () {
    Route::middleware('guest')->group(function () {
//        Route::get('/register', AdminRegister::class)->name('admin.register');
        Route::get('/login', AdminLogin::class)->name('admin.login');
        Route::get('/forgot-password', \App\Livewire\Auth\ForgotPassword::class)->defaults('guard', 'admin')->name('admin.password.forgot');
    });

    Route::middleware('admin')->group(function () {
        Route::post('/logout', [AuthController::class, 'adminLogout'])->name('admin.logout');
        Route::get('/dashboard', AdminDashboard::class)->name('admin.dashboard');
        Route::get('/products', Products::class)
            ->middleware('authorize:product-view,admin')->name('admin.product');
        Route::get('/product-detail/{id}', AdminProductDetail::class)
            ->middleware('authorize:product-view,admin')->name('admin.product-detail');
        Route::get('/category', AdminCategory::class)
            ->middleware('authorize:category-manage,admin')->name('admin.category');
        Route::get('/order', AdminOrder::class)
            ->middleware('authorize:order-view|delivery-view,admin')->name('admin.order');
        Route::get('/order-detail/{id}', AdminOrderDetail::class)
            ->middleware('authorize:order-view|delivery-view,admin')->name('admin.order-detail');
        Route::get('/invoice/{id}', [\App\Http\Controllers\InvoiceController::class, 'adminInvoice'])
            ->middleware('authorize:order-view,admin')->name('admin.invoice');
        Route::get('/setting', AdminSetting::class)->name('admin.setting');
        Route::get('/store-policies', \App\Livewire\Admin\StorePolicies::class)
            ->middleware('authorize:policy-manage,admin')->name('admin.store-policies');
        Route::get('/message', Message::class)
            ->middleware('authorize:message-view,admin')->name('admin.message');
        Route::get('message-datail/{id}', ViewMessage::class)
            ->middleware('authorize:message-view,admin')->name('admin.message-datail');
        Route::get('/coupons', AdminCoupons::class)
            ->middleware('authorize:coupon-manage,admin')->name('admin.coupons');
        Route::get('/payouts', AdminPayouts::class)
            ->middleware('authorize:payout-view,admin')->name('admin.payouts');
        Route::get('/shops', AdminShop::class)
            ->middleware('authorize:shop-view,admin')->name('admin.shops');
        Route::get('/users', \App\Livewire\Admin\Users::class)
            ->middleware('authorize:admin-user-manage,admin')->name('admin.users');
        Route::get('/shop-registrations', \App\Livewire\Admin\ShopRegistrations::class)
            ->middleware('authorize:shop-view,admin')->name('admin.shop-registrations');
    });
});
