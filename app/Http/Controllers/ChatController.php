<?php
use App\Models\Message;
use Illuminate\Http\Request;
use App\Events\MessageSent;

class ChatController extends Controller
{

public function sendMessage(Request $request)
{
    $message = Message::create([
        'conversation_id' => $request->conversation_id,
        'sender_id' => 1,
        'message' => $request->message
    ]);

    broadcast(new MessageSent($message))->toOthers();
    return response()->json($request->all());

    return response()->json($message);

}
}