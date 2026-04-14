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

use App\Models\Booking;
use App\Http\Controllers\Concerns\UsesServiceQuery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\BookingPayment;


class PagesController extends Controller
{
    use UsesServiceQuery;
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
            $response = Http::get('https://newsapi.org/v2/top-headlines', [
        'country' => 'us',
        'apiKey' => 'YOUR_API_KEY'
   ]);

        // Get recent bookings
        $bookings = $user ? $user->bookings()->latest()->take(5)->get() : [];
            $news = collect($response->json()['articles'] ?? [])->map(function ($item) {
                return (array) $item;
            })->toArray();

        return view('user.dashboard', [
            'user' => $user,
            'stats_total' => $stats_total,
            'stats_confirmed' => $stats_confirmed,
            'stats_revenue' => $stats_revenue,
            'bookings' => $bookings,
            'news' => $news
        ]);  
    }
   
public function myBookings(Request $request)
{
    $user = Auth::user();

    $filterStatus = $request->status;

    $query = Booking::where('user_id', $user->id)
        ->with(['field', 'services'])
        ->orderByDesc('id');

    if ($filterStatus) {
        $query->where('status', $filterStatus);
    }

    $bookings = $query->paginate(10);

    return view('user.my-bookings', [
        'bookings' => $bookings,
        'filterStatus' => $filterStatus
    ]);
}public function myBookingsFetch(Request $request)
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

    if (!$user) {
        return redirect()->route('login');
    }

    // check trùng giờ
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

        $field = \App\Models\Field::findOrFail($data['field_id']);

        $hours = (strtotime($data['end_time']) - strtotime($data['start_time'])) / 3600;

        $totalPrice = $field->price_per_hour * $hours;

        $booking = Booking::create([
            'user_id' => $user->id,
            'field_id' => $field->id,
            'booking_date' => $data['booking_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'total_price' => max(0, $totalPrice),
            'status' => 'pending',
        ]);

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

    // tạo payment
    BookingPayment::create([
        'booking_id' => $booking->id,
        'amount' => $booking->total_price,
        'status' => 'pending'
    ]);

    // redirect momo
   
return redirect()->route('user.booking.momo', [
    'booking_id' => $booking->id
]);
}
       public function bookingcreate(Request $request)
    {
        $fieldId = $request->query('field_id');
        $field = $fieldId ? Field::find($fieldId) : null;
        $services = Service::where('status','active')->get();
        return view('user.booking', ['field' => $field, 'services' => $services]);
    }
