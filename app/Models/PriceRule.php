<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class PriceRule extends Model
{
    protected $fillable = [
        'field_id',
        'start_time',
        'end_time',
        'multiplier',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function field()
    {
        return $this->belongsTo(Field::class);
    }
}
