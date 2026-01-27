<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Field;
use App\Models\User;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use DateTime;
use DateTimeZone;

class BossController extends Controller
{
    public function dashboard() { return view('boss.dashboard'); }
    
    public function manageBookings(Request $request)
    {
        $filterStatus = $request->get('status', '');
        
        $query = Booking::with(['user', 'field'])
            ->select('bookings.*');
        
        if (!empty($filterStatus)) {
            $query->where('status', $filterStatus);
        }
        
        $bookings = $query->orderBy('created_at', 'DESC')->get();
        
        return view('boss.manage-bookings', compact('bookings', 'filterStatus'));
    }
    
    public function updateBookingStatus(Request $request)
    {
        $bookingId = (int)$request->input('booking_id');
        $newStatus = trim($request->input('status'));
        
        $booking = Booking::find($bookingId);
        if (!$booking) {
            return back()->with('error', 'Không tìm thấy đơn đặt sân!');
        }
        
        $currentStatus = $booking->status;
        $statusOrder = [
            'pending'     => 1,
            'confirmed'   => 2,
            'in_progress' => 3,
            'completed'   => 4,
            'cancelled'   => 5,
            'expired'     => 6,
        ];
        
        $allowed = false;
        $error = '';
        
        // Check if booking has time info
        $hasTime = !empty($booking->booking_date) && !empty($booking->start_time) && !empty($booking->end_time);
        $now = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        
        if ($hasTime) {
            $start = new DateTime($booking->booking_date . ' ' . $booking->start_time, new DateTimeZone('Asia/Ho_Chi_Minh'));
            $end = new DateTime($booking->booking_date . ' ' . $booking->end_time, new DateTimeZone('Asia/Ho_Chi_Minh'));
        }
        
        // Status transition rules
        if ($currentStatus === 'completed') {
            $error = "Đơn đã hoàn thành, không thể thay đổi!";
        } elseif ($currentStatus === 'cancelled') {
            $error = "Đơn đã hủy, không thể thay đổi!";
        } elseif ($newStatus === 'cancelled') {
            $allowed = true;
        } elseif ($currentStatus === 'pending' && $newStatus === 'confirmed') {
            $allowed = true;
        } elseif ($newStatus === 'in_progress') {
            if ($hasTime && $now >= $start) {
                $allowed = true;
            } else {
                $error = "Chưa đến giờ bắt đầu trận!";
            }
        } elseif ($newStatus === 'completed') {
            if ($hasTime && $now >= $end) {
                $allowed = true;
            } else {
                $error = "Trận đấu chưa kết thúc!";
            }
        } else {
            if ($statusOrder[$newStatus] < $statusOrder[$currentStatus]) {
                $error = "Không thể quay ngược trạng thái!";
            } else {
                $allowed = true;
            }
        }
        
        if ($allowed) {
            $booking->status = $newStatus;
            $booking->save();
            
            // Add spending if completed
            if ($newStatus === 'completed' && $currentStatus !== 'completed') {
                $userId = $booking->user_id;
                $amount = $booking->total_price;
                
                DB::table('user_spending')
                    ->updateOrInsert(
                        ['user_id' => $userId],
                        ['total_booking' => DB::raw("COALESCE(total_booking, 0) + $amount")]
                    );
            }
            
            return back()->with('success', 'Cập nhật trạng thái thành công!');
        } else {
            return back()->with('error', $error);
        }
    }
    
