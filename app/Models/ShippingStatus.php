<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingStatus extends Model
{
    protected $fillable = ['code', 'label', 'shipment_status', 'sort_order', 'is_terminal'];

    protected $casts = [
        'is_terminal' => 'boolean',
    ];

    public function histories()
    {
        return $this->hasMany(OrderShippingStatusHistory::class, 'shipping_status_id');
    }
}
