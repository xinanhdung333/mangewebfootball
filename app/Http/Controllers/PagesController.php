<?php

namespace App\Http\Controllers;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Service;
use App\Models\Field;
use App\Models\Feedback;
use App\Models\Cart;
use App\Models\CartItem;
use Barryvdh\DomPDF\Facade\Pdf; 
use Illuminate\Support\Facades\Hash;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PriceRule;
use Carbon\Carbon;
use App\Models\UserAddress;
use App\Models\Booking;
use App\Http\Controllers\Concerns\UsesServiceQuery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\BookingPayment;
use App\Models\Invoice;
 use App\Models\ServiceDiscount;


class PagesController extends Controller
{
    use UsesServiceQuery;

    private function getOrCreateUserCart($user): Cart
    {
        return Cart::firstOrCreate([
            'user_id' => $user->id,
        ]);
    }

    private function getCartItemsForUser($user)
    {
        $cart = $this->getOrCreateUserCart($user);

        return CartItem::where('cart_id', $cart->id)
            ->join('services', 'cart_items.service_id', '=', 'services.id')
            ->select(
                'cart_items.*',
                'services.name',
                'services.image',
                'services.quantity as stock'
            );
    }

    private function syncCartItem($user, int $serviceId, int $quantity): void
    {
        $cart = $this->getOrCreateUserCart($user);
        $service = Service::findOrFail($serviceId);

        $item = CartItem::where('cart_id', $cart->id)
            ->where('service_id', $service->id)
            ->first();

        if ($item) {
            $item->quantity += $quantity;
            $item->price = $service->price;
            $item->save();
            return;
        }

        CartItem::create([
            'cart_id' => $cart->id,
            'service_id' => $service->id,
            'quantity' => $quantity,
            'price' => $service->price,
        ]);
    }

  
    public function about()
    {
        return view('user.about');
    }

