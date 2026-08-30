<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
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
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\ChatbotIntentController;
use App\Http\Controllers\ChatbotKeywordController;
use App\Http\Controllers\ChatbotResponseController;
use App\Http\Controllers\BookingMomoController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;

use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\AddressController;
require __DIR__.'/auth.php';

Route::get('/h', function () {
    return view('h');
});

// Route::post('/forgot-password', function (Request $request) {
//     $request->validate(['email' => 'required|email']);

//     $status = Password::sendResetLink(
//         $request->only('email')
//     );

//     return $status === Password::RESET_LINK_SENT
//         ? back()->with(['status' => __($status)])
//         : back()->withErrors(['email' => __($status)]);
// });
// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Auth
// Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm'])->name('forgot');
// Route::post('/forgot-password', [ForgotPasswordController::class, 'sendMail'])->name('forgot.post');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Fields & Services
Route::get('/services', [ServiceController::class, 'index'])->name('services.index');

// visitor
Route::get('/about', [VisitorController::class, 'about'])->name('about');
Route::get('/dashboard', [VisitorController::class, 'dashboard'])->name('visitor.dashboard');
Route::get('/fields', [VisitorController::class, 'fields'])->name('visitor.fields');
Route::get('/feedback', [VisitorController::class, 'feedbacks'])->name('visitor.feedback');
Route::get('/services', [VisitorController::class, 'services'])->name('myServices');
Route::get('/Services-detail', [VisitorController::class, 'serviceDetail'])->name('visitor.Services-detail');

// Cartp
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/update-quantity', [CartController::class, 'updateQuantity'])->name('cart.updateQuantity');
Route::post('/cart/update-item', [CartController::class, 'updateItem'])->name('cart.updateItem');
Route::post('/checkout', [CartController::class, 'checkout'])->name('checkout.post');
Route::post('/checkout-multiple', [CartController::class, 'checkoutMultiple'])->name('checkout.multiple');

// Booking
Route::get('/booking', [BookingController::class, 'create'])->name('booking.create');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
Route::get('/booking/detail', [BookingController::class, 'detail'])->name('booking.detail');
Route::get('/field-schedule', [BookingController::class, 'fieldSchedule'])->name('field.schedule');
Route::get('/my-bookings', [BookingController::class, 'myBookings'])->name('bookings.my');

