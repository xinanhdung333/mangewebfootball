<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Field;
use App\Models\Service;
use App\Models\Booking;
use App\Models\OrderItem; 

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
public function searchBooking(Request $request)
{
    $query = Booking::with(['field', 'payment'])
        ->where('user_id', auth()->id());

    if ($request->keyword) {
        $query->whereHas('field', function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->keyword . '%');
        });
    }

    if ($request->status) {
        $query->where('status', $request->status);
    }

    $myBookings = $query->latest()
        ->paginate(5)
        ->withQueryString()
        ->withPath(route('user.search.Booking'));

    $pendingCount = Booking::where('user_id', auth()->id())
        ->where('status', 'pending')
        ->count();

    $paidCount = Booking::where('user_id', auth()->id())
        ->where('status', 'confirmed')
        ->count();

    if ($request->ajax() || $request->boolean('partial')) {
        return view(
            'user.booking-table',
            compact(
                'myBookings',
                'pendingCount',
                'paidCount'
            )
        )->render();
    }

    $filterStatus = $request->status ?? null;
    $keyword = $request->keyword ?? null;
    $method_payment = $request->payment_method ?? null;
    return view(
        'user.my-bookings',
        compact(
            'myBookings',
            'pendingCount',
            'paidCount',
            'filterStatus',
            'keyword',
            'method_payment'
        )
    );
}
   public function bookingdetail($id)
{
    return view('user.booking-detail', compact('id'));
}

   
    // public function myBookings(Request $request)
    // {
    //     return view('user.my-bookings');
    // }
    public function myBookings()
{
    $request = request();
    $filterStatus = $request->query('status');
    $keyword = $request->query('keyword');

    $query = Booking::with(['field', 'payment'])
        ->where('user_id', auth()->id());

    if ($keyword) {
        $query->whereHas('field', function ($q) use ($keyword) {
            $q->where('name', 'like', '%' . $keyword . '%');
        });
    }

    if ($filterStatus) {
        $query->where('status', $filterStatus);
    }

    $myBookings = $query->latest()
        ->paginate(5)
        ->withQueryString();

    $pendingCount = Booking::where('user_id', auth()->id())
        ->where('status', 'pending')
        ->count();

    $paidCount = Booking::where('user_id', auth()->id())
        ->where('status', 'confirmed')
        ->count();

    return view(
        'user.my-bookings',
        compact('filterStatus', 'keyword', 'myBookings', 'pendingCount', 'paidCount')
    );
}

public function searchBookings(Request $request)
{
    return $this->searchBooking($request);
}
}
