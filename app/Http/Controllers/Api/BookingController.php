<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckAvailabilityRequest;
use App\Http\Requests\Api\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\Field;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class BookingController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $bookings = $request->user()->bookings()
            ->with(['field', 'services', 'payment'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return BookingResource::collection($bookings);
    }

    public function show(Request $request, Booking $booking): BookingResource
    {
        abort_unless((int) $booking->user_id === (int) $request->user()->id, 403);

        return new BookingResource($booking->load(['field', 'services', 'payment']));
    }

    public function store(StoreBookingRequest $request): Response
    {
        $data = $request->validated();

        if (! Booking::isFieldAvailable(
            (int) $data['field_id'],
            $data['booking_date'],
            $data['start_time'],
            $data['end_time']
        )) {
            return response()->json(['message' => 'Khung gio da co nguoi dat'], 422);
        }

        try {
            $booking = DB::transaction(function () use ($data, $request) {
                $field = Field::query()->whereKey($data['field_id'])->lockForUpdate()->firstOrFail();
                $totalPrice = Booking::calculatePrice((float) $field->price_per_hour, $data['start_time'], $data['end_time']);

                $booking = Booking::create([
                    'user_id' => $request->user()->id,
                    'field_id' => $field->id,
                    'booking_date' => $data['booking_date'],
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'total_price' => max(0, $totalPrice),
                    'status' => 'pending',
                    'note' => $data['note'] ?? null,
                ]);

                $servicesTotal = 0;
                foreach ($data['services'] ?? [] as $serviceId => $quantity) {
                    $quantity = (int) $quantity;
                    if ($quantity <= 0) {
                        continue;
                    }

                    $service = Service::query()->whereKey($serviceId)->lockForUpdate()->firstOrFail();
                    if ($service->quantity < $quantity) {
                        throw new \RuntimeException('Dich vu da het hang hoac khong du so luong');
                    }

                    $booking->services()->attach($service->id, ['quantity' => $quantity]);
                    $service->decrement('quantity', $quantity);
                    $servicesTotal += ((float) $service->price) * $quantity;
                }

                BookingPayment::create([
                    'booking_id' => $booking->id,
                    'amount' => $booking->total_price + $servicesTotal,
                    'status' => 'pending',
                ]);

                return $booking;
            });
        } catch (\RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        Log::channel('api')->info('Booking created via API', [
            'booking_id' => $booking->id,
            'user_id' => $request->user()->id,
            'amount' => $booking->payment?->amount,
        ]);

        return (new BookingResource($booking->load(['field', 'services', 'payment'])))
            ->response()
            ->setStatusCode(201);
    }

    public function update(StoreBookingRequest $request, Booking $booking): BookingResource
    {
        abort_unless((int) $booking->user_id === (int) $request->user()->id, 403);
        abort_if(in_array($booking->status, ['confirmed', 'in_progress', 'completed'], true), 422, 'Booking cannot be changed');

        $data = $request->validated();

        if (! Booking::isFieldAvailable(
            (int) $data['field_id'],
            $data['booking_date'],
            $data['start_time'],
            $data['end_time'],
            $booking->id
        )) {
            abort(422, 'Khung gio da co nguoi dat');
        }

        $field = Field::findOrFail($data['field_id']);
        $booking->update([
            'field_id' => $field->id,
            'booking_date' => $data['booking_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'total_price' => Booking::calculatePrice((float) $field->price_per_hour, $data['start_time'], $data['end_time']),
            'note' => $data['note'] ?? null,
        ]);

        return new BookingResource($booking->load(['field', 'services', 'payment']));
    }

    public function destroy(Request $request, Booking $booking): JsonResponse
    {
        abort_unless((int) $booking->user_id === (int) $request->user()->id, 403);

        $booking->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Booking cancelled']);
    }

    public function checkAvailable(CheckAvailabilityRequest $request): JsonResponse
    {
        $data = $request->validated();

        return response()->json([
            'available' => Booking::isFieldAvailable(
                (int) $data['field_id'],
                $data['booking_date'],
                $data['start_time'],
                $data['end_time'],
                $data['exclude_booking_id'] ?? null
            ),
        ]);
    }
}
