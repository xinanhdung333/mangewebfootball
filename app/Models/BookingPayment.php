<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingPayment extends Model
{
    protected $fillable = [
        'booking_id',
        'momo_order_id',
        'momo_trans_id',
        'amount',
        'status',
        'paid_at',
        'payment_method'
    ];
}