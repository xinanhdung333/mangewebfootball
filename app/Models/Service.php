<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $table = 'services';

    protected $fillable = ['name','description','price','image'];

    public function scopeWithRatings($query)
    {
        return $query->where('services.status', 'active')
            ->select('services.*')
            ->selectSub("(SELECT COALESCE(AVG(fe.rating), 0) FROM feedback fe WHERE fe.service_id = services.id)", 'avg_rating')
            ->selectSub("(SELECT COALESCE(COUNT(fe.id), 0) FROM feedback fe WHERE fe.service_id = services.id)", 'total_reviews')
            ->orderBy('services.name', 'asc');
    }
}
