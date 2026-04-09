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
        ->where('status', 'active')
        ->select(
            'id',
            'name',
            'description',
            'image',
            'location',
            'price_per_hour',
            'avg_rating',
            'total_reviews',
            'status',
            'created_at',
            'updated_at'
        );
}
}
