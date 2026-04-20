<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $conversation = Conversation::firstOrCreate(
            ['user_id' => $user->id],
            ['target_type' => null, 'target_id' => null, 'admin_id' => null]
        );
        $conversation->load('admin');

        $messages = $conversation->messages()->with('sender')->orderBy('created_at')->get();

        return view('user.chat', compact('conversation', 'messages'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $user = auth()->user();

        $conversation = Conversation::firstOrCreate(
            ['user_id' => $user->id],
            ['target_type' => null, 'target_id' => null, 'admin_id' => null]
        );

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'message' => $request->message,
            'is_read' => false,
        ]);

        try {
            Http::timeout(1)->post(env('WS_SERVER_URL', 'http://127.0.0.1:6001').'/broadcast', [
                'conversation_id' => $conversation->id,
                'message' => [
                    'id' => $message->id,
                    'conversation_id' => $conversation->id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $user->name,
                    'sender_avatar' => $user->avt ? asset('uploads/avatars/'.$user->avt) : asset('assets/images/default.png'),
                    'message' => $message->message,
                    'created_at' => $message->created_at->format('H:i d/m/Y'),
                ],
            ]);
        } catch (\Throwable $e) {
            // ignore websocket broadcast failure
        }

        $conversation->touch();

        $payload = [
            'id' => $message->id,
            'conversation_id' => $conversation->id,
            'sender_id' => $message->sender_id,
            'sender_name' => $user->name,
            'sender_avatar' => $user->avt ? asset('uploads/avatars/'.$user->avt) : asset('assets/images/default.png'),
            'message' => $message->message,
            'created_at' => $message->created_at->format('H:i d/m/Y'),
            'admin_id' => $conversation->admin_id,
        ];

        if ($request->ajax()) {
            return response()->json(['status' => 'ok', 'message' => $payload]);
        }

        return redirect()->route('user.chat.index');
    }

    public function adminIndex()
    {
        $conversations = Conversation::with(['user', 'admin'])->orderBy('updated_at', 'desc')->get();

        return view('admin.chat.index', compact('conversations'));
    }

    public function adminShow(Conversation $conversation)
    {
        if ($conversation->admin_id && $conversation->admin_id !== auth()->id()) {
            abort(403, 'Conversation đã được admin khác xử lý.');
        }

        $conversation->load(['user', 'messages.sender', 'admin']);

        return view('admin.chat.show', compact('conversation'));
    }

    public function adminReply(Request $request, Conversation $conversation)
    {
        if (!$conversation->admin_id) {
            $conversation->admin_id = auth()->id();
            $conversation->save();
        }

        if ($conversation->admin_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Conversation đã được admin khác xử lý.'
            ], 403);
        }

        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => auth()->id(),
            'message' => $request->message,
            'is_read' => false,
        ]);

        $payload = [
            'id' => $message->id,
            'conversation_id' => $conversation->id,
            'sender_id' => $message->sender_id,
            'sender_name' => auth()->user()->name,
            'sender_avatar' => auth()->user()->avt ? asset('uploads/avatars/'.auth()->user()->avt) : asset('assets/images/default.png'),
            'message' => $message->message,
            'created_at' => $message->created_at->format('H:i d/m/Y'),
        ];

        try {
            Http::timeout(1)->post(env('WS_SERVER_URL', 'http://127.0.0.1:6001').'/broadcast', [
                'conversation_id' => $conversation->id,
                'message' => $payload,
            ]);
        } catch (\Throwable $e) {
            // ignore websocket broadcast failure
        }

        $conversation->touch();

        if ($request->ajax()) {
            return response()->json(['status' => 'ok', 'message' => $payload]);
        }

        return back()->with('success', 'Đã gửi tin nhắn cho khách hàng.');
    }
}
