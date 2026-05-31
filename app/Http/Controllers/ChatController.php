<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    private const ATTACHMENT_MAX_KB = 10240;

    private function validateMessageRequest(Request $request): array
    {
        return $request->validate([
            'message' => 'nullable|string|max:2000|required_without:attachment',
            'attachment' => 'nullable|file|max:' . self::ATTACHMENT_MAX_KB,
            'client_temp_id' => 'nullable|string|max:80',
        ]);
    }

    private function attachmentData(Request $request): array
    {
        if (!$request->hasFile('attachment')) {
            return [];
        }

        $file = $request->file('attachment');
        $path = $file->store('chat-attachments', 'public');

        return [
            'attachment_path' => $path,
            'attachment_original_name' => $file->getClientOriginalName(),
            'attachment_mime' => $file->getMimeType(),
            'attachment_size' => $file->getSize(),
        ];
    }

    private function messagePayload(Message $message, ?string $clientTempId = null): array
    {
        $message->loadMissing('sender');
        $sender = $message->sender;

        return [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender_id' => $message->sender_id,
            'sender_name' => $sender?->name,
            'sender_avatar' => $sender && $sender->avt ? asset('uploads/avatars/'.$sender->avt) : asset('assets/images/default.png'),
            'message' => $message->message,
            'attachment_url' => $message->attachmentUrl(),
            'attachment_name' => $message->attachment_original_name,
            'attachment_mime' => $message->attachment_mime,
            'attachment_size' => $message->attachment_size,
            'attachment_is_image' => $message->attachmentIsImage(),
            'created_at' => $message->created_at->format('H:i d/m/Y'),
            'client_temp_id' => $clientTempId,
        ];
    }

    private function broadcastMessage(Conversation $conversation, array $payload): void
    {
        try {
            Http::timeout(1)->post(env('WS_SERVER_URL', 'http://127.0.0.1:6001').'/broadcast', [
                'conversation_id' => $conversation->id,
                'message' => $payload,
            ]);
        } catch (\Throwable $e) {
            // ignore websocket broadcast failure
        }
    }

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
        $data = $this->validateMessageRequest($request);

        $user = auth()->user();

        $conversation = Conversation::firstOrCreate(
            ['user_id' => $user->id],
            ['target_type' => null, 'target_id' => null, 'admin_id' => null]
        );

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'message' => $data['message'] ?? '',
            'is_read' => false,
        ] + $this->attachmentData($request));

        $conversation->touch();

        $payload = $this->messagePayload($message, $data['client_temp_id'] ?? null);
        $payload['admin_id'] = $conversation->admin_id;
        $this->broadcastMessage($conversation, $payload);

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

        $data = $this->validateMessageRequest($request);

        $message = $conversation->messages()->create([
            'sender_id' => auth()->id(),
            'message' => $data['message'] ?? '',
            'is_read' => false,
        ] + $this->attachmentData($request));

        $payload = $this->messagePayload($message, $data['client_temp_id'] ?? null);
        $this->broadcastMessage($conversation, $payload);

        $conversation->touch();

        if ($request->ajax()) {
            return response()->json(['status' => 'ok', 'message' => $payload]);
        }

        return back()->with('success', 'Đã gửi tin nhắn cho khách hàng.');
    }
}