// Admin
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function(){
    Route::get('/home', [HomeController::class, 'indexadmin'])->name('home');
//setting 
Route::get('settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings');

 Route::get('settings/pricing', [\App\Http\Controllers\Admin\SettingController::class, 'pricing'])->name('settings.pricing');
    Route::post('settings/pricing', [\App\Http\Controllers\Admin\SettingController::class, 'storePricing'])->name('settings.pricing.store');
  // 🔥 SERVICE DISCOUNT
    Route::post('/service-discount/store', [SettingController::class, 'storeServiceDiscount'])
        ->name('settings.service-discount.store');

    Route::delete('/service-discount/{id}', [SettingController::class, 'deleteServiceDiscount'])
        ->name('settings.service-discount.delete');

    Route::delete('settings/pricing/{id}', [\App\Http\Controllers\Admin\SettingController::class, 'deletePricing'])->name('settings.pricing.delete');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
    Route::post('/profile', [AdminController::class, 'updateProfile'])->name('profile.update');
    Route::get('/manage-bookings', [AdminController::class, 'manageBookings'])->name('manage.bookings');
    Route::post('/update-booking-status', [AdminController::class, 'updateBookingStatus'])->name('update-booking-status');
    Route::get('/invoices', [AdminController::class, 'invoices'])->name('invoices');
    Route::get('/export-invoice', [AdminController::class, 'exportInvoice'])->name('export.invoice');
Route::get('/edit-status/{id}', [AdminController::class, 'editStatus'])
    ->name('admin.edit.status.order');    Route::get('/about', [AdminController::class, 'about'])->name('about');
    Route::get('/manage-fields', [AdminController::class, 'manageFields'])->name('manage.fields');
    Route::post('/store-field', [AdminController::class, 'storeField'])->name('store.field');
    Route::post('/update-field', [AdminController::class, 'updateField'])->name('update.field');
    Route::post('/delete-field', [AdminController::class, 'deleteField'])->name('delete.field');
    Route::get('/manage-orders', [AdminController::class, 'manageOrders'])->name('manage.orders');
Route::get('/edit-status-order/{id}', [AdminController::class, 'editStatusOrder'])
    ->name('edit.status.order');    Route::post('/update-order-status', [AdminController::class, 'updateOrderStatus'])->name('update.order.status');
    Route::post('/update-order-items-status', [AdminController::class, 'updateOrderItemsStatus'])->name('update.order.items.status');
    Route::get('/manage-services', [AdminController::class, 'manageServices'])->name('manage.services');
    Route::post('/store-service', [AdminController::class, 'storeService'])->name('store.service');
    Route::post('/update-service', [AdminController::class, 'updateService'])->name('update.service');
    Route::post('/delete-service', [AdminController::class, 'deleteService'])->name('delete.service');
    Route::get('/manage-categories', [AdminController::class, 'manageCategories'])->name('manage.categories');
    Route::post('/store-category', [AdminController::class, 'storeCategory'])->name('store.category');
    Route::post('/update-category', [AdminController::class, 'updateCategory'])->name('update.category');
    Route::post('/delete-category', [AdminController::class, 'deleteCategory'])->name('delete.category');
    Route::get('/user_service_history', [AdminController::class, 'userServiceHistory'])->name('user.service.history');
    Route::get('/statistics', [AdminController::class, 'statistics'])->name('statistics');
    Route::get('/manage-feedback', [AdminController::class, 'manageFeedback'])->name('manage.feedback');
    Route::post('/manage-feedback/{feedback}/reply', [AdminController::class, 'replyFeedback'])->name('manage.feedback.reply');
    Route::get('/chat-conversations', [ChatController::class, 'adminIndex'])->name('chat.index');
    Route::get('/chat-conversations/{conversation}', [ChatController::class, 'adminShow'])->name('chat.show');
    Route::post('/chat-conversations/{conversation}/reply', [ChatController::class, 'adminReply'])->name('chat.reply');
//generate chatbot rules

    Route::get('/', [ChatbotIntentController::class,'index'])->name('chatbot.index');

    Route::post('/intent/store', [ChatbotIntentController::class,'storeIntent'])
        ->name('chatbot.intent.store');

    Route::post('/keyword/store', [ChatbotIntentController::class,'storeKeyword'])
        ->name('chatbot.keyword.store');

    Route::post('/response/store', [ChatbotIntentController::class,'storeResponse'])
        ->name('chatbot.response.store');

    Route::post('/generate', [ChatbotIntentController::class,'generateRules'])
        ->name('chatbot.generate');

Route::put('/intent/{id}',
[ChatbotIntentController::class,'update'])
->name('chatbot.intent.update');

Route::delete('/intent/{id}',
[ChatbotIntentController::class,'destroy'])
->name('chatbot.intent.delete');


Route::put('/keyword/{id}',
[ChatbotKeywordController::class,'update'])
->name('chatbot.keyword.update');

Route::delete('/keyword/{id}',
[ChatbotKeywordController::class,'destroy'])
->name('chatbot.keyword.delete');


Route::put('/response/{id}',
[ChatbotResponseController::class,'update'])
->name('chatbot.response.update');

Route::delete('/response/{id}',
[ChatbotResponseController::class,'destroy'])
->name('chatbot.response.delete');

});


