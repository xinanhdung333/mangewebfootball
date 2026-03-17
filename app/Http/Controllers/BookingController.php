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

   public function bookingdetail($id)
{
    return view('user.booking-detail', compact('id'));
}

    public function fieldSchedule(Request $request)
    {
        $fields = Field::all();
        return view('user.field-schedule', ['fields' => $fields]);
    }

    public function myBookings(Request $request)
    {
        return view('user.my-bookings');
    }
}
