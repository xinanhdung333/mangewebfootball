<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $table = 'user_addresses';

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'street_address',
        'ward',
        'district',
        'city',
        'postal_code',
        'is_default',
        'lat',
        'lng',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns this address.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Return a formatted full address string.
     */
    public function getAddressAttribute()
    {
        $parts = [];
        if ($this->street_address) $parts[] = $this->street_address;
        if ($this->ward) $parts[] = $this->ward;
        if ($this->district) $parts[] = $this->district;
        if ($this->city) $parts[] = $this->city;
        $addr = implode(', ', $parts);
        if ($this->postal_code) $addr .= ' - ' . $this->postal_code;
        return $addr;
    }
}