// Boss
Route::prefix('boss')->name('boss.')->middleware(['auth','boss'])->group(function(){
    Route::get('/home', [HomeController::class, 'indexboss'])->name('home');

    Route::get('/profile', [BossController::class, 'profile'])->name('profile');
    Route::post('/profile', [BossController::class, 'updateProfile'])->name('profile.update');
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
    Route::get('/edit-status-order', [BossController::class, 'editStatusOrder'])->name('edit.status.order');
    Route::post('/update-order-status', [BossController::class, 'updateOrderStatus'])->name('update.order.status');
    Route::get('/manage-services', [BossController::class, 'manageServices'])->name('manage.services');
    Route::post('/store-service', [BossController::class, 'storeService'])->name('store.service');
    Route::post('/update-service', [BossController::class, 'updateService'])->name('update.service');
    Route::post('/delete-service', [BossController::class, 'deleteService'])->name('delete.service');
    Route::get('/manage-categories', [BossController::class, 'manageCategories'])->name('manage.categories');
    Route::post('/store-category', [BossController::class, 'storeCategory'])->name('store.category');
    Route::post('/update-category', [BossController::class, 'updateCategory'])->name('update.category');
    Route::post('/delete-category', [BossController::class, 'deleteCategory'])->name('delete.category');
    Route::get('/user_service_history', [BossController::class, 'userServiceHistory'])->name('user.service.history');
    Route::get('/statistics', [BossController::class, 'statistics'])->name('statistics');
    Route::get('/manage-feedback', [BossController::class, 'manageFeedback'])->name('manage.feedback');
});
// User Pages - Protected Routes
Route::middleware(['auth'])->prefix('user')->name('user.')->group(function(){
    // Dashboard & Main Pages
    Route::get('/home', [HomeController::class, 'indexuser'])->name('home');
    Route::post('/check-booking', [PagesController::class, 'checkBooking'])
    ->name('check.booking');
    Route::get('/dashboard', [PagesController::class, 'dashboard'])->name('dashboard');
    Route::get('/about', [PagesController::class, 'about'])->name('about');
    

    // Fields & Services
    Route::get(
    '/services/search',
    [PagesController::class, 'searchServices']
)->name('services.search');
    Route::get('/fields', [PagesController::class, 'fields'])->name('fields');
    Route::get('/services', [PagesController::class, 'services'])->name('services');
    Route::get('/service/{id}', [PagesController::class, 'serviceDetail'])
        ->whereNumber('id')
        ->name('serviceDetail');
    Route::post('/service/{id}', [PagesController::class, 'addToCart'])->name('service.addToCart');
    
    // Profile
    Route::get('/profile', [PagesController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [PagesController::class, 'updateProfile'])->name('profile.update');

    // Addresses
    Route::post('/address/store', [AddressController::class, 'store'])->name('address.store');
    Route::get('/address/list', [AddressController::class, 'list'])
    ->name('address.list');
    Route::put('/address/{address}/update', [AddressController::class, 'update'])->name('address.update');
    Route::delete('/address/{address}/delete', [AddressController::class, 'delete'])->name('address.delete');
    Route::get(
        '/address/edit/{orderId}',
        [AddressController::class, 'showEditAddressOrder']
    )->name('address.order.edit.show');
    Route::post(
        '/address/edit/{orderId}',
        [AddressController::class, 'editAddressOrder']
    )->name('address.order.edit');
    // Booking
    Route::get('/my-bookings-fetch', [PagesController::class, 'myBookingsFetch'])->name('myBookings.fetch');
    Route::get('/bookingcreate', [PagesController::class, 'bookingcreate'])->name('bookingcreate');
    Route::post('/booking', [PagesController::class, 'storeBooking'])->name('bookingstore');
    Route::get('/my-bookings', [BookingController::class, 'myBookings'])->name('myBookings');
    Route::get('/field-schedule', [PagesController::class, 'fieldSchedule']) ->name('fieldSchedule');
    Route::get('/booking/search', [BookingController::class, 'searchBooking'])->name('search.Booking');
    Route::get('/booking/{id}', [PagesController::class, 'bookingdetail'])->whereNumber('id')->name('bookingdetail');
    Route::post('/booking/{id}/cancel', [PagesController::class, 'cancelBooking'])->whereNumber('id')->name('cancelBooking');
    Route::get('/booking/{id}/export', [PagesController::class, 'exportInvoicebooking'])->whereNumber('id')->name('exportInvoicebooking');

    
    // Cart & Shopping
    Route::get('/cart', [PagesController::class, 'cart'])->name('cart');
    Route::post('cart/add/checkoutAll', [CartController::class, 'checkoutAll'])->name('checkoutAll');
    Route::post('cart/add/checkoutSelected', [CartController::class, 'checkoutSelected'])->name('cart.add.checkoutSelected');
    Route::post('add/checkoutBuyNow', [CartController::class, 'checkoutBuyNow'])->name('add.checkoutBuyNow');
Route::post('/user/cart/update-item', [CartController::class, 'updateItem'])
    ->name('cart.updateItem');
    Route::post('/cart/add', [PagesController::class, 'addToCart'])->name('addToCart');
    Route::post('/cart/remove', [PagesController::class, 'removeFromCart'])->name('removeFromCart');
    Route::post('/cart/update-quantity', [PagesController::class, 'updateQuantity'])->name('updateQuantity');
    Route::post('/cart/update-item', [PagesController::class, 'updateCartItem'])->name('updateCartItem');
    Route::post('/cart/addAjax', [PagesController::class, 'addAjax'])->name('cart.add.ajax');
    // Checkout
     Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [CartController::class, 'checkout'])->name('checkout.post');
    Route::post('/checkout-multiple', [PagesController::class, 'checkoutMultiple'])->name('checkoutMultiple');
    Route::get('/checkout-success', [PagesController::class, 'checkoutSuccess'])->name('checkoutSuccess');
    
    // Orders
    Route::get('/orders', [PagesController::class, 'orders'])->name('orders');
    Route::get('/order/{id}', [PagesController::class, 'orderDetail'])->name('orderDetail');
    Route::get('/order/{id}/export', [PagesController::class, 'exportInvoice'])->name('exportInvoice');
    
    // Order Tracking
    Route::get('/order/{order}/tracking', [\App\Http\Controllers\ShipmentController::class, 'show'])->name('order.tracking');
    Route::get('/order/{order}/tracking-data', [\App\Http\Controllers\ShipmentController::class, 'data'])->name('order.tracking.data');
    Route::post('/order/{order}/tracking-status', [\App\Http\Controllers\ShipmentController::class, 'updateStatus'])->name('order.tracking.status');
    
    // Services Purchased
      Route::get(
    '/bookings/search',
    [BookingController::class, 'searchBookings']
)->name('bookings.search');

    Route::get('/my-services', [PagesController::class, 'myServices'])->name('myServices');
    Route::get('/wishlist', function () {
        return view('user.wishlist');
    })->name('wishlist');
    Route::get('/payment/order/{order}', [PagesController::class, 'showOrderPaymentMethod'])->name('payment.order');
    Route::post('/payment/order/{order}', [PagesController::class, 'handleOrderPaymentMethod'])->name('payment.order.submit');
    Route::get('/payment/booking/{booking}', [PagesController::class, 'showBookingPaymentMethod'])->name('payment.booking');
    Route::post('/payment/booking/{booking}', [PagesController::class, 'handleBookingPaymentMethod'])->name('payment.booking.submit');
    //payments
    Route::get('/momo/pay', [MomoController::class, 'createPayment'])->name('momo.pay');
    Route::get('/momo/return', [MomoController::class, 'returnUrl'])->name('momo.return');
    // Feedback
    Route::get('/feedback', [PagesController::class, 'feedback'])->name('feedback');
    Route::post('/feedback', [PagesController::class, 'sendFeedback'])->name('sendFeedback');

    Route::middleware(['auth'])->group(function () {
        // User chat support
        Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
        Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');
    });

// Route::get('/chatbot', function () {
//     return view('ap');
//});

Route::get('/booking/momo/{booking_id}',
    [BookingMomoController::class, 'createPayment']
)->name('booking.momo');


});

// Chatbot is available to guests as well as authenticated users.
Route::post('/chatbot/message', [ChatbotController::class, 'reply'])->name('chatbot.message');

Route::get('/booking/momo/return', [BookingMomoController::class, 'returnUrl'])
->name('booking.momo.return');
Route::match(['GET','POST'], '/booking/momo/ipn', [BookingMomoController::class, 'ipnUrl'])
->name('booking.momo.ipn');
Route::match(['GET','POST'], '/momo/ipn',
    [MomoController::class, 'ipnUrl']
)->name('momo.ipn');
//Route::get('/chat/{id}', [ChatController::class,'index']);
//Route::post('/chat/send', [ChatController::class,'sendMessage']);  

Route::middleware(['auth', 'role:admin'])->get(
    '/generate-chatbot-json',
    [ChatbotIntentController::class, 'generateRules']
)->name('chatbot.generate.legacy');

// MB Bank Webhook (no auth, no CSRF)
Route::post('/api/mbbank/webhook', [\App\Http\Controllers\MbBankWebhookController::class, 'handle'])
    ->name('mbbank.webhook');
