<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'feedbacks';

    protected $fillable = [
        'user_id',
        'booking_id',
        'service_id',
        'message',
        'rating',
        'admin_reply',
        'replied_by',
        'replied_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'replied_at' => 'datetime',
    ];

    /**
     * Relationship to user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship to booking
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Relationship to service
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function replier()
    {
        return $this->belongsTo(User::class, 'replied_by');
    }
}
