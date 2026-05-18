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
use App\Livewire\User\Order;
use App\Livewire\User\Review;
use App\Livewire\User\Setting;
use App\Livewire\User\VendorInfo;
use App\Livewire\Vendor\Order as modalOrder;
use App\Livewire\User\Product as modalProduct;
use App\Livewire\Auth\Register;
use App\Livewire\User\Home;
use App\Livewire\User\ProductDetail;
use App\Livewire\Vendor\Category;
use App\Livewire\Vendor\Dashboard;
use App\Livewire\Vendor\OrderDetail;
use App\Livewire\Vendor\Product\Product;
use App\Livewire\Vendor\ProductReview;
use Illuminate\Support\Facades\Route;

use App\Livewire\Vendor\Setting as vendorSetting;

use App\Livewire\Auth\Admin\Register as adminRegister;
use App\Livewire\Auth\Admin\Login as adminLogin;
use App\Livewire\Admin\Dashboard as adminDashboard;
use App\Livewire\Admin\ProductDetail as adminProductDetail;
use App\Livewire\Admin\Category as adminCategory;
use App\Livewire\Admin\Order as adminOrder;
use App\Livewire\Admin\OrderDetail as adminOrderDetail;
use App\Livewire\Admin\Setting as adminSetting;


// User Route
Route::get('/', Home::class)->name('home');
Route::get('product',modalProduct::class)->name('user.product');
Route::get('/product-detail/{id}',ProductDetail::class)->name('product.detail');
Route::get('/contact-us',ContactUs::class)->name('user.contact-us');
Route::get('/about-us',AboutUs::class)->name('user.about-us');
Route::get('/vendor-info/{id}',VendorInfo::class)->name('user.vendorInfo');

Route::middleware('guest')->group(function () {
    Route::get('/login', UserLogin::class)->name('user.login');
    Route::get('/register', UserRegister::class)->name('user.register');
});

Route::middleware('web')->group(function (){
    Route::post('/logout',[AuthController::class, 'userlogout'])->name('user.logout');
    Route::get('/cart',Cart::class)->name('user.cart');
    Route::get('/order', Order::class)->name('user.order');
    Route::get('/review/{id}', Review::class)->name('user.review');
    Route::get('/setting', Setting::class)->name('user.setting');
});

// Vendor Route
Route::prefix('vendor')->name('vendor.')->group(function () {

    Route::middleware('guest')->group(function () {
        Route::get('/register', Register::class)->name('register');
        Route::get('/login', Login::class)->name('login');
    });

    Route::middleware('vendor')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', Dashboard::class)->name('dashboard');
        Route::get('category', Category::class)->name('category');
        Route::get('/product', Product::class)->name('product');
        Route::get('/order',modalOrder::class)->name('order');
        Route::get('/order-detail/{id}',OrderDetail::class)->name('orderDetail');
        Route::get('/setting',vendorSetting::class)->name('setting');
        Route::get('product-review',ProductReview::class)->name('product-review');
    });
});


//Admin Route
Route::prefix('admin')->group(function(){
    Route::middleware('guest')->group(function(){
        Route::get('/register',adminRegister::class)->name('admin.register');
        Route::get('/login',adminLogin::class)->name('admin.login');
    });

    Route::middleware('admin')->group(function(){
        Route::post('/logout',[AuthController::class,'adminLogout'])->name('admin.logout');
        Route::get('/dashboard',adminDashboard::class)->name('admin.dashboard');
        Route::get('/vendors',Vendor::class)->name('admin.vendor');
        Route::get('/products',Products::class)->name('admin.product');
        Route::get('/product-detail/{id}',adminProductDetail::class)->name('admin.product-detail');
        Route::get('/category',adminCategory::class)->name('admin.category');
        Route::get('/order',adminOrder::class)->name('admin.order');
        Route::get('/order-detail/{id}',adminOrderDetail::class)->name('admin.order-detail');
        Route::get('/setting',adminSetting::class)->name('admin.setting');
        Route::get('/message',Message::class)->name('admin.message');
        Route::get('message-datail/{id}', ViewMessage::class)->name('admin.message-datail');

    });
});