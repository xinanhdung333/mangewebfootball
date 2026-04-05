<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotLog extends Model
{
    protected $table = 'chatbot_logs';

    protected $fillable = [
        'message',
        'matched_intent'
    ];

    public $timestamps = false;
}