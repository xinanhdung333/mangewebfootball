<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
    'booking_id',
    'order_id',
    'invoice_code',
    'total_amount',
    'issued_at'
];
    public function booking()
{
    return $this->belongsTo(\App\Models\Booking::class);
}
public function order()
{
    return $this->belongsTo(Order::class);
}
}
