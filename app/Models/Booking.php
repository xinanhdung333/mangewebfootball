<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';

    protected $fillable = [
        'user_id',
        'field_id',
        'booking_date',
        'start_time',
        'end_time',
        'total_price',
        'status',
        'notes'
    ];

    protected $casts = [
        'booking_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
public function payment()
{
    return $this->hasOne(BookingPayment::class);
}
    public function field()
    {
        return $this->belongsTo(Field::class);
    }

 public function services()
{
    return $this->belongsToMany(Service::class, 'booking_services')
        ->withPivot('quantity');
}
    public function feedback()
    {
        return $this->hasOne(Feedback::class);
    }

    /**
     * Check availability for a field within a time window
     */
    public static function isFieldAvailable(int $fieldId, string $date, string $startTime, string $endTime, ?int $excludeBookingId = null): bool
    {
        $query = self::where('field_id', $fieldId)
            ->whereDate('booking_date', $date)
            ->whereNotIn('status', ['cancelled', 'expired'])
            ->where(function($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->count() === 0;
    }

    /**
     * Calculate price based on hours and price per hour
     */
    public static function calculatePrice(float $pricePerHour, string $startTime, string $endTime): float
    {
        $start = strtotime($startTime);
        $end = strtotime($endTime);
        $hours = ($end - $start) / 3600;
        return $hours * $pricePerHour;
    }

    /**
     * Auto-update booking status
     */
    public static function autoUpdateBookingStatus(): array
    {
        $now = now();
        $expireBefore = $now->copy()->subMinutes(config('booking.pending_expire_minutes', 15));

        $expiredIds = self::query()
            ->where('status', 'pending')
            ->where(function ($query) use ($now, $expireBefore) {
                $query->where('created_at', '<=', $expireBefore)
                    ->orWhereRaw("CONCAT(booking_date, ' ', start_time) <= ?", [$now]);
            })
            ->whereDoesntHave('payment', function ($query) {
                $query->where('status', 'success');
            })
            ->pluck('id');

        if ($expiredIds->isNotEmpty()) {
            DB::transaction(function () use ($expiredIds) {
                self::whereIn('id', $expiredIds)->update(['status' => 'expired']);

                BookingPayment::whereIn('booking_id', $expiredIds)
                    ->where('status', 'pending')
                    ->update(['status' => 'failed']);
            });
        }
        
        // confirmed -> in_progress
        $inProgress = self::where('status', 'confirmed')
            ->whereRaw("CONCAT(booking_date, ' ', start_time) <= ?", [$now])
            ->whereRaw("CONCAT(booking_date, ' ', end_time) >= ?", [$now])
            ->update(['status' => 'in_progress']);

        // in_progress -> completed
        $completed = self::where('status', 'in_progress')
            ->whereRaw("CONCAT(booking_date, ' ', end_time) < ?", [$now])
            ->update(['status' => 'completed']);

        return [
            'expired' => $expiredIds->count(),
            'in_progress' => $inProgress,
            'completed' => $completed,
        ];
    }
}
