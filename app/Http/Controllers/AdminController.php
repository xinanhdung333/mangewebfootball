<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Booking;
use App\Models\Field;
use App\Models\Service;
use App\Models\Feedback;
use App\Models\UserSpending;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
    use App\Models\ChatbotIntent;
  use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Log; 
use App\Models\ActivityLog;
use App\Models\Category;
class AdminController extends Controller
{
    /**
     * Display admin dashboard with key statistics
     */
    public function activityLogs()
{
    $logs = ActivityLog::orderByDesc('created_at')->paginate(20);

    return view('admin.logs', compact('logs'));
}


    public function dashboard()
    {
        // Get statistics
        $stats_users = User::count();
        $stats_fields = Field::count();
        $stats_bookings = Booking::where('status', 'confirmed')->count();
        $stats_revenue = UserSpending::sum('total_booking') ?? 0;
        $stats_services_used = DB::table('booking_services')->count();
        $stats_services = Service::count();

        return view('admin.dashboard', [
            'stats_users' => $stats_users,
            'stats_fields' => $stats_fields,
            'stats_bookings' => $stats_bookings,
            'stats_revenue' => $stats_revenue,
            'stats_services_used' => $stats_services_used,
            'stats_services' => $stats_services,
        ]);
    }

    /**
     * Get admin statistics with caching
     */
    public function statistics()
    {
        Cache::forget('admin_statistics');
        
        $stats = Cache::remember('admin_statistics', 300, function() {
            // Basic stats
            $stats_users = User::count();
            $stats_fields = Field::count();
            $stats_bookings = Booking::where('status', 'confirmed')->count();
            $stats_revenue = UserSpending::sum('total_booking') ?? 0;
            $stats_services_used = DB::table('booking_services')->count();
            $stats_services_revenue = DB::table('booking_services')
                ->join('services', 'booking_services.service_id', '=', 'services.id')
                ->sum(DB::raw('booking_services.quantity * services.price')) ?? 0;

            // Service stats by type (Pie Chart)
            $services_by_type = DB::table('booking_services')
                ->join('services', 'booking_services.service_id', '=', 'services.id')
                ->selectRaw('services.name AS service_name, SUM(booking_services.quantity) AS total_used')
                ->groupBy('booking_services.service_id', 'services.name', 'services.id')
                ->get();

            $service_labels = $services_by_type->pluck('service_name')->toArray();
            $service_counts = $services_by_type->pluck('total_used')->map(fn($v) => (int)$v)->toArray();

            // Bookings by month (Last 12 months)
            $bookings_by_month = Booking::where('status', 'confirmed')
                ->selectRaw("DATE_FORMAT(booking_date, '%m/%Y') as month, COUNT(*) as count, SUM(total_price) as revenue")
                ->groupByRaw("DATE_FORMAT(booking_date, '%m/%Y')")
                ->orderByRaw("STR_TO_DATE(CONCAT('01/', DATE_FORMAT(booking_date, '%m/%Y')), '%d/%m/%Y') DESC")
                ->limit(12)
                ->get();

            $labels_month = [];
            $counts_month = [];
            $revenues_month = [];

            foreach ($bookings_by_month as $row) {
                $labels_month[] = $row->month;
                $counts_month[] = (int)$row->count;
                $revenues_month[] = (float)$row->revenue;
            }

            $labels_month = array_reverse($labels_month);
            $counts_month = array_reverse($counts_month);
            $revenues_month = array_reverse($revenues_month);

            // Service revenue by month
            $service_revenue = DB::table('booking_services')
                ->join('bookings', 'booking_services.booking_id', '=', 'bookings.id')
                ->join('services', 'booking_services.service_id', '=', 'services.id')
                ->selectRaw("DATE_FORMAT(bookings.booking_date, '%m/%Y') AS month, SUM(booking_services.quantity * services.price) AS revenue, DATE_FORMAT(bookings.booking_date, '%Y-%m') as sort_month")
                ->groupBy('month', 'sort_month')
                ->orderBy('sort_month')
                ->get();

            $service_revenue_labels = $service_revenue->pluck('month')->toArray();
            $service_revenue_values = $service_revenue->pluck('revenue')->map(fn($v) => (int)$v)->toArray();

            // Field status
            $field_types = Field::selectRaw('status, COUNT(*) AS total')
                ->groupBy('status')
                ->get();

            $field_type_labels = $field_types->pluck('status')->toArray();
            $field_type_counts = $field_types->pluck('total')->map(fn($v) => (int)$v)->toArray();

            $stats_services = Service::count();

            return [
                'stats_users' => $stats_users,
                'stats_fields' => $stats_fields,
                'stats_bookings' => $stats_bookings,
                'stats_revenue' => $stats_revenue,
                'stats_services_used' => $stats_services_used,
                'stats_services_revenue' => $stats_services_revenue,
                'service_labels' => $service_labels,
                'service_counts' => $service_counts,
                'labels_month' => $labels_month,
                'counts_month' => $counts_month,
                'revenues_month' => $revenues_month,
                'service_revenue_labels' => $service_revenue_labels,
                'service_revenue_values' => $service_revenue_values,
                'field_types' => $field_type_labels,
                'field_types_counts' => $field_type_counts,
                'stats_services' => $stats_services,
            ];
        });

        return view('admin.statistics', [...$stats]);
    }