    public function dashboard()
    {
        $user = Auth::user();
        
        // Get booking stats
        $stats_total = $user ? $user->bookings()->count() : 0;
        $stats_confirmed = $user ? $user->bookings()->where('status', 'confirmed')->count() : 0;
        $stats_revenue = $user ? $user->bookings()->sum('total_price') : 0;
            
        // Get recent bookings
        $bookings = $user ? $user->bookings()->latest()->take(5)->get() : [];
         $rule = PriceRule::first();
        $ruleService = ServiceDiscount::first();
        return view('user.dashboard', [
            'user' => $user,
            'stats_total' => $stats_total,
            'stats_confirmed' => $stats_confirmed,
            'stats_revenue' => $stats_revenue,
            'bookings' => $bookings,
            'rule' => $rule,
            'ruleService' => $ruleService
        ]);  
    }
  public function myBookings()
{
    return app(BookingController::class)->myBookings();
}
public function searchBooking(Request $request)
{
    return app(BookingController::class)->searchBooking($request);
}
public function myBookingsFetch(Request $request)
{
    $user = Auth::user();

    $filterStatus = $request->status;

    $query = Booking::where('user_id', $user->id)
        ->with(['field', 'services'])
        ->orderByDesc('id');

    if ($filterStatus) {
        $query->where('status', $filterStatus);
    }

    $bookings = $query
        ->paginate(10)
        ->appends(['status' => $filterStatus]);

    return view('user.booking-table', compact('bookings'))->render();
}
public function checkBooking(Request $request)
{
    $exists = Booking::where('field_id', $request->field_id)
        ->where('booking_date', $request->booking_date)
        ->where(function ($query) use ($request) {
            $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                ->orWhere(function ($q) use ($request) {
                    $q->where('start_time', '<=', $request->start_time)
                        ->where('end_time', '>=', $request->end_time);
                });
        })
        ->exists();

    return response()->json([
        'available' => !$exists
    ]);
}
//thanh toán momo
public function storeBooking(Request $request)
{
    $data = $request->validate([
        'field_id' => 'required|exists:fields,id',
        'booking_date' => 'required|date|after_or_equal:today',
        'start_time' => 'required',
        'end_time' => 'required|after:start_time',
        'services' => 'array',
    ]);

    $user = Auth::user();
    if (!$user) return redirect()->route('login');

    // ===== check trùng =====
    $exists = Booking::where('field_id', $data['field_id'])
        ->where('booking_date', $data['booking_date'])
        ->where(function ($q) use ($data) {
            $q->whereBetween('start_time', [$data['start_time'], $data['end_time']])
              ->orWhereBetween('end_time', [$data['start_time'], $data['end_time']])
              ->orWhere(function ($q2) use ($data) {
                  $q2->where('start_time', '<=', $data['start_time'])
                     ->where('end_time', '>=', $data['end_time']);
              });
        })
        ->exists();

    if ($exists) {
        return back()->with('error', 'Khung giờ đã có người đặt');
    }

    $booking = DB::transaction(function () use ($data, $user, $request) {

        $field = Field::findOrFail($data['field_id']);

        $start = Carbon::createFromFormat('H:i', $data['start_time']);
        $end   = Carbon::createFromFormat('H:i', $data['end_time']);

        $rules = PriceRule::where(function($q) use ($field) {
                $q->where('field_id', $field->id)
                  ->orWhereNull('field_id');
            })
            ->orderByRaw('field_id IS NULL')
            ->get();

        $totalPrice = 0;
        $current = $start->copy();

        while ($current < $end) {

            $next = $current->copy()->addHour();
            if ($next > $end) $next = $end;

            $diff = $current->floatDiffInHours($next);
            $price = $field->price_per_hour;

            $currentMin = ($current->hour * 60) + $current->minute;

            foreach ($rules as $rule) {

                $ruleStart = $this->toMinutes($rule->start_time);
                $ruleEnd   = $this->toMinutes($rule->end_time);

                $inRange =
                    ($ruleStart <= $ruleEnd && $currentMin >= $ruleStart && $currentMin < $ruleEnd)
                    ||
                    ($ruleStart > $ruleEnd && ($currentMin >= $ruleStart || $currentMin < $ruleEnd));

                if ($inRange) {
                    $price *= $rule->multiplier;
                    break;
                }
            }

            $totalPrice += $diff * $price;
            $current = $next;
        }

        // ===== BOOKING =====
        $booking = Booking::create([
            'user_id' => $user->id,
            'field_id' => $field->id,
            'booking_date' => $data['booking_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'total_price' => max(0, $totalPrice),
            'status' => 'pending',
        ]);

        // ===== SERVICES =====
        foreach ($request->input('services', []) as $serviceId => $qty) {

            $qty = (int) $qty;

            if ($qty > 0) {

                $service = Service::find($serviceId);

                if ($service && $service->quantity >= $qty) {

                    DB::table('booking_services')->insert([
                        'booking_id' => $booking->id,
                        'service_id' => $serviceId,
                        'quantity' => $qty,
                    ]);

                    $service->decrement('quantity', $qty);
                }
            }
        }

        return $booking;
    });

    // ===== SERVICES TOTAL =====
    $totalPriceServices = DB::table('booking_services as bs')
        ->join('services as s', 'bs.service_id', '=', 's.id')
        ->where('bs.booking_id', $booking->id)
        ->select(DB::raw('SUM(s.price * bs.quantity) as total'))
        ->value('total') ?? 0;

    $finalTotal = $booking->total_price + $totalPriceServices;

    // ===== PAYMENT =====
    BookingPayment::create([
        'booking_id' => $booking->id,
        'amount' => $finalTotal,
        'status' => 'pending'
    ]);

    return redirect()->route('user.payment.booking', $booking->id);
}

public function bookingcreate(Request $request)
{
    $fieldId = $request->query('field_id');
    $field = $fieldId ? Field::find($fieldId) : null;
    $services = Service::where('status','active')->get();

    // 🔥 lấy rule (ưu tiên riêng > global)
    $priceRules = [];
    if ($field) {
        $priceRules = PriceRule::where(function($q) use ($field) {
            $q->where('field_id', $field->id)
              ->orWhereNull('field_id');
        })
        ->orderByRaw('field_id IS NULL') // ưu tiên rule riêng
        ->get();
    }

    return view('user.booking', [
        'field' => $field,
        'services' => $services,
        'priceRules' => $priceRules
    ]);
}
public function bookingdetail($id)
{
    $booking = \App\Models\Booking::find($id);

    if(!$booking){
        return redirect()->back()->with('error','Không tìm thấy booking');
    }

    return view('user.booking-detail', compact('booking'));
}
public function showOrderPaymentMethod(Order $order)
{
    abort_unless($order->user_id === auth()->id(), 403);

    $payment = Payment::firstOrCreate(
        ['order_id' => $order->id],
        [
            'amount' => $order->total_amount,
            'status' => 'pending',
        ]
    );
$service = DB::table('order_items as oi')
    ->join('services as s', 'oi.service_id', '=', 's.id')
    ->where('oi.order_id', $order->id)
    ->select('s.name', 's.price', 's.image', 'oi.quantity')
    ->get();

    // Get user addresses
    $addresses = \App\Models\UserAddress::where('user_id', auth()->id())->get();

    return view('user.payment-method', [
        'type' => 'order',
        'item' => $order,
        'amount' => $order->total_amount,
        'submitRoute' => route('user.payment.order.submit', $order->id),
        'title' => 'Chon phuong thuc thanh toan don hang',
        'description' => 'Don hang #' . $order->id,
        'payment' => $payment,
        'services' => $service,
        'addresses' => $addresses
    ]);
}
private function toMinutes($time)
{
    [$h, $m] = explode(':', $time);
    return $h * 60 + $m;
}
public function handleOrderPaymentMethod(Request $request, Order $order)
{
    abort_unless($order->user_id === auth()->id(), 403);

    $data = $request->validate([
        'payment_method' => 'required|in:momo,cash',
        'selected_address_id' => 'nullable|exists:user_addresses,id',
    ]);

    // Update order with selected address
    if (!empty($data['selected_address_id'])) {
        $order->update(['user_address_id' => $data['selected_address_id']]);
    }

    $payment = Payment::updateOrCreate(
        ['order_id' => $order->id],
        [
            'amount' => $order->total_amount,
            'status' => 'pending',
            'user_address_id' => $data['selected_address_id'] ?? null,
        ]
    );
    
    if ($data['payment_method'] === 'momo') {
         if ($payment->amount < 10000 || $payment->amount > 50000000) {
            return back()->with('error', 'Số tiền phải từ 10k đến 50 triệu để thanh toán MoMo,vui lòng thanh toán bằng tiền mặt');
        }

        return redirect()->route('user.momo.pay', ['order_id' => $order->id]);
    }

    DB::transaction(function () use ($order, $payment) {
        $payment->update([
            'status' => 'success',
            'paid_at' => now(),
            'payment_method' => 'cash',
        ]);

        $order->update([
            'status' => 'confirmed',
        ]);

        DB::table('order_items')
            ->where('order_id', $order->id)
            ->update(['status' => 'confirmed']);

        $serviceIds = OrderItem::where('order_id', $order->id)
            ->pluck('service_id');

        if ($order->cart_id) {
            CartItem::where('cart_id', $order->cart_id)
                ->whereIn('service_id', $serviceIds)
                ->delete();
        }
    });

    return redirect()->route('user.myServices')
        ->with('success', 'Da xac nhan thanh toan tien mat');
}

public function showBookingPaymentMethod(Booking $booking)
{
    abort_unless($booking->user_id === auth()->id(), 403);
    $payment = BookingPayment::firstOrCreate(
        ['booking_id' => $booking->id],
        [
            'amount' => $booking->total_price,
            'status' => 'pending',
        ]
    );
  $services = DB::table('booking_services as bs')
    ->join('services as s', 'bs.service_id', '=', 's.id')
    ->where('bs.booking_id', $booking->id)
    ->select('s.name', 's.price', 's.image', 'bs.quantity')
    ->get();
    
    // Get user addresses
    $addresses = \App\Models\UserAddress::where('user_id', auth()->id())->get();
    
    return view('user.payment-method', [
        'type' => 'booking',
        'item' => $booking,
        'amount' => $booking->total_price,
        'submitRoute' => route('user.payment.booking.submit', $booking->id),
        'title' => 'Chon phuong thuc thanh toan booking',
        'description' => 'Booking #' . $booking->id,
        'payment' => $payment,
        'services' => $services,
        'addresses' => $addresses
    ]);
}
public function handleBookingPaymentMethod(Request $request, Booking $booking)
{
    abort_unless($booking->user_id === auth()->id(), 403);

    $data = $request->validate([
        'payment_method' => 'required|in:momo,cash',
        'selected_address_id' => 'nullable|exists:user_addresses,id',
    ]);

    $payment = $booking->payment;

    if (!$payment) {
        return back()->with('error', 'Không tìm thấy thông tin thanh toán');
    }

    // Update address_id if provided
    if (!empty($data['selected_address_id'])) {
        $payment->update(['user_address_id' => $data['selected_address_id']]);
    }

    // 👉 Lấy amount từ DB (chuẩn)
    $amount = (int) $payment->amount;

    // 👉 CHẶN MOMO nếu vượt giới hạn
    if ($data['payment_method'] === 'momo') {

        if ($amount < 10000 || $amount > 50000000) {
            return back()->with('error', 'Số tiền phải từ 10k đến 50 triệu để thanh toán MoMo,vui lòng thanh toán bằng tiền mặt');
        }

        return redirect()->route('user.booking.momo', [
            'booking_id' => $booking->id
        ]);
    }

    // 👉 CASH
    $payment->update([
        'status' => 'success',
        'paid_at' => now(),
        'payment_method' => 'cash',
    ]);

    $booking->update([
        'status' => 'confirmed',
    ]);

    return redirect()->route('user.myBookings')
        ->with('success', 'Đã xác nhận thanh toán tiền mặt');
}
public function myServices()
{
    $request = request();
    $filterStatus = $request->query('status');
    $keyword = $request->query('keyword');

    $query = OrderItem::with(['service', 'order'])
        ->whereHas('order', function ($q) {
            $q->where('user_id', auth()->id());
        });

    if ($keyword) {
        $query->whereHas('service', function ($q) use ($keyword) {
            $q->where('name', 'like', '%' . $keyword . '%');
        });
    }

    if ($filterStatus) {
        $query->whereHas('order', function ($q) use ($filterStatus) {
            $q->where('status', $filterStatus);
        });
    }

    $myServices = $query->latest()
        ->paginate(5)
        ->withQueryString();

    $myServices->getCollection()->transform(function ($item) {
        $item->image = $item->service->image ?? null;
        $item->total_amount = ($item->price ?? 0) * ($item->quantity ?? 1);
        return $item;
    });

    return view(
        'user.my-services',
        compact('filterStatus', 'keyword', 'myServices')
    );
}

public function addAjax(Request $request)
{
    $request->validate([
        'service_id' => 'required|integer|exists:services,id',
        'quantity' => 'nullable|integer|min:1',
    ]);

    if (!auth()->check()) {

        return response()->json([
            'success' => false,
            'error' => 'Bạn cần đăng nhập'
        ]);
    }

    $user = auth()->user();
    $qty = (int) ($request->quantity ?? 1);

    $this->syncCartItem($user, (int) $request->service_id, $qty);
  $cart = $this->getOrCreateUserCart($user);

    $total = \App\Models\CartItem::where('cart_id', $cart->id)
        ->sum('quantity');

    return response()->json([
        'success' => true,
        'totalItems' => $total
    ]);
}
 public function exportInvoice($id)
{
    // Lấy đơn hàng
    $order = Order::findOrFail($id);

    // Lấy danh sách dịch vụ trong đơn
    $items = OrderItem::join('services','order_items.service_id','=','services.id')
        ->where('order_items.order_id',$id)
        ->select(
            'services.name',
            'order_items.price',
            'order_items.quantity'
        )
        ->get();

    // tạo PDF
    $pdf = Pdf::loadView('user.pdf.invoice-service', compact('order','items'));
Invoice::create([
    'order_id'   => $order->id,
    'invoice_code' => 'INV-' . time(),
    'total_amount' => $order->total_amount,
    'issued_at'    => now()
]);

    return $pdf->stream("hoa-don-{$order->id}.pdf");
}
 public function cancelBooking($id)
{
    $booking = Booking::findOrFail($id);

    // kiểm tra quyền (tránh user hủy booking của người khác)
    if ($booking->user_id != auth()->id()) {
        abort(403, 'Không có quyền');
    }

    // chỉ cho hủy nếu chưa hoàn thành
    if ($booking->status === 'completed') {
        return redirect()->back()->with('error', 'Không thể hủy booking đã hoàn thành');
    }

    // cập nhật trạng thái
    $booking->status = 'cancelled';
    $booking->save();

    return redirect()->back()->with('success', 'Hủy booking thành công');
}
public function exportInvoicebooking($id)
{
    $booking = Booking::findOrFail($id);

    $services = DB::table('booking_services as bs')
        ->join('services as s', 'bs.service_id', '=', 's.id')
        ->where('bs.booking_id', $id)
        ->select('s.name', 's.price', 'bs.quantity')
        ->get();

    $pdf = Pdf::loadView('user.pdf.invoice-booking', compact('booking','services'));
   Invoice::create([
    'booking_id'   => $booking->id,
    'invoice_code' => 'INV-' . time(),
    'total_amount' => $booking->total_price,
    'issued_at'    => now()
]);

     return $pdf->stream("hoa-don-booking-{$booking->id}.pdf");
    return $pdf->stream("hoa-don-booking-{$booking->id}.pdf");
}

       public function fields()
    {
        // use the Eloquent scope to include ratings
        $fields = Field::get();
$rule = PriceRule::first();
$ruleService = ServiceDiscount::first();
        return view('user.fields', ['fields' => $fields,
        'rule' => $rule,
        'ruleService' => $ruleService
        ]);
    }


public function profile()
{
    $user = Auth::user();
    // 🔥 lịch sử đặt sân
    $bookingHistory = DB::table('bookings as b')
        ->join('fields as f', 'b.field_id', '=', 'f.id')
        ->where('b.user_id', $user->id)
        ->orderByDesc('b.id')
        ->select([
            'b.id',
            'f.name as field_name',
            'b.booking_date',
            'b.start_time',
            'b.end_time',
            'b.total_price'
        ])
        ->get();

    // 🔥 lịch sử mua dịch vụ
  $serviceHistory = DB::table('orders as o')
    ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
    ->join('services as s', 'oi.service_id', '=', 's.id')
    ->where('o.user_id', $user->id)
    ->where('o.status', 'paid')
    ->orderByDesc('o.created_at')
    ->select([
        's.name as service_name',
        DB::raw('oi.price * oi.quantity as total'),
        'o.created_at'
    ])
    ->get();
            $avatar = $user->avt;
    
    // 🔥 lấy danh sách địa chỉ
    $addresses = \App\Models\UserAddress::where('user_id', $user->id)->get();
    
    return view('user.profile', [
        'user' => $user,
        'bookingHistory' => $bookingHistory,
        'serviceHistory' => $serviceHistory, // 🔥 thêm dòng này
        'avatar' => $avatar,
        'addresses' => $addresses
    ]);
}
public function updateProfile(Request $request)
{
    $user = Auth::user();

    // Validate
    $request->validate([
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'new_password' => 'nullable|min:6|confirmed'
    ]);

    // Cập nhật avatar
    if ($request->hasFile('avatar')) {
        $file = $request->file('avatar');
        $filename = 'avatar_'.$user->id.'_'.time().'.'.$file->getClientOriginalExtension();

        $file->move(public_path('uploads/avatars'), $filename);

        $user->avt = $filename;
    }

    // Cập nhật thông tin cơ bản
    $user->name = $request->name;
    $user->phone = $request->phone;

    // Đổi mật khẩu nếu có
    if ($request->filled('new_password')) {

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Mật khẩu hiện tại không đúng');
        }

        $user->password = Hash::make($request->new_password);
    }

    $user->save();

    return back()->with('success', 'Cập nhật thành công!');
}
public function feedback()
{
    $userId = Auth::id();

    // SERVICES PAGINATION
    $services = DB::table('order_items as oi')
        ->join('orders as o', 'oi.order_id', '=', 'o.id')
        ->join('services as s', 'oi.service_id', '=', 's.id')
        ->leftJoin('feedbacks as f', function ($join) {
            $join->on('f.service_id', '=', 's.id')
                 ->on('f.user_id', '=', 'o.user_id');
        })
        ->where('o.user_id', $userId)
        ->orderByDesc('oi.created_at')
        ->select([
            'oi.id as order_item_id',
            's.name as service_name',
            's.image as service_image',
            DB::raw('(oi.price * oi.quantity) as total'),
            'f.message as feedback_message',
            'f.rating as feedback_rating',
        ])
        ->paginate(5, ['*'], 'services_page'); // 👈 phân trang riêng


    // BOOKINGS PAGINATION
    $bookings = DB::table('bookings as b')
        ->join('fields as f', 'b.field_id', '=', 'f.id')
        ->leftJoin('feedbacks as fb', function ($join) {
            $join->on('fb.booking_id', '=', 'b.id')
                 ->on('fb.user_id', '=', 'b.user_id');
        })
        ->where('b.user_id', $userId)
        ->orderByDesc('b.created_at')
        ->select([
            'b.id as booking_id',
            'f.name as field_name',
            'f.image as field_image',
            'b.booking_date',
            'b.start_time',
            'b.end_time',
            'fb.message as feedback_message',
            'fb.rating as feedback_rating',
        ])
        ->paginate(5, ['*'], 'bookings_page'); // 👈 phân trang riêng

    return view('user.feedback', compact('services', 'bookings'));
}
  
public function sendFeedback(Request $request)
{
    $validated = $request->validate([
        'feedback_type' => 'required|in:service,booking',
        'item_id' => 'required|integer',
        'message' => 'required|string|max:2000',
        'rating' => 'required|integer|min:1|max:5',
    ]);

    $userId = Auth::id();

    $feedbackData = [
        'user_id' => $userId,
        'message' => $validated['message'],
        'rating' => $validated['rating'],
    ];

    if ($validated['feedback_type'] === 'service') {
        $feedbackData['service_id'] = $validated['item_id'];
    } else {
        $feedbackData['booking_id'] = $validated['item_id'];
    }

    $query = Feedback::where('user_id', $userId);

    if ($validated['feedback_type'] === 'service') {
        $query->where('service_id', $validated['item_id']);
    } else {
        $query->where('booking_id', $validated['item_id']);
    }

    $feedback = $query->first();

    if ($feedback) {
        $feedback->update($feedbackData);
    } else {
        Feedback::create($feedbackData);
    }


    /*
    =============================
    UPDATE AVG RATING SERVICE
    =============================
    */

    if ($validated['feedback_type'] === 'service') {

        $serviceId = $validated['item_id'];

        $avgRating = Feedback::where('service_id', $serviceId)->avg('rating') ?? 0;

        $totalReviews = Feedback::where('service_id', $serviceId)->count();

        Service::where('id', $serviceId)->update([
            'avg_rating' => round($avgRating, 2),
            'total_reviews' => $totalReviews
        ]);
    }


    /*
    =============================
    UPDATE AVG RATING FIELD
    =============================
    */

   if ($validated['feedback_type'] === 'booking') {

    $bookingId = $validated['item_id'];

    $booking = Booking::find($bookingId);

    if ($booking) {

        $fieldId = $booking->field_id;

        $avgRating = Feedback::whereHas('booking', function ($q) use ($fieldId) {
            $q->where('field_id', $fieldId);
        })->avg('rating') ?? 0;

        $totalReviews = Feedback::whereHas('booking', function ($q) use ($fieldId) {
            $q->where('field_id', $fieldId);
        })->count();

        Field::where('id', $fieldId)->update([
            'avg_rating' => round($avgRating, 2),
            'total_reviews' => $totalReviews
        ]);
    }
}

    return redirect()
        ->route('user.feedback')
        ->with('success', 'Feedback đã gửi thành công.');
}


public function services(Request $request)
{
    $query = Service::query();

    // Search
    if ($request->q) {
        $query->where('name', 'like', '%' . $request->q . '%');
    }

    // Sort
    if ($request->sort == 'priceAsc') $query->orderBy('price', 'asc');
    if ($request->sort == 'priceDesc') $query->orderBy('price', 'desc');
    if ($request->sort == 'rating') $query->orderByDesc('avg_rating');
    if ($request->sort == 'name') $query->orderBy('name', 'asc');

    $services = $query->get();

    // ===== THÊM LOGIC GIẢM GIÁ =====
    $now = Carbon::now();
    $currentMin = $now->hour * 60 + $now->minute;

    foreach ($services as $service) {

        $finalPrice = $service->price;
        $discountPercent = 0;

        $rules = ServiceDiscount::where(function($q) use ($service) {
            $q->where('service_id', $service->id)
              ->orWhereNull('service_id');
        })
        ->orderByRaw('service_id IS NULL')
        ->get();

        foreach ($rules as $rule) {

            $start = explode(':', $rule->start_time);
            $end   = explode(':', $rule->end_time);

            $startMin = $start[0] * 60 + $start[1];
            $endMin   = $end[0] * 60 + $end[1];

            $inTime =
                ($startMin <= $endMin && $currentMin >= $startMin && $currentMin < $endMin)
                ||
                ($startMin > $endMin && ($currentMin >= $startMin || $currentMin < $endMin));

            if ($inTime) {
                $finalPrice = $service->price * $rule->multiplier;
                $discountPercent = (1 - $rule->multiplier) * 100;
                break;
            }
        }

        // gắn thêm vào object
        $service->final_price = $finalPrice;
        $service->discount_percent = $discountPercent;
    }

    // ===== CART COUNT =====
    $totalItems = 0;
    if (Auth::check()) {
        $totalItems = (int) $this->getCartItemsForUser(Auth::user())
            ->sum('cart_items.quantity');
    }
$flashSale = ServiceDiscount::where('is_active', 1)
    ->whereNull('service_id') // áp dụng toàn bộ
    ->first();

$flashStart = null;
$flashEnd = null;
$flashnote = null;

$flashPercent = 0;

if ($flashSale) {
    $flashStart = substr($flashSale->start_time, 0, 5); // 01:00
    $flashEnd = substr($flashSale->end_time, 0, 5);     // 12:00
    $flashPercent = (1 - $flashSale->multiplier) * 100;
    $flashnote = $flashSale->note;
}
$rule = PriceRule::first();
return view('user.services', compact(
    'services',
    'totalItems',
    'flashStart',
    'flashEnd',
    'flashPercent',
    'flashnote',
    'rule'
));
}
public function cart()
{
    $user = auth()->user();

    $cartItemsRaw = $this->getCartItemsForUser($user)->with('service')->get();

    $cartItems = [];
    $totalPrice = 0;

    $now = Carbon::now();
    $currentMin = $now->hour * 60 + $now->minute;

    foreach ($cartItemsRaw as $item) {

        $service = $item->service;
        if (!$service) continue;

        $finalPrice = $service->price;
        $discountPercent = 0;

        // ===== DISCOUNT RULE =====
        $rules = ServiceDiscount::where(function ($q) use ($service) {
                $q->where('service_id', $service->id)
                  ->orWhereNull('service_id');
            })
            ->orderByRaw('service_id IS NULL')
            ->get();

        foreach ($rules as $rule) {

            [$sh, $sm] = explode(':', $rule->start_time);
            [$eh, $em] = explode(':', $rule->end_time);

            $startMin = $sh * 60 + $sm;
            $endMin   = $eh * 60 + $em;

            $inTime =
                ($startMin <= $endMin && $currentMin >= $startMin && $currentMin < $endMin)
                ||
                ($startMin > $endMin && ($currentMin >= $startMin || $currentMin < $endMin));

            if ($inTime) {
                $finalPrice = $service->price * $rule->multiplier;
                $discountPercent = (1 - $rule->multiplier) * 100;
                break;
            }
        }

        $cartItems[] = [
            'id' => $item->id,
            'name' => $service->name,
            'image' => $service->image,

            // 🔥 quan trọng
            'price' => $finalPrice,
            'original_price' => $service->price,
            'discount_percent' => $discountPercent,

            'quantity' => $item->quantity,
        ];

        $totalPrice += $finalPrice * $item->quantity;
    }

    // history
    $serviceHistory = Order::where('user_id', $user->id)
        ->join('order_items', 'orders.id', '=', 'order_items.order_id')
        ->join('services', 'order_items.service_id', '=', 'services.id')
        ->select(
            'orders.id as order_id',
            'orders.total_amount',
            'orders.created_at',
            'order_items.quantity',
            'services.name',
            'services.image'
        )
        ->orderByDesc('orders.created_at')
        ->get();

    return view('user.cart', compact(
        'cartItems',
        'totalPrice',
        'serviceHistory'
    ));
}

