<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'momo_order_id',
        'momo_trans_id',
        'amount',
        'status',
        'paid_at',
        'payment_method'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
