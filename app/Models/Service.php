<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Service extends Model
{
    use HasFactory;

    protected $table = 'services';

    protected $fillable = [
        'name',
        'description',
        'price',
        'quantity',
        'image',
        'avg_rating',
        'total_reviews',
        'status'
    ];

    protected $casts = [
        'avg_rating' => 'float',
        'total_reviews' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_services')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items')
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('status', 'active')->where('quantity', '>', 0);
    }

    /**
     * Scope to include ratings
     */
    public function scopeWithRatings(Builder $query)
    {
        return $query
            ->leftJoin('feedback as fe', 'fe.service_id', '=', 'services.id')
            ->where('services.status', 'active')
            ->where('services.quantity', '>', 0)
            ->groupBy(
                'services.id',
                'services.name',
                'services.description',
                'services.price',
                'services.quantity',
                'services.image',
                'services.status',
                'services.created_at',
                'services.updated_at'
            )
            ->select('services.*')
            ->selectRaw('COALESCE(AVG(fe.rating), 0) as avg_rating')
            ->selectRaw('COALESCE(COUNT(fe.id), 0) as total_reviews')
            ->orderBy('services.name', 'asc');
    }
}
