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

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Fields & Services
Route::get('/fields', [FieldsController::class, 'index'])->name('fields.index');
Route::get('/services', [ServiceController::class, 'index'])->name('services.index');

// Pages
Route::get('/about', [PagesController::class, 'about'])->name('about');
Route::get('/dashboard', [PagesController::class, 'dashboard'])->name('visitor.dashboard');
Route::get('/fields', [PagesController::class, 'fields'])->name('visitor.fields');
Route::get('/profile', [PagesController::class, 'profile'])->name('profile');
Route::get('/feedback', [PagesController::class, 'feedback'])->name('visitor.feedback');
Route::get('/myServices', [PagesController::class, 'myServices'])->name('myServices');
Route::get('/Services-detail', [PagesController::class, 'serviceDetail'])->name('visitor.Services-detail');
Route::get('/order-detail', [PagesController::class, 'orderDetail'])->name('order.detail');

// Cart
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

// Admin
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function(){
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

// Boss
Route::prefix('boss')->name('boss.')->middleware(['auth'])->group(function(){
    Route::get('/boss/profile', [BossController::class, 'profile'])->name('profile');
    Route::post('/boss/profile', [BossController::class, 'updateProfile'])->name('profile.update');
    Route::get('/dashboard', [BossController::class, 'dashboard'])->name('dashboard');
    Route::get('/manage-users', [BossController::class, 'manageUsers'])->name('manage.users');
    Route::post('/store-user', [BossController::class, 'storeUser'])->name('store.user');
    Route::post('/update-user', [BossController::class, 'updateUser'])->name('update.user');
    Route::post('/delete-user', [BossController::class, 'deleteUser'])->name('delete.user');
    Route::get('/manage-bookings', [BossController::class, 'manageBookings'])->name('manage.bookings');
    Route::post('/update-booking-status', [BossController::class, 'updateBookingStatus'])->name('update-booking-status');
    Route::get('/invoices', [BossController::class, 'invoices'])->name('invoices');
    Route::get('/export-invoice', [BossController::class, 'exportInvoice'])->name('export.invoice');
    Route::get('/edit-status', [BossController::class, 'editStatus'])->name('edit.status');
    Route::get('/about', [BossController::class, 'about'])->name('about');
    Route::get('/manage-fields', [BossController::class, 'manageFields'])->name('manage.fields');
    Route::post('/store-field', [BossController::class, 'storeField'])->name('store.field');
    Route::post('/update-field', [BossController::class, 'updateField'])->name('update.field');
    Route::post('/delete-field', [BossController::class, 'deleteField'])->name('delete.field');
    Route::get('/manage-orders', [BossController::class, 'manageOrders'])->name('manage.orders');
    Route::get('/manage-services', [BossController::class, 'manageServices'])->name('manage.services');
    Route::post('/store-service', [BossController::class, 'storeService'])->name('store.service');
    Route::post('/update-service', [BossController::class, 'updateService'])->name('update.service');
    Route::post('/delete-service', [BossController::class, 'deleteService'])->name('delete.service');
    Route::get('/user_service_history', [BossController::class, 'userServiceHistory'])->name('user.service.history');
    Route::get('/statistics', [BossController::class, 'statistics'])->name('statistics');
    Route::get('/manage-feedback', [BossController::class, 'manageFeedback'])->name('manage.feedback');
});

// Momo
Route::get('/momo/pay', [MomoController::class, 'createPayment'])->name('momo.pay');
Route::get('/momo/return', [MomoController::class, 'returnUrl'])->name('momo.return');
Route::post('/momo/ipn', [MomoController::class, 'ipnUrl'])->name('momo.ipn');  