<?php

namespace App\Http\Controllers;

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

class AdminController extends Controller
{
    /**
     * Display admin dashboard with key statistics
     */
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
                ->selectRaw("DATE_FORMAT(bookings.booking_date, '%m/%Y') AS month, SUM(booking_services.quantity * services.price) AS revenue")
                ->groupByRaw("DATE_FORMAT(bookings.booking_date, '%Y-%m')")
                ->orderByRaw("DATE_FORMAT(bookings.booking_date, '%Y-%m')")
                ->get();

            $service_revenue_labels = $service_revenue->pluck('month')->toArray();
            $service_revenue_values = $service_revenue->pluck('revenue')->map(fn($v) => (int)$v)->toArray();

            // Field status
            $field_types = Field::selectRaw('status, COUNT(*) AS total')
                ->groupBy('status')
                ->get();

            $field_type_labels = $field_types->pluck('status')->toArray();
            $field_type_counts = $field_types->pluck('total')->map(fn($v) => (int)$v)->toArray();

            return [
                'users' => $stats_users,
                'fields' => $stats_fields,
                'bookings' => $stats_bookings,
                'revenue' => $stats_revenue,
                'services_used' => $stats_services_used,
                'services_revenue' => $stats_services_revenue,
                'service_labels' => $service_labels,
                'service_counts' => $service_counts,
                'labels_month' => $labels_month,
                'counts_month' => $counts_month,
                'revenues_month' => $revenues_month,
                'service_revenue_labels' => $service_revenue_labels,
                'service_revenue_values' => $service_revenue_values,
                'field_types' => $field_type_labels,
                'field_type_counts' => $field_type_counts,
            ];
        });

        return view('admin.statistics', $stats);
    }

    /**
     * Manage bookings with status filtering
     */
    public function manageBookings(Request $request)
    {
        $filter_status = $request->get('status', '');

        $query = Booking::with(['user', 'field']);

        if ($filter_status !== "") {
            $query->where('status', $filter_status);
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.manage-bookings', [
            'bookings' => $bookings,
            'filter_status' => $filter_status,
        ]);
    }

    /**
     * Update booking status with validation
     */
    public function updateBookingStatus(Request $request)
    {
        $booking_id = $request->input('booking_id');
        $new_status = $request->input('status');

        $booking = Booking::find($booking_id);

        if (!$booking) {
            return redirect()->back()->with('error', 'Không tìm thấy đơn đặt sân!');
        }

        $current_status = $booking->status;

        $status_order = [
            'pending' => 1,
            'confirmed' => 2,
            'in_progress' => 3,
            'completed' => 4,
            'cancelled' => 5,
            'expired' => 6
        ];

        $allowed = false;
        $error = null;

        // Validate status
        if ($current_status === 'completed') {
            $error = "Đơn đã hoàn thành, không thể thay đổi!";
        } elseif ($current_status === 'cancelled') {
            $error = "Đơn đã hủy, không thể thay đổi!";
        } elseif ($new_status === 'cancelled') {
            $allowed = true;
        } elseif ($current_status === 'pending' && $new_status === 'confirmed') {
            $allowed = true;
        } elseif ($new_status === 'in_progress') {
            if ($booking->booking_date && $booking->start_time) {
                $start = Carbon::parse($booking->booking_date . ' ' . $booking->start_time);
                $now = Carbon::now();
                if ($now >= $start) {
                    $allowed = true;
                } else {
                    $error = "Chưa đến giờ bắt đầu trận!";
                }
            }
        } elseif ($new_status === 'completed') {
            if ($booking->booking_date && $booking->end_time) {
                $end = Carbon::parse($booking->booking_date . ' ' . $booking->end_time);
                $now = Carbon::now();
                if ($now >= $end) {
                    $allowed = true;
                } else {
                    $error = "Trận đấu chưa kết thúc!";
                }
            }
        } else {
            if ($status_order[$new_status] < $status_order[$current_status]) {
                $error = "Không thể quay ngược trạng thái!";
            } else {
                $allowed = true;
            }
        }

        if (!$allowed) {
            return redirect()->back()->with('error', $error ?? 'Không thể cập nhật trạng thái!');
        }

        $booking->update(['status' => $new_status]);

        // Add to user spending when completed
        if ($new_status === 'completed' && $current_status !== 'completed') {
            UserSpending::updateOrCreate(
                ['user_id' => $booking->user_id],
                [
                    'total_booking' => DB::raw("total_booking + " . $booking->total_price),
                    'last_update' => now(),
                ]
            );
        }

        // Clear statistics cache
        Cache::forget('admin_statistics');

        return redirect()->back()->with('success', 'Cập nhật trạng thái thành công!');
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
     * Store new field
     */
    public function storeField(Request $request)
    {
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
    }

    /**
     * Update field
     */
    public function updateField(Request $request)
    {
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
            // Delete old image
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
    }

    /**
     * Delete field
     */
    public function deleteField(Request $request)
    {
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
    }

    /**
     * Manage services
     */
    public function manageServices()
    {
        $services = Service::orderBy('name')->paginate(15);
        return view('admin.manage-services', ['services' => $services]);
    }

    /**
     * Store new service
     */
    public function storeService(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'quantity' => 'required|integer',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('image')) {
            $image_name = time() . "_" . rand(1000, 9999) . "." . $request->file('image')->extension();
            $request->file('image')->move(public_path('uploads/services'), $image_name);
            $validated['image'] = $image_name;
        }

        Service::create($validated);

        Cache::forget('admin_statistics');

        return redirect()->back()->with('success', 'Thêm dịch vụ thành công!');
    }

    /**
     * Update service
     */
    public function updateService(Request $request)
    {
        $service = Service::find($request->input('id'));

        if (!$service) {
            return redirect()->back()->with('error', 'Không tìm thấy dịch vụ!');
        }

        $validated = $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'quantity' => 'required|integer',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('image')) {
            if ($service->image && file_exists(public_path('uploads/services/' . $service->image))) {
                unlink(public_path('uploads/services/' . $service->image));
            }
            $image_name = time() . "_" . rand(1000, 9999) . "." . $request->file('image')->extension();
            $request->file('image')->move(public_path('uploads/services'), $image_name);
            $validated['image'] = $image_name;
        }

        $service->update($validated);

        Cache::forget('admin_statistics');

        return redirect()->back()->with('success', 'Cập nhật dịch vụ thành công!');
    }

    /**
     * Delete service
     */
    public function deleteService(Request $request)
    {
        $service = Service::find($request->input('id'));

        if (!$service) {
            return redirect()->back()->with('error', 'Không tìm thấy dịch vụ!');
        }

        if ($service->image && file_exists(public_path('uploads/services/' . $service->image))) {
            unlink(public_path('uploads/services/' . $service->image));
        }

        $service->delete();

        Cache::forget('admin_statistics');

        return redirect()->back()->with('success', 'Xóa dịch vụ thành công!');
    }

    /**
     * Manage orders (user spending)
     */
    public function manageOrders(Request $request)
    {
        $filter_user = $request->get('user_id', '');

        $query = UserSpending::with('user');

        if ($filter_user !== "") {
            $query->where('user_id', $filter_user);
        }

        $orders = $query->orderBy('last_update', 'desc')->paginate(15);
        $users = User::orderBy('name')->get();

        return view('admin.manage-orders', [
            'orders' => $orders,
            'users' => $users,
            'filter_user' => $filter_user,
        ]);
    }

    /**
     * User service history
     */
    public function userServiceHistory()
    {
        $history = DB::table('booking_services')
            ->join('bookings', 'booking_services.booking_id', '=', 'bookings.id')
            ->join('services', 'booking_services.service_id', '=', 'services.id')
            ->join('users', 'bookings.user_id', '=', 'users.id')
            ->select('users.name', 'services.name as service_name', 'booking_services.quantity', 'services.price', 'bookings.booking_date')
            ->orderBy('bookings.booking_date', 'desc')
            ->paginate(15);

        return view('admin.user_service_history', ['history' => $history]);
    }

    /**
     * Manage feedback
     */
    public function manageFeedback()
    {
        $services_feedback = Feedback::with(['user', 'service'])
            ->where('service_id', '!=', null)
            ->orderBy('id', 'desc')
            ->get();

        $bookings_feedback = Feedback::with(['user', 'booking.field'])
            ->where('booking_id', '!=', null)
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.manage-feedback', [
            'services_feedback' => $services_feedback,
            'bookings_feedback' => $bookings_feedback,
        ]);
    }

    /**
     * Invoices (placeholder)
     */
    public function invoices()
    {
        return view('admin.invoices');
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
}
