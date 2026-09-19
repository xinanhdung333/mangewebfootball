<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderShippingStatusHistory extends Model
{
    protected $fillable = ['order_id', 'shipping_status_id', 'updated_by'];

    public function status()
    {
        return $this->belongsTo(ShippingStatus::class, 'shipping_status_id');
    }
}