    /**
     * Manage bookings with status filtering
     */
   public function manageBookings(Request $request)
{
    $filter_status = $request->get('status');

    $query = Booking::with(['user', 'field']);

    if (!empty($filter_status)) {
        $query->where('status', $filter_status);
    }

    $bookings = $query->orderBy('created_at', 'desc')->paginate(15);

    return view('admin.manage-bookings', [
        'bookings' => $bookings,
        'filterStatus' => $filter_status,
    ]);
}

    /**
     * Update booking status with validation
     */
  public function updateBookingStatus(Request $request)
{
    try {
        $booking = Booking::find($request->booking_id);

        if (!$booking) {
            return back()->with('error', 'Đơn đặt sân không tồn tại hoặc đã bị xoá.');
        }

        $current = $booking->status;
        $new = $request->status;

        $order = [
            'pending' => 1,
            'confirmed' => 2,
            'in_progress' => 3,
            'completed' => 4,
            'cancelled' => 5,
            'expired' => 6
        ];

        // trạng thái cố định không cho sửa
        if (in_array($current, ['completed', 'cancelled'])) {
            return back()->with('error', 'Đơn này đã kết thúc và không thể thay đổi.');
        }

        // không cho quay ngược trạng thái
        if (isset($order[$new], $order[$current]) && $order[$new] < $order[$current]) {
            return back()->with('error', 'Không thể chuyển trạng thái về bước trước.');
        }

        // logic đặc biệt
        if ($new === 'in_progress') {
            $start = Carbon::parse($booking->booking_date . ' ' . $booking->start_time);

            if (now()->lt($start)) {
                return back()->with('error', 'Chưa đến giờ bắt đầu trận đấu.');
            }
        }

        if ($new === 'completed') {
            $end = Carbon::parse($booking->booking_date . ' ' . $booking->end_time);

            if (now()->lt($end)) {
                return back()->with('error', 'Trận đấu chưa kết thúc.');
            }
        }

        $booking->update(['status' => $new]);

        // cộng doanh thu khi hoàn thành
        if ($new === 'completed' && $current !== 'completed') {
            UserSpending::updateOrCreate(
                ['user_id' => $booking->user_id],
                [
                    'total_booking' => DB::raw("total_booking + {$booking->total_price}"),
                    'last_update' => now(),
                ]
            );
        }

        Cache::forget('admin_statistics');

        return back()->with('success', 'Cập nhật trạng thái thành công.');
    } catch (\Throwable $e) {
        // log thật, user không thấy lỗi hệ thống
        Log::error('Update booking status error: ' . $e->getMessage());

        return back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau.');
    }
}
    /**
     * Manage fields
     */
    public function manageFields()
    {
        $fields = Field::orderBy('name')->paginate(15);
        return view('admin.manage-fields', ['fields' => $fields]);
    }

