<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Field;

class BookingController extends Controller
{
    public function create(Request $request)
    {
        $fieldId = $request->query('field_id');
        $field = $fieldId ? Field::find($fieldId) : null;
        return view('booking.create', ['field' => $field]);
    }

    public function detail(Request $request)
    {
        return view('booking.detail');
    }

    public function fieldSchedule(Request $request)
    {
        return view('booking.field-schedule');
    }

    public function myBookings(Request $request)
    {
        return view('booking.my-bookings');
    }
}
