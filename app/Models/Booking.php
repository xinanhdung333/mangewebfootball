<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';

    protected $fillable = [
        'user_id','field_id','start_time','end_time','status','total'
    ];

    /**
     * Check availability for a field within a time window (excludes a booking id if provided)
     * This replicates the original isFieldAvailable logic using Eloquent.
     */
    public static function isFieldAvailable(int $fieldId, string $date, string $startTime, string $endTime, ?int $excludeBookingId = null): bool
    {
        $query = self::where('field_id', $fieldId)
            ->whereDate('start_time', $date)
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

    public static function calculatePrice(float $pricePerHour, string $startTime, string $endTime): float
    {
        $start = strtotime($startTime);
        $end = strtotime($endTime);
        $hours = ($end - $start) / 3600;
        return $hours * $pricePerHour;
    }

    public function field()
    {
        return $this->belongsTo(\App\Models\Field::class, 'field_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Auto-update booking status (similar to commented function in original)
     */
    public static function autoUpdateBookingStatus()
    {
        // confirmed -> in_progress
        self::where('status', 'confirmed')
            ->whereRaw("start_time <= NOW() AND end_time >= NOW()")
            ->update(['status' => 'in_progress']);

        // in_progress -> completed
        self::where('status', 'in_progress')
            ->whereRaw("end_time < NOW()")
            ->update(['status' => 'completed']);
    }
}
