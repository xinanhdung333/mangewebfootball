<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    use HasFactory;

    protected $table = 'fields';

    protected $fillable = [
        'name', 'description', 'image', 'location', 'price_per_hour'
    ];

    /**
     * Scope to include avg_rating and total_reviews using left joins on bookings/feedback
     */
    public function scopeWithRatings($query)
    {
        return $query
            ->leftJoin('bookings as b', 'b.field_id', '=', 'fields.id')
            ->leftJoin('feedback as fe', 'fe.booking_id', '=', 'b.id')
            ->where('fields.status', 'active')
            ->groupBy('fields.id', 'fields.name', 'fields.description', 'fields.image', 'fields.location', 'fields.price_per_hour', 'fields.status', 'fields.created_at', 'fields.updated_at')
            ->select('fields.*')
            ->selectRaw('COALESCE(AVG(fe.rating), 0) as avg_rating')
            ->selectRaw('COALESCE(COUNT(fe.id), 0) as total_reviews')
            ->orderBy('fields.name', 'asc');
    }
}