    /**
     * Update admin profile
     */

public function editStatusOrder($id)
{
    $order = Order::with(['user', 'items.service'])->find($id);

    if (!$order) {
        return back()->with('error', 'Đơn hàng không tồn tại!');
    }

    return view('admin.edit-status', compact('order'));
}

/**
 * Update order status with validation
 */
public function updateOrderStatus(Request $request)
{
    try {
        $request->validate([
            'order_id' => 'required|integer',
            'status' => 'required|string'
        ]);

        $order = Order::find($request->order_id);

        if (!$order) {
            return back()->with('error', 'Đơn hàng không tồn tại.');
        }

        $current = $order->status;
        $new = $request->status;

        $validStatus = [
            'pending',
            'confirmed',
            'in_progress',
            'completed',
            'cancelled'
        ];

        if (!in_array($new, $validStatus)) {
            return back()->with('error', 'Trạng thái không hợp lệ.');
        }

        $orderFlow = [
            'pending' => 1,
            'confirmed' => 2,
            'in_progress' => 3,
            'completed' => 4,
            'cancelled' => 5,
        ];

        // trạng thái kết thúc
        if (in_array($current, ['completed', 'cancelled'])) {
            return back()->with('error', 'Đơn hàng đã kết thúc.');
        }

        // không cho quay lui
        if (
            isset($orderFlow[$current], $orderFlow[$new]) &&
            $orderFlow[$new] < $orderFlow[$current]
        ) {
            return back()->with('error', 'Không thể lùi trạng thái.');
        }

        // update status
        $order->update([
            'status' => $new
        ]);

        // cộng doanh thu khi hoàn thành
        if ($new === 'completed' && $current !== 'completed') {

            $spending = UserSpending::firstOrCreate(
                ['user_id' => $order->user_id],
                ['total_booking' => 0]
            );

            $spending->total_booking += $order->total_amount;
            $spending->last_update = now();
            $spending->save();
        }

        Cache::forget('admin_statistics');

        return back()->with('success', 'Cập nhật trạng thái thành công.');

    } catch (\Throwable $e) {

        Log::error('Order status error: ' . $e->getMessage());

        return back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }
}


public function updateOrderItemsStatus(Request $request)
{
    $request->validate([
        'order_id' => 'required|exists:orders,id',
        'items' => 'required|array',
    ]);

$validStatus = [
    'pending',
    'confirmed',
    'processing',
    'completed',
    'cancelled'
];
    $order = Order::find($request->order_id);

    DB::transaction(function () use ($request, $order, $validStatus) {

       foreach ($request->items as $itemId => $status) {

   $f = DB::table('order_items')
        ->where('id', $itemId)
        ->update([
            'status' => $status
        ]);
}

    });
  return back()->with('success', 'Cập nhật dịch vụ thành công');
}
public function updateProfile(Request $request)
{
    $admin = Auth::user();

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $admin->id,
        'phone' => 'nullable|string|max:20',
        'avt' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    // update thông tin cơ bản
    $admin->name = $validated['name'];
    $admin->email = $validated['email'];
    $admin->phone = $validated['phone'];

    // upload avatar
  if ($request->hasFile('avt')) {

    if ($admin->avt && file_exists(public_path('uploads/avatars/'.$admin->avt))) {
        unlink(public_path('uploads/avatars/'.$admin->avt));
    }

    $file = $request->file('avt');

    $filename = time().'_'.$file->getClientOriginalName();

    $file->move(public_path('uploads/avatars'), $filename);

    $admin->avt = $filename;
}
    // đổi mật khẩu nếu nhập
    if ($request->filled('password')) {

        if (!Hash::check($request->current_password, $admin->password)) {

            return back()->withErrors([
                'current_password' => 'Mật khẩu hiện tại không đúng'
            ]);
        }

        $request->validate([
            'password' => 'required|confirmed|min:6'
        ]);

        $admin->password = bcrypt($request->password);
    }

    $admin->save();

    return back()->with('success', 'Cập nhật thông tin thành công!');
}
    /**
     * Store new field
     */
  public function storeField(Request $request)
{
    try {
        $validated = $request->validate([
            'name' => 'required|string',
            'location' => 'required|string',
            'description' => 'nullable|string',
            'price_per_hour' => 'required|numeric',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('image')) {
            $image_name = time() . "_" . rand(1000, 9999) . "." . $request->file('image')->extension();
            $request->file('image')->move(public_path('uploads/fields'), $image_name);
            $validated['image'] = $image_name;
        }

        Field::create($validated);

        Cache::forget('admin_statistics');

        return redirect()->back()->with('success', 'Thêm sân thành công!');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Có lỗi xảy ra khi thêm sân!');
    }
}

    /**
     * Update field
     */
  public function updateField(Request $request)
{
    try {
        $field = Field::find($request->input('id'));

        if (!$field) {
            return redirect()->back()->with('error', 'Không tìm thấy sân!');
        }

        $validated = $request->validate([
            'name' => 'required|string',
            'location' => 'required|string',
            'description' => 'nullable|string',
            'price_per_hour' => 'required|numeric',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('image')) {
            if ($field->image && file_exists(public_path('uploads/fields/' . $field->image))) {
                unlink(public_path('uploads/fields/' . $field->image));
            }

            $image_name = time() . "_" . rand(1000, 9999) . "." . $request->file('image')->extension();
            $request->file('image')->move(public_path('uploads/fields'), $image_name);
            $validated['image'] = $image_name;
        }

        $field->update($validated);

        Cache::forget('admin_statistics');

        return redirect()->back()->with('success', 'Cập nhật sân thành công!');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Có lỗi xảy ra khi cập nhật sân!');
    }
}
    /**
     * Delete field
     */
   public function deleteField(Request $request)
{
    try {
        $field = Field::find($request->input('id'));

        if (!$field) {
            return redirect()->back()->with('error', 'Không tìm thấy sân!');
        }

        if ($field->image && file_exists(public_path('uploads/fields/' . $field->image))) {
            unlink(public_path('uploads/fields/' . $field->image));
        }

        $field->delete();

        Cache::forget('admin_statistics');

        return redirect()->back()->with('success', 'Xóa sân thành công!');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Có lỗi xảy ra khi xóa sân!');
    }
}

    /**
     * Manage services
     */
    public function manageServices()
    {
        $services = Service::with('category')->orderBy('name')->paginate(15);
        $categories = Category::all();
        return view('admin.manage-services', compact('services', 'categories'));
    }

    /**
     * Store new service
     */
  
public function storeService(Request $request)
{
    $validated = $request->validate([
        'category_id' => 'required|integer|exists:categories,id',
        'name' => 'required|string',
        'price' => 'required|numeric|min:0|max:9999999999999',
        'quantity' => 'required|integer|min:0',
        'status' => 'required|in:active,inactive',
        'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,avif|max:4096'
    ]);

    try {
        if ($request->hasFile('image')) {
            $image_name = time() . "_" . rand(1000, 9999) . "." . $request->file('image')->extension();
            $request->file('image')->move(public_path('uploads/services'), $image_name);
            $validated['image'] = $image_name;
        }

        Service::create($validated);

        Cache::forget('admin_statistics');

        return back()->with('success', 'Thêm dịch vụ thành công!');
    } catch (\Exception $e) {
        Log::error('Store service error: ' . $e->getMessage(), [
            'line' => $e->getLine()
        ]);

        return back()->with('error', 'Không thể thêm dịch vụ, vui lòng thử lại!');
    }
}
    /**
     * Update service
     */
public function updateService(Request $request)
{
    $service = Service::find($request->input('id'));

    if (!$service) {
        return back()->with('error', 'Không tìm thấy dịch vụ!');
    }

    $validated = $request->validate([
        'category_id' => 'required|integer|exists:categories,id',
        'name' => 'required|string',
        'price' => 'required|numeric|min:0',
        'quantity' => 'required|integer|min:0',
        'status' => 'required|in:active,inactive',
        'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,avif|max:4096'
    ]);

    try {
        // xử lý file (có thể lỗi)
        if ($request->hasFile('image')) {
            if ($service->image && file_exists(public_path('uploads/services/' . $service->image))) {
                unlink(public_path('uploads/services/' . $service->image));
            }

            $image_name = time() . "_" . rand(1000, 9999) . "." . $request->file('image')->extension();
            $request->file('image')->move(public_path('uploads/services'), $image_name);

            $validated['image'] = $image_name;
        }

        // DB update (có thể lỗi SQL)
        $service->update($validated);

        Cache::forget('admin_statistics');

        return back()->with('success', 'Cập nhật dịch vụ thành công!');
    } catch (\Exception $e) {
        return back()->with('error', 'Có lỗi khi cập nhật dữ liệu!');
    }
}

    /**
     * Delete service
     */
  public function deleteService(Request $request)
{
    $service = Service::find($request->input('id'));

    if (!$service) {
        return back()->with('error', 'Không tìm thấy dịch vụ!');
    }

    try {
        if ($service->image && file_exists(public_path('uploads/services/' . $service->image))) {
            unlink(public_path('uploads/services/' . $service->image));
        }

        $service->delete();

        Cache::forget('admin_statistics');

        return back()->with('success', 'Xóa dịch vụ thành công!');
    } catch (\Exception $e) {
        return back()->with('error', 'Không thể xóa dịch vụ!');
    }
}

    /**
     * Category management methods
     */
    public function manageCategories()
    {
        $categories = Category::withCount('services')->orderBy('id')->paginate(15);
        return view('admin.manage-categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        Category::create($validated);
        return back()->with('success', 'Thêm danh mục thành công!');
    }

    public function updateCategory(Request $request)
    {
        $category = Category::findOrFail($request->input('id'));
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);

        $category->update($validated);
        return back()->with('success', 'Cập nhật danh mục thành công!');
    }

    public function deleteCategory(Request $request)
    {
        $id = $request->input('id');
        if ($id == 1) {
            return back()->with('error', 'Không thể xóa danh mục mặc định!');
        }

        $category = Category::findOrFail($id);
        
        // Move all services in this category to default category (ID = 1)
        Service::where('category_id', $category->id)->update(['category_id' => 1]);

        $category->delete();
        return back()->with('success', 'Xóa danh mục thành công!');
    }

public function manageOrders(Request $request)
{
    $query = Order::with(['user', 'items.service', 'shipment']);

    // FILTER USER
    if ($request->filled('user_id')) {
        $query->where('user_id', $request->user_id);
    }

    // FILTER STATUS
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // SEARCH (thêm nếu cần tìm nhanh)
    if ($request->filled('search')) {
        $keyword = $request->search;

        $query->where(function ($q) use ($keyword) {
            $q->where('id', $keyword)
              ->orWhereHas('user', function ($u) use ($keyword) {
                  $u->where('name', 'like', "%$keyword%")
                    ->orWhere('email', 'like', "%$keyword%");
              });
        });
    }

    $orders = $query->orderByDesc('created_at')
        ->paginate(15)
        ->appends($request->all()); // 🔥 GIỮ FILTER KHI CHUYỂN PAGE

    $users = User::orderBy('name')->get();

    return view('admin.manage-orders', compact(
        'orders',
        'users'
    ));
} 

    /**
     * Admin: view shipment map for an order
     */
    public function viewShipment(Order $order, \App\Services\ShippingService $shipping)
    {
        $shipment = $shipping->ensureShipmentForOrder($order);

        return view('admin.order-shipment', [
            'order'    => $order->loadMissing(['user', 'items.service', 'userAddress']),
            'shipment' => $shipment,
            'tracking' => $shipping->trackingPayload($shipment),
        ]);
    }

    /**
     * Admin: update shipment status (AJAX)
     */
    public function updateShipmentStatus(Request $request, Order $order, \App\Services\ShippingService $shipping)
    {
        $data = $request->validate([
            'status' => 'required|in:' . implode(',', \App\Models\OrderShipment::STATUSES),
        ]);

        $shipment = $shipping->ensureShipmentForOrder($order);
        $shipment = $shipping->updateStatus($shipment, $data['status']);

        return response()->json($shipping->trackingPayload($shipment));
    }

    /**
     * User service history
     */
    public function userServiceHistory()
    {
        

        return view('admin.user_service_history', ['data' => $data]);
    }

    /**
     * Manage feedback
     */
   public function manageFeedback() 
{ 
    // Feedback dịch vụ
    $serviceFeedbacks = DB::table('feedbacks as f')
        ->join('services as s', 's.id', '=', 'f.service_id')
        ->join('users as u', 'u.id', '=', 'f.user_id')
        ->whereNotNull('f.service_id')
        ->select([
            'f.id as feedback_id',
            'u.name as user_name',
            's.id as service_id',
            's.name as service_name',
            's.image as service_image',
            'f.message as feedback_message',
            'f.admin_reply',
            'f.replied_at',
            'f.rating as feedback_rating',
            'f.created_at'
        ])
        ->orderByDesc('f.id')
        ->get();

    // Feedback booking
    $bookingFeedbacks = DB::table('feedbacks as fb')
        ->join('bookings as b', 'b.id', '=', 'fb.booking_id')
        ->join('users as u', 'u.id', '=', 'fb.user_id')
        ->join('fields as f', 'f.id', '=', 'b.field_id')
        ->select([
            'fb.id as feedback_id',
            'b.id as booking_id',
            'u.name as user_name',
            'f.name as field_name',
            'f.image as field_image',
            'b.booking_date',
            'b.start_time',
            'b.end_time',
            'fb.message as feedback_message',
            'fb.rating as feedback_rating',
            'fb.created_at'
        ])
        ->orderByDesc('fb.id')
        ->get();
    
    return view('admin.manage-feedback', compact('serviceFeedbacks', 'bookingFeedbacks')); 
}

public function replyFeedback(Request $request, Feedback $feedback)
{
    $data = $request->validate([
        'admin_reply' => ['required', 'string', 'max:2000'],
    ]);

    $feedback->update([
        'admin_reply' => trim($data['admin_reply']),
        'replied_by' => Auth::id(),
        'replied_at' => now(),
    ]);

    return back()->with('success', 'Đã trả lời feedback.');
}
    /**
     * Invoices
     */
public function invoices()
{
    $invoices = Invoice::with([
        'booking.user',
        'booking.field',
        'order.user',
        'order.items.service'
    ])->latest()->get();

    return view('admin.invoices', compact('invoices'));
}

    /**
     * Edit status (placeholder)
     */
    public function editStatus()
    {
        return view('admin.edit-status');
    }

    /**
     * About (placeholder)
     */
    public function about()
    {
        return view('admin.about');
    }

    /**
     * User profile view
     */
    public function profile()
    {
        $admin = Auth::user();

        $bookingHistory = DB::table('bookings')
            ->join('fields', 'bookings.field_id', '=', 'fields.id')
            ->where('bookings.user_id', $admin->id)
            ->select(
                'fields.name as field_name',
                'bookings.booking_date',
                'bookings.start_time',
                'bookings.end_time',
                'bookings.total_price'
            )
            ->orderByDesc('bookings.booking_date')
            ->get();

        $serviceHistory = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('services', 'order_items.service_id', '=', 'services.id')
            ->where('orders.user_id', $admin->id)
            ->where('orders.status', 'paid')
            ->select(
                'services.name as service_name',
                'orders.created_at',
                DB::raw('(order_items.price * order_items.quantity) as total')
            )
            ->orderByDesc('orders.created_at')
            ->get();

        return view('admin.profile', compact(
            'admin',
            'bookingHistory',
            'serviceHistory'
        ));
    }

    /**
     * Export invoice as PDF
     */
    public function exportInvoice(Request $request)
    {
        $type = $request->get('type', '');
        $id = (int)$request->get('id', 0);
        
        if (empty($type) || $id <= 0) {
            return view('admin.export_invoice');
        }
        
        if (!in_array($type, ['booking', 'service'])) {
            return back()->with('error', 'Loại hóa đơn không hợp lệ');
        }
        
        if ($type === 'booking') {
            $booking = DB::table('bookings')
                ->join('users', 'bookings.user_id', '=', 'users.id')
                ->join('fields', 'bookings.field_id', '=', 'fields.id')
                ->where('bookings.id', $id)
                ->whereIn('bookings.status', ['confirmed', 'completed'])
                ->select('bookings.*', 'users.name as user_name', 'users.email', 'fields.name as field_name', 'fields.location')
                ->first();
            
            if (!$booking) {
                return back()->with('error', 'Không tìm thấy booking hoặc chưa đủ điều kiện xuất hóa đơn');
            }
            
            $services = DB::table('booking_services')
                ->join('services', 'booking_services.service_id', '=', 'services.id')
                ->where('booking_services.booking_id', $id)
                ->select('services.name', 'services.price', 'booking_services.quantity')
                ->get();
            
            $pdf = \PDF::loadView('admin.pdf.invoice-booking', [
                'booking' => $booking,
                'services' => $services
            ]);
            Invoice::create([
                'booking_id'   => $booking->id,
                'invoice_code' => 'INV-' . time(),
                'total_amount' => $booking->total_price,
                'issued_at'    => now()
            ]);
            return $pdf->download('hoa-don-booking-' . $id . '.pdf');
        }
        
        if ($type === 'service') {

            $order = DB::table('orders')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->where('orders.id', $id)
                ->whereIn('orders.status', ['confirmed', 'completed'])
                ->select('orders.*', 'users.name as user_name', 'users.email')
                ->first();
            
            if (!$order) {
                return back()->with('error', 'Không tìm thấy đơn dịch vụ hoặc chưa đủ điều kiện xuất hóa đơn');
            }
            
            $items = DB::table('order_items')
                ->join('services', 'order_items.service_id', '=', 'services.id')
                ->where('order_items.order_id', $id)
                ->select('services.name', 'order_items.price', 'order_items.quantity')
                ->get();
            
            $pdf = \PDF::loadView('admin.pdf.invoice-service', [
                'order' => $order,
                'items' => $items
            ]);
            
            Invoice::create([
                'order_id'     => $order->id,
                'invoice_code' => 'INV-' . time(),
                'total_amount' => $order->total_amount,
                'issued_at'    => now()
            ]);

            return $pdf->download('hoa-don-dich-vu-' . $id . '.pdf');
        }
    }

    

}