public function bookingdetail($id)
{
    $booking = \App\Models\Booking::find($id);

    if(!$booking){
        return redirect()->back()->with('error','Không tìm thấy booking');
    }

    return view('user.booking-detail', compact('booking'));
}
        public function myServices()
{
    $filterStatus = request()->query('status');

    return view(
        'user.my-services',
        compact('filterStatus')
    );
}
public function addAjax(Request $request)
{
    if (!auth()->check()) {

        return response()->json([
            'success' => false,
            'error' => 'Bạn cần đăng nhập'
        ]);
    }

    $user = auth()->user();

    $cart = $user->cart;

    if (!$cart) {

        $cart = Cart::create([
            'user_id' => $user->id
        ]);

    }
$qty = $request->quantity ?? 1;

    $item = CartItem::where([
        'cart_id' => $cart->id,
        'service_id' => $request->service_id
    ])->first();

    if ($item) {

        $item->increment('quantity',$qty);

    } else {

        CartItem::create([
            'cart_id' => $cart->id,
            'service_id' => $request->service_id,
            'quantity' => $qty,
            'price' => Service::find($request->service_id)->price
        ]);

    }

    return response()->json([
        'success' => true
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

    return $pdf->stream("hoa-don-booking-{$booking->id}.pdf");
}

       public function fields()
    {
        // use the Eloquent scope to include ratings
        $fields = Field::get();

        return view('user.fields', ['fields' => $fields]);
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

    return view('user.profile', [
        'user' => $user,
        'bookingHistory' => $bookingHistory,
        'serviceHistory' => $serviceHistory // 🔥 thêm dòng này
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

        // update existing feedback by user for this service/booking or create new
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

        return redirect()->route('user.feedback')->with('success', 'Feedback đã gửi thành công.');
    }



public function services(Request $request)
{
$query = Service::query();
    // Search theo tên
    if ($request->q) {
        $query->where('services.name', 'like', '%' . $request->q . '%');
    }



    $services = $query->get();

    // Nếu request từ AJAX → chỉ trả list HTML
    if ($request->ajax()) {

        return view(
            'user.service_list',
            compact('services')
        )->render();

    }

    // Cart session
    $cart = session()->get('cart', []);

    $totalItems = array_sum(
        array_column($cart, 'quantity')
    );

    return view(
        'user.services',
        compact('services', 'totalItems')
    );
}
    public function serviceDetail($id)
    {
        $service = Service::findOrFail($id);
        return view('user.service-detail', compact('service'));
    }
public function cart()
{
    $userId = auth()->id();

    // lấy cart
    $cart = Cart::where('user_id', $userId)
        ->latest()
        ->first();

    $cartItems = [];
    $totalPrice = 0;

    if ($cart) {
        $items = CartItem::where('cart_id', $cart->id)
            ->join('services', 'cart_items.service_id', '=', 'services.id')
            ->select(
                'cart_items.*',
                'services.name',
                'services.price',
                'services.image',
                'services.quantity as stock'
            )
            ->get();

        $cartItems = $items;

        foreach ($items as $item) {
            $totalPrice += $item->price * $item->quantity;
        }
    }

    // history giống code cũ
    $serviceHistory = Order::where('user_id', $userId)
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

    return view('user.cart', compact('cartItems', 'totalPrice', 'serviceHistory'));
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

        $service = Service::findOrFail($serviceId);
        $cart = session()->get('cart', []);
        $id = (string)$service->id;
        $qty = $data['quantity'];

        if (isset($cart[$id])) {
            $cart[$id]['qty'] = ($cart[$id]['qty'] ?? 0) + $qty;
        } else {
            $cart[$id] = [
                'id' => $service->id,
                'name' => $service->name,
                'price' => $service->price,
                'qty' => $qty,
                'quantity' => $qty,
                'image' => $service->image ?? null,
                'stock' => $service->quantity ?? 0,
            ];
        }
        $cart[$id]['quantity'] = $cart[$id]['qty'];

        session(['cart' => $cart]);

        if ($request->has('buy_now')) {
            return redirect()->route('user.checkout');
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
        ->paginate(10)
        ->withQueryString()
        ->withPath(route('user.services.search'));

    // thêm ảnh + tổng tiền
    $myServices->getCollection()->transform(function ($item) {

        $item->image = $item->service->image ?? null;

        $item->total_amount = 
            ($item->price ?? 0) * ($item->quantity ?? 1);

        return $item;
    });

    if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
        return view('user.service-table', compact('myServices'))->render();
    }

    $filterStatus = $request->status ?? null;

    return view('user.my-services', compact('myServices', 'filterStatus'));
}
public function removeFromCart(Request $request)
{
    $id = $request->input('cart_item_id');

    if (!$id) {
        return back()->with('error', 'Không có ID');
    }

    $cartItem = CartItem::find($id);

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
        $cart = session()->get('cart', []);
        if ($id && isset($cart[$id])) {
            $cart[$id]['qty'] = max(1, $qty);
            $cart[$id]['quantity'] = $cart[$id]['qty'];
            session(['cart' => $cart]);
        }
        return redirect()->route('user.cart');
    }

    public function updateCartItem(Request $request)
    {
        return $this->updateQuantity($request);
    }
public function checkoutMultiple(Request $request)
{
    $user = $request->user();
    $selected = $request->input('selected_items', '[]');
    $selectedIds = json_decode($selected, true);

    if (!is_array($selectedIds) || empty($selectedIds)) {
        return redirect()->route('user.cart')->with('error', 'Chưa chọn sản phẩm nào để thanh toán.');
    }

    $cartItems = CartItem::whereIn('id', $selectedIds)
                         ->where('cart_id', $user->cart->id ?? null)
                         ->get();

    if ($cartItems->isEmpty()) {
        return redirect()->route('user.cart')->with('error', 'Sản phẩm không tồn tại trong giỏ hàng.');
    }

    $createdOrders = [];

    DB::transaction(function() use ($cartItems, $user, &$createdOrders) {
        // Tạo order mới
        $order = Order::create([
            'user_id' => $user->id,
            'total_amount' => $cartItems->sum(fn($item) => $item->price * $item->quantity),
            'status' => 'pending',
        ]);

        foreach ($cartItems as $item) {
            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'service_id' => $item->service_id,
                'name' => $item->name,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'total' => $item->price * $item->quantity,
            ]);

            $createdOrders[] = [
                'order_id' => $order->id,
                'name' => $item->name,
                'total' => $orderItem->total,
            ];
        }

        // Xóa CartItem đã thanh toán
        CartItem::whereIn('id', $cartItems->pluck('id'))->delete();
    });

    return view('user.checkout', compact('createdOrders'));
}


 public function fieldSchedule(Request $request)
    {
        $fields = Field::all();
        return view('user.field-schedule', ['fields' => $fields]);
    }

public function checkoutAll(Request $request)
{
    $user = $request->user();

    $cartItems = CartItem::where(
        'cart_id',
        $user->cart->id ?? null
    )->get();
    if ($cartItems->isEmpty()) {
        return redirect()
            ->route('user.cart')
            ->with('error', 'Giỏ hàng trống');
    }

    DB::beginTransaction();

    try {

        $order = $this->createOrderFromItems(
            $cartItems,
            $user
        );
        $createdOrders = [];

foreach ($cartItems as $item){
        $serviceId = $item->service_id;

    $service = Service::find($serviceId);

    $createdOrders[] = [
        'order_id' => $order->id,
        'name' => $service->name,
        'total' => $service->price * $item->quantity
    ];
}

        CartItem::whereIn(
            'id',
            $cartItems->pluck('id')
        )->delete();

        DB::commit();

       return view('user.checkout', [
    'createdOrders' => $createdOrders
]);

  }catch (\Exception $e) {

    DB::rollback();

    dd($e->getMessage());

}
}
public function checkoutSelected(Request $request)
{
    $request->validate([
        'selected_items' => 'required'
    ]);

    $user = $request->user();

    $ids = $request->selected_items;

    if (!is_array($ids)) {
        $ids = explode(',', $ids);
    }

    $items = CartItem::whereIn('id', $ids)
        ->whereHas('cart', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->get();

    if ($items->isEmpty()) {
        return back()->with('error', 'Chưa chọn sản phẩm');
    }

    DB::beginTransaction();

    try {
    $order = $this->createOrderFromItems($items, $user);

            // tạo pending mới
            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $order->total_amount,
                'status' => 'pending'
            ]);
     
DB::commit();

        return redirect()->route('user.momo.pay', [
            'order_id' => $order->id
        ]);

    } catch (\Exception $e) {

        DB::rollback();

        return back()->with('error', 'Thanh toán thất bại');

    }
}
public function checkoutBuyNow(Request $request)
{
    $user = $request->user();

    $service = Service::findOrFail($request->service_id);

    // tạo object giống CartItem
    $item = new \stdClass();
    $item->service_id = $service->id;
    $item->quantity = $request->quantity ?? 1;
    $item->price = $service->price;

    DB::beginTransaction();

    try {

        $order = $this->createOrderFromItems(
            collect([$item]),
            $user
        );

        DB::commit();
$order->load('items');
     return view('user.checkout', [
    'createdOrders' => [
        [
            'order_id' => $order->id,
            'name' => $service->name,
            'total' => $service->price * ($request->quantity ?? 1)
        ]
    ]
]);

    } catch (\Exception $e) {

        DB::rollback();

        return back()->with('error', 'Thanh toán thất bại');

    }
}
public function orderDetail($id)
{
    $order = \App\Models\Order::with('items.service')
        ->findOrFail($id);

    $orderItems = $order->items;

    return view(
        'user.order-detail',
        compact('order','orderItems')
    );
}
// taoj payment

}