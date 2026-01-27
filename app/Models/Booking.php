<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function field()
    {
        return $this->belongsTo(Field::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'booking_services')
            ->withPivot('quantity')
            ->withTimestamps();
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
            ->where('status', '!=', 'cancelled')
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
    public static function autoUpdateBookingStatus()
    {
        $now = now();
        
        // confirmed -> in_progress
        self::where('status', 'confirmed')
            ->whereRaw("CONCAT(booking_date, ' ', start_time) <= ?", [$now])
            ->whereRaw("CONCAT(booking_date, ' ', end_time) >= ?", [$now])
            ->update(['status' => 'in_progress']);

        // in_progress -> completed
        self::where('status', 'in_progress')
            ->whereRaw("CONCAT(booking_date, ' ', end_time) < ?", [$now])
            ->update(['status' => 'completed']);
    }
}
