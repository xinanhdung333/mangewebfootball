<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotIntent extends Model
{
    protected $table = 'chatbot_intents';

    protected $fillable = [
        'name',
        'priority',
        'is_active'
    ];

    public function keywords()
    {
        return $this->hasMany(ChatbotKeyword::class, 'intent_id');
    }

    public function responses()
    {
        return $this->hasMany(ChatbotResponse::class, 'intent_id');
    }
}