    public function addToCart(Request $request, $id = null)
    {
        $data = $request->validate([
            'service_id' => 'sometimes|integer|exists:services,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $serviceId = $data['service_id'] ?? $id;
        if (!$serviceId) {
            return redirect()->back()->withErrors('Dịch vụ không hợp lệ.');
        }

        $user = $request->user();
        $this->syncCartItem($user, (int) $serviceId, (int) $data['quantity']);

        if ($request->has('buy_now')) {
            return $this->checkoutBuyNow($request);
        }

        return redirect()->route('user.cart')->with('success', 'Đã thêm vào giỏ hàng');
    }


public function searchServices(Request $request)
{
    $query = OrderItem::with(['service', 'order'])
        ->whereHas('order', function ($q) {
            $q->where('user_id', auth()->id());
        });

    if ($request->keyword) {
        $query->whereHas('service', function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->keyword . '%');
        });
    }

    if ($request->status) {
        $query->whereHas('order', function ($q) use ($request) {
            $q->where('status', $request->status);
        });
    }

    $myServices = $query->latest()
        ->paginate(5)
        ->withQueryString()
        ->withPath(route('user.services.search'));

    // thêm ảnh + tổng tiền
    $myServices->getCollection()->transform(function ($item) {

        $item->image = $item->service->image ?? null;

        $item->total_amount = 
            ($item->price ?? 0) * ($item->quantity ?? 1);

        return $item;
    });

    if ($request->ajax() || $request->boolean('partial')) {
        return view('user.service-table', compact('myServices'))->render();
    }

    $filterStatus = $request->status ?? null;
    $keyword = $request->keyword ?? null;
$method_payment = $request->method_payment ?? null;
    return view('user.my-services', compact('myServices', 'filterStatus', 'keyword', 'method_payment'));
}
public function removeFromCart(Request $request)
{
    $id = $request->input('cart_item_id');

    if (!$id) {
        return back()->with('error', 'Không có ID');
    }

    $cartItem = CartItem::where('id', $id)
        ->whereHas('cart', function ($query) {
            $query->where('user_id', auth()->id());
        })
        ->first();

    if (!$cartItem) {
        return back()->with('error', 'Không tìm thấy sản phẩm');
    }

    $cartItem->delete();

    return redirect()->route('user.cart')->with('success', 'Đã xóa khỏi giỏ hàng');
}

