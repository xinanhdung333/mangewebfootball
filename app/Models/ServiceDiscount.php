<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ServiceDiscount extends Model
{
    protected $fillable = [
    'service_id',
    'start_time',
    'end_time',
    'multiplier',
        'note', // 🔥 thêm dòng này

    'is_active'
];

protected $casts = [
    'is_active' => 'boolean',
];
public function service()
{
    return $this->belongsTo(\App\Models\Service::class, 'service_id');
}

}
