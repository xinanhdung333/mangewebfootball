<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotKeyword extends Model
{
    protected $table = 'chatbot_keywords';

    protected $fillable = [
        'intent_id',
        'keyword'
    ];

    public function intent()
    {
        return $this->belongsTo(ChatbotIntent::class, 'intent_id');
    }
}