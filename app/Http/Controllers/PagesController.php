<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Field;
use App\Models\Service;
use App\Models\Booking;
use App\Models\Order;
use App\Models\Feedback;
use App\Models\User;
use App\Models\UserSpending;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PagesController extends Controller
{
    /**
     * Dashboard - Trang chính của user
     */
    public function dashboard()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Lấy stats
        $stats = [
            'total' => Booking::where('user_id', $user->id)->count(),
            'confirmed' => Booking::where('user_id', $user->id)->where('status', 'confirmed')->count(),
            'revenue' => UserSpending::where('user_id', $user->id)->sum('total_booking') ?? 0,
        ];

        // Lấy bookings gần đây
        $bookings = Booking::where('user_id', $user->id)
            ->with('field')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // News/promotions
        $news = [];

        return view('user.dashboard', [
            'stats_total' => $stats['total'],
            'stats_confirmed' => $stats['confirmed'],
            'stats_revenue' => $stats['revenue'],
            'bookings' => $bookings,
            'news' => $news,
        ]);
    }

    /**
     * Danh sách sân
     */
    public function fields(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $query = Field::where('status', 'active');

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'like', "%$search%")
                ->orWhere('location', 'like', "%$search%");
        }

        $fields = $query->paginate(12);

        return view('user.fields', ['fields' => $fields]);
    }

    /**
     * Danh sách dịch vụ
     */
    public function services(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $query = Service::where('status', 'active');

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'like', "%$search%");
        }

        // Price filter
        if ($request->has('min_price') && $request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && $request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        $services = $query->paginate(12);
        $totalItems = $query->count();

        return view('user.services', [
            'services' => $services,
            'totalItems' => $totalItems,
        ]);
    }

    /**
     * Profile user
     */
    public function profile()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Lịch sử booking
        $bookingHistory = Booking::where('user_id', $user->id)
            ->with('field')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Lịch sử dịch vụ
        $serviceHistory = Order::where('user_id', $user->id)
            ->with('items.service')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($order) {
                return [
                    'order_id' => $order->id,
                    'name' => $order->items->first()?->service->name ?? 'N/A',
                    'total_amount' => $order->total_amount,
                    'created_at' => $order->created_at,
                    'image' => $order->items->first()?->service->image ?? null,
                ];
            });

        return view('user.profile', [
            'user' => $user,
            'bookingHistory' => $bookingHistory,
            'serviceHistory' => $serviceHistory,
        ]);
    }

    /**
     * Đặt sân
     */
    public function booking(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!$request->has('field_id')) {
            return redirect()->route('user.fields');
        }

        $field = Field::find($request->field_id);
        if (!$field) {
            return redirect()->route('user.fields');
        }

        $services = Service::where('status', 'active')
            ->where('quantity', '>', 0)
            ->orderBy('name')
            ->get();

        return view('user.booking', [
            'field' => $field,
            'services' => $services,
        ]);
    }

    /**
     * Chi tiết booking
     */
    public function bookingDetail(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $booking = Booking::where('id', $request->id)
            ->where('user_id', Auth::id())
            ->with('field', 'services')
            ->firstOrFail();

        return view('user.booking-detail', ['booking' => $booking]);
    }

    /**
     * Giỏ hàng
     */
    public function cart()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Lấy cart mới nhất
        $cart = DB::table('cart')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        $cartItems = [];
        $totalPrice = 0;

        if ($cart) {
            $cartItems = DB::table('cart_items as ci')
                ->join('services as s', 'ci.service_id', '=', 's.id')
                ->where('ci.cart_id', $cart->id)
                ->select('ci.id', 'ci.service_id', 'ci.quantity', 's.name', 's.price', 's.image', DB::raw('s.quantity as stock'))
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'service_id' => $item->service_id,
                        'quantity' => $item->quantity,
                        'name' => $item->name,
                        'price' => $item->price,
                        'image' => $item->image,
                        'stock' => $item->stock,
                    ];
                })
                ->toArray();

            $totalPrice = array_reduce($cartItems, function($sum, $item) {
                return $sum + ($item['price'] * $item['quantity']);
            }, 0);
        }

        // Lịch sử dịch vụ
        $serviceHistory = DB::table('orders as o')
            ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->join('services as s', 'oi.service_id', '=', 's.id')
            ->where('o.user_id', $user->id)
            ->select('o.id as order_id', 'o.total_amount', 'o.created_at', 'oi.quantity', 's.name', 's.image')
            ->orderBy('o.created_at', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'order_id' => $item->order_id,
                    'total_amount' => $item->total_amount,
                    'created_at' => $item->created_at,
                    'quantity' => $item->quantity,
                    'name' => $item->name,
                    'image' => $item->image,
                ];
            })
            ->toArray();

        return view('user.cart', [
            'cartItems' => $cartItems,
            'totalPrice' => $totalPrice,
            'serviceHistory' => $serviceHistory,
        ]);
    }

    /**
     * Chi tiết dịch vụ
     */
    public function serviceDetail(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!$request->has('id')) {
            return redirect()->route('user.services');
        }

        $service = Service::where('id', $request->id)
            ->where('status', 'active')
            ->firstOrFail();

        return view('user.service-detail', ['service' => $service]);
    }

    /**
     * Chi tiết đơn hàng
     */
    public function orderDetail(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $order = Order::where('id', $request->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $orderItems = DB::table('order_items as oi')
            ->join('services as s', 'oi.service_id', '=', 's.id')
            ->where('oi.order_id', $order->id)
            ->select('oi.quantity', 'oi.price', 's.name', 's.image')
            ->get()
            ->map(function($item) {
                return [
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'name' => $item->name,
                    'image' => $item->image,
                ];
            })
            ->toArray();

        return view('user.order-detail', [
            'order' => $order,
            'orderItems' => $orderItems,
        ]);
    }

    /**
     * Danh sách booking của user
     */
    public function myBookings(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $query = Booking::where('user_id', Auth::id())->with('field');

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('user.my-bookings', [
            'bookings' => $bookings,
            'filterStatus' => $request->status ?? '',
        ]);
    }

    /**
     * Danh sách dịch vụ đã mua
     */
    public function myServices()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $myServices = DB::table('order_items as oi')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->join('services as s', 'oi.service_id', '=', 's.id')
            ->where('o.user_id', Auth::id())
            ->select('o.id as order_id', 's.id', 's.name', 's.image', 's.price', 'oi.quantity', 'oi.created_at', 'o.status')
            ->orderBy('oi.created_at', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'order_id' => $item->order_id,
                    'id' => $item->id,
                    'name' => $item->name,
                    'image' => $item->image,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'created_at' => $item->created_at,
                    'status' => $item->status,
                ];
            })
            ->toArray();

        return view('user.my-services', ['myServices' => $myServices]);
    }

    /**
     * Feedback
     */
    public function feedback()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Danh sách dịch vụ đã mua
        $services = DB::table('order_items as oi')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->join('services as s', 'oi.service_id', '=', 's.id')
            ->leftJoin('feedback as f', function($join) use ($user) {
                $join->on('f.service_id', '=', 's.id')
                    ->where('f.user_id', '=', $user->id);
            })
            ->where('o.user_id', $user->id)
            ->where('o.status', 'completed')
            ->select(
                'oi.id as order_item_id',
                's.name as service_name',
                's.image as service_image',
                DB::raw('(oi.quantity * oi.price) as total'),
                'f.message as feedback_message',
                'f.rating as feedback_rating'
            )
            ->orderBy('oi.id', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'order_item_id' => $item->order_item_id,
                    'service_name' => $item->service_name,
                    'service_image' => $item->service_image,
                    'total' => $item->total,
                    'feedback_message' => $item->feedback_message,
                    'feedback_rating' => $item->feedback_rating,
                ];
            })
            ->toArray();

        // Danh sách booking đã hoàn thành
        $bookings = DB::table('bookings as b')
            ->join('fields as f', 'b.field_id', '=', 'f.id')
            ->leftJoin('feedback as fb', function($join) use ($user) {
                $join->on('fb.booking_id', '=', 'b.id')
                    ->where('fb.user_id', '=', $user->id);
            })
            ->where('b.user_id', $user->id)
            ->where('b.status', 'completed')
            ->select(
                'b.id as booking_id',
                'f.name as field_name',
                'f.image as field_image',
                'b.booking_date',
                'b.start_time',
                'b.end_time',
                'fb.message as feedback_message',
                'fb.rating as feedback_rating'
            )
            ->orderBy('b.created_at', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'booking_id' => $item->booking_id,
                    'field_name' => $item->field_name,
                    'field_image' => $item->field_image,
                    'booking_date' => $item->booking_date,
                    'start_time' => $item->start_time,
                    'end_time' => $item->end_time,
                    'feedback_message' => $item->feedback_message,
                    'feedback_rating' => $item->feedback_rating,
                ];
            })
            ->toArray();

        return view('user.feedback', [
            'services' => $services,
            'bookings' => $bookings,
        ]);
    }

    /**
     * Trang About
     */
    public function about()
    {
        return view('user.about');
    }

    /**
     * Lịch đặt sân
     */
    public function fieldSchedule()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $fields = Field::where('status', 'active')->get();

        // Lấy tất cả booking
        $bookingMap = [];
        $bookings = DB::table('bookings as b')
            ->join('users as u', 'b.user_id', '=', 'u.id')
            ->whereIn('b.status', ['pending', 'confirmed'])
            ->select('b.id', 'b.field_id', 'b.booking_date', 'b.start_time', 'b.end_time', 'b.status', 'u.name as user_name')
            ->orderBy('b.booking_date')
            ->orderBy('b.start_time')
            ->get();

        foreach ($bookings as $booking) {
            if (!isset($bookingMap[$booking->field_id])) {
                $bookingMap[$booking->field_id] = [];
            }
            $bookingMap[$booking->field_id][] = [
                'id' => $booking->id,
                'field_id' => $booking->field_id,
                'booking_date' => $booking->booking_date,
                'start_time' => $booking->start_time,
                'end_time' => $booking->end_time,
                'status' => $booking->status,
                'user_name' => $booking->user_name,
            ];
        }

        return view('user.field-schedule', [
            'fields' => $fields,
            'bookingMap' => $bookingMap,
        ]);
    }

    /**
     * Cập nhật profile
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $user = Auth::user();
        
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            if ($user->avatar && file_exists(public_path('uploads/avatars/' . $user->avatar))) {
                unlink(public_path('uploads/avatars/' . $user->avatar));
            }
            
            $file = $request->file('avatar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/avatars'), $filename);
            $user->avatar = $filename;
        }

        // Handle password update
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return redirect()->route('user.profile')->with('success', 'Cập nhật hồ sơ thành công!');
    }

    /**
     * Lưu booking
     */
    public function storeBooking(Request $request)
    {
        $request->validate([
            'field_id' => 'required|integer|exists:fields,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $user = Auth::user();
        $field = Field::find($request->field_id);

        // Tính giá
        $start = \Carbon\Carbon::createFromFormat('H:i', $request->start_time);
        $end = \Carbon\Carbon::createFromFormat('H:i', $request->end_time);
        $hours = $end->diffInHours($start);
        $totalPrice = $hours * $field->price_per_hour;

        // Thêm giá dịch vụ nếu có
        $selectedServices = $request->get('services', []);
        foreach ($selectedServices as $serviceId => $quantity) {
            if ($quantity > 0) {
                $service = Service::find($serviceId);
                if ($service) {
                    $totalPrice += $service->price * $quantity;
                }
            }
        }

        // Tạo booking
        $booking = Booking::create([
            'user_id' => $user->id,
            'field_id' => $field->id,
            'booking_date' => $request->booking_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'total_price' => $totalPrice,
            'status' => 'pending',
        ]);

        // Lưu dịch vụ kèm theo
        foreach ($selectedServices as $serviceId => $quantity) {
            if ($quantity > 0) {
                $service = Service::find($serviceId);
                if ($service) {
                    $booking->services()->attach($serviceId, [
                        'quantity' => $quantity,
                        'price' => $service->price,
                    ]);

                    // Trừ số lượng dịch vụ
                    $service->decrement('quantity', $quantity);
                }
            }
        }

        // Cập nhật user spending
        UserSpending::updateOrCreate(
            ['user_id' => $user->id],
            ['total_booking' => DB::raw('total_booking + ' . $totalPrice)]
        );

        return redirect()->route('user.bookingDetail', ['id' => $booking->id])
            ->with('success', 'Đặt sân thành công! Vui lòng chờ xác nhận từ quản lý.');
    }

    /**
     * Hủy booking
     */
    public function cancelBooking(Request $request)
    {
        $booking = Booking::where('id', $request->id)
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();

        $booking->update(['status' => 'cancelled']);

        return redirect()->route('user.bookingDetail', ['id' => $booking->id])
            ->with('success', 'Đặt sân đã được hủy!');
    }

    /**
     * Danh sách đơn hàng
     */
    public function orders()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $orders = Order::where('user_id', Auth::id())
            ->with('items.service')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.orders', ['orders' => $orders]);
    }

    /**
     * Thêm vào giỏ hàng
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'service_id' => 'required|integer|exists:services,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $service = Service::find($request->service_id);

        if ($request->quantity > $service->quantity) {
            return response()->json(['success' => false, 'error' => 'Số lượng không đủ!'], 422);
        }

        // Lấy hoặc tạo cart
        $cart = DB::table('cart')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$cart) {
            $cartId = DB::table('cart')->insertGetId([
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $cartId = $cart->id;
        }

        // Kiểm tra item đã có chưa
        $existingItem = DB::table('cart_items')
            ->where('cart_id', $cartId)
            ->where('service_id', $service->id)
            ->first();

        if ($existingItem) {
            DB::table('cart_items')
                ->where('id', $existingItem->id)
                ->update(['quantity' => $existingItem->quantity + $request->quantity]);
        } else {
            DB::table('cart_items')->insert([
                'cart_id' => $cartId,
                'service_id' => $service->id,
                'quantity' => $request->quantity,
                'price' => $service->price,
                'created_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Xóa khỏi giỏ
     */
    public function removeFromCart(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|integer',
        ]);

        DB::table('cart_items')->where('id', $request->cart_item_id)->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Cập nhật số lượng
     */
    public function updateQuantity(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
        ]);

        $item = DB::table('cart_items')->where('id', $request->cart_item_id)->first();
        
        if (!$item) {
            return response()->json(['success' => false], 404);
        }

        DB::table('cart_items')
            ->where('id', $request->cart_item_id)
            ->update(['quantity' => $request->quantity]);

        // Tính lại giá
        $item = DB::table('cart_items')->where('id', $request->cart_item_id)->first();
        $itemTotal = $item->quantity * $item->price;

        // Tính tổng giỏ
        $cart = DB::table('cart_items')->where('cart_id', $item->cart_id)->get();
        $cartTotal = $cart->sum(function($i) {
            return $i->quantity * $i->price;
        });

        return response()->json([
            'success' => true,
            'new_quantity' => $request->quantity,
            'item_total' => $itemTotal,
            'cart_total' => $cartTotal,
        ]);
    }

    /**
     * Cập nhật item giỏ
     */
    public function updateCartItem(Request $request)
    {
        return $this->updateQuantity($request);
    }

    /**
     * Checkout tất cả
     */
    public function checkout()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Lấy cart mới nhất
        $cart = DB::table('cart')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$cart) {
            return redirect()->route('user.cart')->with('error', 'Giỏ hàng trống!');
        }

        // Lấy items
        $items = DB::table('cart_items')->where('cart_id', $cart->id)->get();

        if ($items->isEmpty()) {
            return redirect()->route('user.cart')->with('error', 'Giỏ hàng trống!');
        }

        $createdOrders = [];

        foreach ($items as $item) {
            $totalAmount = $item->quantity * $item->price;

            // Tạo order
            $orderId = DB::table('orders')->insertGetId([
                'user_id' => $user->id,
                'cart_id' => $cart->id,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'created_at' => now(),
            ]);

            // Tạo order items
            DB::table('order_items')->insert([
                'order_id' => $orderId,
                'service_id' => $item->service_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'created_at' => now(),
            ]);

            $createdOrders[] = [
                'order_id' => $orderId,
                'service_name' => DB::table('services')->find($item->service_id)->name,
                'total_amount' => $totalAmount,
            ];
        }

        // Xóa cart items
        DB::table('cart_items')->where('cart_id', $cart->id)->delete();
        // Xóa cart
        DB::table('cart')->where('id', $cart->id)->delete();

        return view('user.checkout', ['createdOrders' => $createdOrders]);
    }

    /**
     * Checkout các items đã chọn
     */
    public function checkoutMultiple(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $request->validate([
            'selected_items' => 'required|json',
        ]);

        $user = Auth::user();
        $selectedIds = json_decode($request->selected_items, true);

        if (!is_array($selectedIds) || empty($selectedIds)) {
            return redirect()->route('user.cart')->with('error', 'Không có sản phẩm nào được chọn!');
        }

        $items = DB::table('cart_items')
            ->whereIn('id', $selectedIds)
            ->get();

        $createdOrders = [];

        foreach ($items as $item) {
            $totalAmount = $item->quantity * $item->price;

            $orderId = DB::table('orders')->insertGetId([
                'user_id' => $user->id,
                'cart_id' => $item->cart_id,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'created_at' => now(),
            ]);

            DB::table('order_items')->insert([
                'order_id' => $orderId,
                'service_id' => $item->service_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'created_at' => now(),
            ]);

            $createdOrders[] = [
                'order_id' => $orderId,
                'service_name' => DB::table('services')->find($item->service_id)->name,
                'total_amount' => $totalAmount,
            ];

            // Xóa item
            DB::table('cart_items')->where('id', $item->id)->delete();
        }

        return view('user.checkout-multiple', ['createdOrders' => $createdOrders]);
    }

    /**
     * Export hóa đơn
     */
    public function exportInvoice(Request $request)
    {
        // Tạm thời chỉ return JSON
        return response()->json(['message' => 'Invoice export feature coming soon!']);
    }

    /**
     * Gửi feedback
     */
    public function sendFeedback(Request $request)
    {
        $request->validate([
            'feedback_type' => 'required|in:service,booking',
            'item_id' => 'required|integer',
            'message' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $user = Auth::user();

        if ($request->feedback_type === 'service') {
            $orderItem = DB::table('order_items as oi')
                ->join('orders as o', 'o.id', '=', 'oi.order_id')
                ->where('oi.id', $request->item_id)
                ->where('o.user_id', $user->id)
                ->where('o.status', 'completed')
                ->first();

            if (!$orderItem) {
                return redirect()->route('user.feedback')->with('error', 'Dịch vụ không tồn tại!');
            }

            Feedback::create([
                'user_id' => $user->id,
                'service_id' => $orderItem->service_id,
                'message' => $request->message,
                'rating' => $request->rating,
            ]);
        } else {
            $booking = Booking::where('id', $request->item_id)
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->first();

            if (!$booking) {
                return redirect()->route('user.feedback')->with('error', 'Booking không tồn tại!');
            }

            Feedback::create([
                'user_id' => $user->id,
                'booking_id' => $booking->id,
                'message' => $request->message,
                'rating' => $request->rating,
            ]);
        }

        return redirect()->route('user.feedback')->with('success', 'Gửi feedback thành công!');
    }
}
 