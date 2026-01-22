<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MomoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FieldsController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BossController;
use App\Http\Controllers\DashboardController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/momo', function () {
    return view('momo');
});

// Auth pages (show forms and handle submit)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

// Fields listing
Route::get('/fields', [FieldsController::class, 'index'])->name('visitor.fields');

// Static / simple pages
Route::get('/about', [PagesController::class, 'about'])->name('about');
Route::get('/dashboard', [PagesController::class, 'dashboard'])->name('visitor.dashboard');
Route::get('/profile', [PagesController::class, 'profile'])->name('profile');
Route::get('/feedback', [PagesController::class, 'feedback'])->name('visitor.feedback');
Route::get('/myServices', [PagesController::class, 'myServices'])->name('myServices');
Route::get('/Services-detail', [PagesController::class, 'serviceDetail'])->name('visitor.Services-detail');

Route::get('/fields', [PagesController::class, 'fields'])->name('visitor.fields');

// Services
Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
//Route::get('/services/{id}', [ServiceController::class, 'myServices'])->name('myServices.show');

// Cart actions
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/update-quantity', [CartController::class, 'updateQuantity'])->name('cart.updateQuantity');
Route::post('/cart/update-item', [CartController::class, 'updateItem'])->name('cart.updateItem');
Route::post('/checkout', [CartController::class, 'checkout'])->name('checkout');
Route::post('/checkout-multiple', [CartController::class, 'checkoutMultiple'])->name('checkout.multiple');

// Booking
Route::get('/booking', [BookingController::class, 'create'])->name('booking.create');
Route::get('/booking/detail', [BookingController::class, 'detail'])->name('booking.detail');
Route::get('/field-schedule', [BookingController::class, 'fieldSchedule'])->name('field.schedule');
Route::get('/my-bookings', [BookingController::class, 'myBookings'])->name('bookings.my');

// Misc
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/order-detail', [PagesController::class, 'orderDetail'])->name('order.detail');

// Admin area (original pages under pages/admin)
Route::prefix('admin')->name('admin.')->group(function(){
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/manage-bookings', [AdminController::class, 'manageBookings'])->name('manage.bookings');
    Route::get('/invoices', [AdminController::class, 'invoices'])->name('invoices');
    Route::get('/edit-status', [AdminController::class, 'editStatus'])->name('edit.status');
    Route::get('/about', [AdminController::class, 'about'])->name('about');
    Route::get('/manage-fields', [AdminController::class, 'manageFields'])->name('manage.fields');
    Route::get('/manage-orders', [AdminController::class, 'manageOrders'])->name('manage.orders');
    Route::get('/manage-services', [AdminController::class, 'manageServices'])->name('manage.services');
    Route::get('/user_service_history', [AdminController::class, 'userServiceHistory'])->name('user.service.history');
    Route::get('/statistics', [AdminController::class, 'statistics'])->name('statistics');
    Route::get('/manage-feedback', [AdminController::class, 'manageFeedback'])->name('manage.feedback');
});

// Boss area (original pages under pages/boss)
Route::prefix('boss')->name('boss.')->group(function(){
    Route::get('/dashboard', [BossController::class, 'dashboard'])->name('dashboard');
    Route::get('/manage-bookings', [BossController::class, 'manageBookings'])->name('manage.bookings');
    Route::get('/invoices', [BossController::class, 'invoices'])->name('invoices');
    Route::get('/export-invoice', [BossController::class, 'exportInvoice'])->name('export.invoice');
    Route::get('/edit-status', [BossController::class, 'editStatus'])->name('edit.status');
    Route::get('/about', [BossController::class, 'about'])->name('about');
    Route::get('/manage-fields', [BossController::class, 'manageFields'])->name('manage.fields');
    Route::get('/manage-orders', [BossController::class, 'manageOrders'])->name('manage.orders');
    Route::get('/manage-services', [BossController::class, 'manageServices'])->name('manage.services');
    Route::get('/user_service_history', [BossController::class, 'userServiceHistory'])->name('user.service.history');
    Route::get('/statistics', [BossController::class, 'statistics'])->name('statistics');
    Route::get('/manage-feedback', [BossController::class, 'manageFeedback'])->name('manage.feedback');
});

Route::get('/momo/pay', [MomoController::class, 'createPayment'])->name('momo.pay');

Route::get('/momo/return', [MomoController::class, 'returnUrl'])->name('momo.return');

Route::post('/momo/ipn', [MomoController::class, 'ipnUrl'])->name('momo.ipn');

// Compatibility redirects for legacy php-base site paths (/pages/*)
Route::get('/pages/{slug}', function ($slug) {
    $map = [
        'login' => route('login'),
        'register' => route('register'),
        'fields' => route('visitor.fields'),
        'field-schedule.php' => route('field.schedule'),
        'field-schedule' => route('field.schedule'),
        'booking.php' => route('booking.create'),
        'booking' => route('booking.create'),
        'dashboard.php' => route('visitor.dashboard'),
        'dashboard' => route('visitor.dashboard'),
        'about.php' => route('about'),
        'about' => route('about'),
        'cart.php' => route('cart.index'),
        'checkout.php' => route('order.detail'),
        'pages/login' => route('login'), 
        // Legacy php-base Visitor pages (e.g. /pages/Visitor/dashboard.php)
        'Visitor/dashboard.php' => route('home'),
        'Visitor/dashboard' => route('home'),
        'Visitor/fields.php' => route('visitor.fields'),
        'Visitor/fields' => route('visitor.fields'),
        'Visitor/services.php' => route('services.index'),
        'Visitor/services' => route('services.index'),
        'Visitor/feedback.php' => route('visitor.feedback'),
        'Visitor/feedback' => route('visitor.feedback'),
        'Visitor/about.php' => route('about'),
        'Visitor/about' => route('about'),
        'Visitor/booking.php' => route('booking.create'),
        'Visitor/booking' => route('booking.create'),
    ];

    if (isset($map[$slug])) {
        return redirect($map[$slug]);
    }

    abort(404);
});