    function manageUsers()
    {
        $users = User::orderBy('created_at', 'DESC')->get();
        return view('boss.manage-users', compact('users'));
    }
    
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:user,admin,boss',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'password' => bcrypt('123456'),
        ]);

        return back()->with('success', 'Thêm người dùng thành công!');
    }
    
    public function updateUser(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $request->input('id'),
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:user,admin,boss',
        ]);

        $user = User::findOrFail($validated['id']);
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
        ]);

        return back()->with('success', 'Cập nhật người dùng thành công!');
    }
    
    public function deleteUser(Request $request)
    {
        $userId = (int)$request->input('id');
        
        if ($userId == auth()->id()) {
            return back()->with('error', 'Không thể xóa tài khoản Boss hiện tại!');
        }
        
        User::findOrFail($userId)->delete();
        
        return back()->with('success', 'Xóa người dùng thành công!');
    }
    
    public function invoices() { return view('boss.invoices'); }
    public function exportInvoice() { return view('boss.export_invoice'); }
    public function editStatus() { return view('boss.edit-status'); }
    public function about() { return view('boss.about'); }
    public function manageFields()
    {
        $fields = Field::orderBy('name')->get();
        return view('boss.manage-fields', compact('fields'));
    }
    
    public function storeField(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'description' => 'required|string',
            'price_per_hour' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,avif|max:2048',
        ]);

        $imageName = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imageName = time() . '_' . rand(1000, 9999) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/fields'), $imageName);
        }

        Field::create([
            'name' => $validated['name'],
            'location' => $validated['location'],
            'description' => $validated['description'],
            'price_per_hour' => $validated['price_per_hour'],
            'status' => $validated['status'],
            'image' => $imageName,
        ]);

        return back()->with('success', 'Thêm sân thành công!');
    }
    
    public function updateField(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:fields,id',
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'description' => 'required|string',
            'price_per_hour' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,avif|max:2048',
        ]);

        $field = Field::findOrFail($validated['id']);

        $imageName = $field->image;
        if ($request->hasFile('image')) {
            // Delete old image
            if ($field->image && file_exists(public_path('uploads/fields/' . $field->image))) {
                unlink(public_path('uploads/fields/' . $field->image));
            }
            
            $file = $request->file('image');
            $imageName = time() . '_' . rand(1000, 9999) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/fields'), $imageName);
        }

        $field->update([
            'name' => $validated['name'],
            'location' => $validated['location'],
            'description' => $validated['description'],
            'price_per_hour' => $validated['price_per_hour'],
            'status' => $validated['status'],
            'image' => $imageName,
        ]);

        return back()->with('success', 'Cập nhật sân thành công!');
    }
    
    public function deleteField(Request $request)
    {
        $field = Field::findOrFail($request->input('id'));
        
        if ($field->image && file_exists(public_path('uploads/fields/' . $field->image))) {
            unlink(public_path('uploads/fields/' . $field->image));
        }
        
        $field->delete();
        
        return back()->with('success', 'Xóa sân thành công!');
    }
    
    public function manageOrders() { return view('boss.manage-orders'); }
    public function manageServices()
    {
        $services = Service::orderBy('name')->get();
        return view('boss.manage-services', compact('services'));
    }
    
    public function storeService(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,avif|max:2048',
        ]);

        $imageName = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imageName = time() . '_' . rand(1000, 9999) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/services'), $imageName);
        }

        Service::create([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'quantity' => $validated['quantity'],
            'status' => $validated['status'],
            'image' => $imageName,
        ]);

        return back()->with('success', 'Thêm dịch vụ thành công!');
    }
    
    public function updateService(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:services,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,avif|max:2048',
        ]);

        $service = Service::findOrFail($validated['id']);

        $imageName = $service->image;
        if ($request->hasFile('image')) {
            // Delete old image
            if ($service->image && file_exists(public_path('uploads/services/' . $service->image))) {
                unlink(public_path('uploads/services/' . $service->image));
            }
            
            $file = $request->file('image');
            $imageName = time() . '_' . rand(1000, 9999) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/services'), $imageName);
        }

        $service->update([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'quantity' => $validated['quantity'],
            'status' => $validated['status'],
            'image' => $imageName,
        ]);

        return back()->with('success', 'Cập nhật dịch vụ thành công!');
    }
    
    public function deleteService(Request $request)
    {
        $service = Service::findOrFail($request->input('id'));
        
        if ($service->image && file_exists(public_path('uploads/services/' . $service->image))) {
            unlink(public_path('uploads/services/' . $service->image));
        }
        
        $service->delete();
        
        return back()->with('success', 'Xóa dịch vụ thành công!');
    }
    
    public function userServiceHistory() { return view('boss.user_service_history'); }
    public function manageFeedback() { return view('boss.manage-feedback'); }

    public function statistics()
    {
        $stats = Cache::remember('boss.statistics', 300, function () {

            // =======================
            // THỐNG KÊ TỔNG QUAN
            // =======================

            $stats_users = User::count();
            $stats_fields = Field::count();
            $stats_bookings = Booking::where('status', 'confirmed')->count();

            $stats_revenue = DB::table('user_spending')
                ->selectRaw('SUM(total_booking + total_services) as total')
                ->value('total') ?? 0;

            // =======================
            // THỐNG KÊ DỊCH VỤ
            // =======================

            $stats_services_used = DB::table('booking_services')->count();

            $stats_services_revenue = DB::table('booking_services as bs')
                ->join('services as s', 'bs.service_id', '=', 's.id')
                ->selectRaw('SUM(bs.quantity * s.price) as total')
                ->value('total') ?? 0;

            $services_data = DB::table('booking_services as bs')
                ->join('services as s', 'bs.service_id', '=', 's.id')
                ->select('s.name as service_name', DB::raw('SUM(bs.quantity) as total_used'))
                ->groupBy('s.id', 's.name')
                ->get();

            $service_labels = $services_data->pluck('service_name')->toArray();
            $service_counts = $services_data->pluck('total_used')->map(fn($v) => (int)$v)->toArray();

            // =======================
            // BOOKINGS THEO THÁNG
            // =======================

            $bookings_by_month = DB::table('bookings')
                ->where('status', 'confirmed')
                ->selectRaw("
                    DATE_FORMAT(booking_date, '%m/%Y') as month,
                    COUNT(*) as count,
                    SUM(total_price) as revenue,
                    DATE_FORMAT(booking_date, '%Y-%m') as sort_month
                ")
                ->groupBy('month', 'sort_month')
                ->orderBy('sort_month', 'desc')
                ->limit(12)
                ->get()
                ->reverse()
                ->values();

            $labels_month = $bookings_by_month->pluck('month')->toArray();
            $counts_month = $bookings_by_month->pluck('count')->map(fn($v) => (int)$v)->toArray();
            $revenues_month = $bookings_by_month->pluck('revenue')->map(fn($v) => (float)$v)->toArray();

            // ==========================
            // DOANH THU DỊCH VỤ THEO THÁNG
            // ==========================

            $service_revenue_data = DB::table('booking_services as bs')
                ->join('bookings as b', 'bs.booking_id', '=', 'b.id')
                ->join('services as s', 'bs.service_id', '=', 's.id')
                ->selectRaw("
                    DATE_FORMAT(b.booking_date, '%m/%Y') as month,
                    SUM(bs.quantity * s.price) as revenue,
                    DATE_FORMAT(b.booking_date, '%Y-%m') as sort_month
                ")
                ->groupBy('month', 'sort_month')
                ->orderBy('sort_month')
                ->get();

            $service_revenue_labels = $service_revenue_data->pluck('month')->toArray();
            $service_revenue_values = $service_revenue_data->pluck('revenue')->map(fn($v) => (int)$v)->toArray();

            // =======================
            // LOẠI SÂN
            // =======================

            $fields_by_status = DB::table('fields')
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->get();

            $field_types = $fields_by_status->pluck('status')->toArray();
            $field_types_counts = $fields_by_status->pluck('total')->map(fn($v) => (int)$v)->toArray();

            $stats_services = Service::count();

            return compact(
                'stats_users',
                'stats_fields',
                'stats_bookings',
                'stats_revenue',
                'stats_services_used',
                'stats_services_revenue',
                'service_labels',
                'service_counts',
                'labels_month',
                'counts_month',
                'revenues_month',
                'service_revenue_labels',
                'service_revenue_values',
                'field_types',
                'field_types_counts',
                'stats_services'
            );
        });

        return view('boss.statistics', $stats);
    }
    
    public function invoices()
    {
        $orders = DB::table('orders')->orderBy('created_at', 'DESC')->paginate(20);
        return view('boss.invoices', compact('orders'));
    }
    
    public function exportInvoice()
    {
        return view('boss.export_invoice');
    }
    
    public function editStatus()
    {
        return view('boss.edit-status');
    }
    
    public function about()
    {
        return view('boss.about');
    }
    
    public function manageOrders()
    {
        $orders = DB::table('orders')->orderBy('created_at', 'DESC')->paginate(20);
        return view('boss.manage-orders', compact('orders'));
    }
    
    public function userServiceHistory()
    {
        return view('boss.user_service_history');
    }
    
    public function manageFeedback()
    {
        $feedbacks = DB::table('feedbacks')->orderBy('created_at', 'DESC')->paginate(20);
        return view('boss.manage-feedback', compact('feedbacks'));
    }
}