    public function updateQuantity(Request $request)
    {
        $id = $request->input('cart_item_id') ?? $request->input('id');
        $qty = (int) ($request->input('quantity') ?? $request->input('qty') ?? 1);
        if ($id) {
            $cartItem = CartItem::where('id', $id)
                ->whereHas('cart', function ($query) {
                    $query->where('user_id', auth()->id());
                })
                ->first();

            if ($cartItem) {
                $cartItem->quantity = max(1, $qty);
                $cartItem->save();
            }
        }
        return redirect()->route('user.cart');
    }

    public function updateCartItem(Request $request)
    {
        return $this->updateQuantity($request);
    }
public function fieldSchedule(Request $request)
{
    // Lấy danh sách sân
    $fields = Field::all();

    // Lấy tất cả booking + user
    $bookings = Booking::with('user')
        ->orderBy('booking_date')
        ->orderBy('start_time')
        ->get();

    // Gom booking theo từng field_id
    $bookingMap = [];

    foreach ($bookings as $booking) {
        $bookingMap[$booking->field_id][] = $booking;
    }

    return view('user.field-schedule', [
        'fields' => $fields,
        'bookingMap' => $bookingMap
    ]);
}


public function serviceDetail($id)
{
    $service = Service::findOrFail($id);

    // ===== TÍNH GIẢM GIÁ =====
    $now = Carbon::now();
    $currentMin = $now->hour * 60 + $now->minute;

    $finalPrice = $service->price;
    $originalPrice = $service->price;
    $discountPercent = 0;

   $rules = ServiceDiscount::where(function($q) use ($service) {
        $q->where('service_id', $service->id)
          ->orWhereNull('service_id');
    })
    ->where('is_active', 1)
    ->orderByRaw('service_id IS NULL')
    ->get(); 

  foreach ($rules as $rule) {

    $start = explode(':', $rule->start_time);
    $end   = explode(':', $rule->end_time);

    $startMin = $start[0] * 60 + $start[1];
    $endMin   = $end[0] * 60 + $end[1];

    $matchService =
        $rule->service_id == null || $rule->service_id == $service->id;

    $inTime =
        ($startMin <= $endMin && $currentMin >= $startMin && $currentMin < $endMin)
        ||
        ($startMin > $endMin && ($currentMin >= $startMin || $currentMin < $endMin));

    if ($matchService && $inTime) {
        $finalPrice = $service->price * $rule->multiplier;
        $discountPercent = (1 - $rule->multiplier) * 100;
        break;
    }
}
   return view('user.service-detail', compact(
        'service',
        'finalPrice',
        'originalPrice',
        'discountPercent'
    ));
}
public function orderDetail($id)
{
    $order = Order::with([
        'items.service',
        'userAddress'
    ])->findOrFail($id);

    $orderItems = $order->items;

    $addresses = UserAddress::where(
        'user_id',
        Auth::id()
    )->get();

    return view(
        'user.order-detail',
        compact(
            'order',
            'orderItems',
            'addresses'
        )
    );
}
// taoj payment

}
