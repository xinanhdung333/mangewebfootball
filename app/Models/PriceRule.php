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
    ];

    public function field()
    {
        return $this->belongsTo(Field::class);
    }
}