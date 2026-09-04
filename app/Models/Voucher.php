<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount_type',
        'discount_amount',
        'max_discount_amount',
        'min_order_amount',
        'usage_limit',
        'usage_limit_per_user',
        'first_order_only',
        'is_active',
        'starts_at',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'first_order_only' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
}
