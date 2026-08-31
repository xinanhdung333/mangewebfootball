<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderShipment extends Model
{
    public const STATUS_CREATED = 'created';
    public const STATUS_PICKED_UP = 'picked_up';
    public const STATUS_TRANSPORTING = 'transporting';
    public const STATUS_DELIVERING = 'delivering';
    public const STATUS_DELIVERED = 'delivered';

    public const STATUSES = [
        self::STATUS_CREATED,
        self::STATUS_PICKED_UP,
        self::STATUS_TRANSPORTING,
        self::STATUS_DELIVERING,
        self::STATUS_DELIVERED,
    ];

    protected $fillable = [
        'order_id',
        'provider',
        'tracking_code',
        'client_order_code',
        'status',
        'pickup_lat',
        'pickup_lng',
        'delivery_lat',
        'delivery_lng',
        'shipper_lat',
        'shipper_lng',
        'route_points',
        'provider_response',
        'provider_error',
        'last_status_at',
    ];

    protected $casts = [
        'pickup_lat' => 'float',
        'pickup_lng' => 'float',
        'delivery_lat' => 'float',
        'delivery_lng' => 'float',
        'shipper_lat' => 'float',
        'shipper_lng' => 'float',
        'route_points' => 'array',
        'provider_response' => 'array',
        'last_status_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function statusLabel(): string
    {
        return self::labels()[$this->status] ?? $this->status;
    }

    public static function labels(): array
    {
        return [
            self::STATUS_CREATED      => 'Đã tạo đơn',
            self::STATUS_PICKED_UP    => 'Đã lấy hàng',
            self::STATUS_TRANSPORTING => 'Đang vận chuyển',
            self::STATUS_DELIVERING   => 'Đang giao hàng',
            self::STATUS_DELIVERED    => 'Đã giao thành công',
        ];
    }
}
