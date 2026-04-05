<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotResponse extends Model
{
    protected $table = 'chatbot_responses';

    protected $fillable = [
        'intent_id',
        'response_text'
    ];

    public function intent()
    {
        return $this->belongsTo(ChatbotIntent::class, 'intent_id');
    }
}