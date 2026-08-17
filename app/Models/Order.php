<?php

namespace App\Models;
use App\Models\UserAddress;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'user_id',
        'cart_id',
        'user_address_id',
        'total_amount',
        'status',
        'meta'
    ];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Alias relation to keep compatibility with existing code using `userAddress`
     */
    

   public function items()
{
    return $this->hasMany(OrderItem::class);
}

    public function services()
    {
        return $this->belongsToMany(Service::class, 'order_items')
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }
    public function userAddress()
    {
        return $this->belongsTo(
            UserAddress::class,
            'user_address_id'
        );
    }

    public function shipment()
    {
        return $this->hasOne(OrderShipment::class);
    }
}
