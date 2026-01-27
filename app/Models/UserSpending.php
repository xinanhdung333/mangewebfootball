<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSpending extends Model
{
    use HasFactory;

    protected $table = 'user_spending';

    protected $fillable = [
        'user_id',
        'total_booking',
        'total_services',
        'total_spent',
        'last_update',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship to user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
