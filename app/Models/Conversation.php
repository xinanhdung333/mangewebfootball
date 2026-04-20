<?php

namespace App\Models;

use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'target_type',
        'target_id',
        'admin_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
public function admin()
{
    return $this->belongsTo(User::class, 'admin_id');
}
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function target()
    {
        return $this->morphTo();
    }
}
