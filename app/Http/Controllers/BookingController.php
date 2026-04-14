<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Field;
use App\Models\Service;
use App\Models\Booking;

class BookingController extends Controller
{
    public function create(Request $request)
    {
        $fieldId = $request->query('field_id');
        $field = $fieldId ? Field::find($fieldId) : null;
        $services = Service::where('status','active')->get();
        return view('user.booking', ['field' => $field, 'services' => $services]);
    }

    public function store(Request $request)
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
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để đặt sân.');
        }

        DB::transaction(function () use ($data, $user, $request) {
            $field = Field::findOrFail($data['field_id']);
            $totalPrice = $field->price_per_hour * (strtotime($data['end_time']) - strtotime($data['start_time'])) / 3600;

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
                    DB::table('booking_services')->insert([
                        'booking_id' => $booking->id,
                        'service_id' => $serviceId,
                        'quantity' => $qty,
                    ]);
                }
            }
        });

        return redirect()->route('booking.create', ['field_id' => $data['field_id']])->with('success', 'Đặt sân thành công.');
    }
public function searchBookings(Request $request)
{
    $query = Booking::with(['service', 'order'])
        ->whereHas('order', function ($q) {
            $q->where('user_id', auth()->id());
        });

    // tìm theo tên service
    if ($request->keyword) {
        $query->whereHas('service', function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->keyword . '%');
        });
    }

    // lọc theo trạng thái order
    if ($request->status) {
        $query->whereHas('order', function ($q) use ($request) {
            $q->where('status', $request->status);
        });
    }

    $myBookings = $query->latest()
        ->paginate(10)
        ->withQueryString()
        ->withPath(route('user.bookings.search'));

    // thêm ảnh + tổng tiền
    $myBookings->getCollection()->transform(function ($item) {

        $item->image = $item->service->image ?? null;

        $item->total_amount =
            ($item->price ?? 0) * ($item->quantity ?? 1);

        return $item;
    });

    // ajax render table
    if ($request->ajax() || 
        $request->header('X-Requested-With') === 'XMLHttpRequest') {

        return view('user.booking-table', compact('myBookings'))->render();
    }

    $filterStatus = $request->status ?? null;

    return view('user.my-bookings', compact('myBookings', 'filterStatus'));
}
   public function bookingdetail($id)
{
    return view('user.booking-detail', compact('id'));
}

   
    public function myBookings(Request $request)
    {
        return view('user.my-bookings');
    }
    public function searchBooking(Request $request)
{
    $keyword = $request->keyword;
    $status = $request->status;

    $query = Booking::with(['order', 'field']);

    // 🔎 tìm theo tên sân (service bên dịch vụ)
    if ($keyword) {
        $query->whereHas('field', function ($q) use ($keyword) {
            $q->where('name', 'like', '%' . $keyword . '%');
        });
    }

    // 🔎 lọc theo trạng thái order
    if ($status) {
        $query->whereHas('order', function ($q) use ($status) {
            $q->where('status', $status);
        });
    }

    $myBookings = $query
        ->where('user_id', auth()->id()) // 👈 QUAN TRỌNG: chỉ lấy booking của user
        ->orderByDesc('created_at')
        ->paginate(5);

    if ($request->ajax()) {
        return view('user.booking-table', compact('myBookings'));
    }

    return view('user.my-bookings', compact('myBookings'));
}
}
