@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12 d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="bi bi-chat-dots"></i> Chat với {{ $conversation->user->name ?? 'Người dùng' }}</h1>
                <p class="text-muted">Conversation ID: {{ $conversation->id }}</p>
            </div>
            <a href="{{ route('admin.chat.index') }}" class="btn btn-secondary">Quay lại danh sách</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div id="admin-chat-body" class="card-body" style="min-height: 400px; max-height: 600px; overflow-y: auto;">
                    @if($conversation->messages->isEmpty())
                        <div class="text-muted">Chưa có tin nhắn nào.</div>
                    @endif

                    @foreach($conversation->messages as $message)
                        @if($message->sender_id === auth()->id())
                            <div class="d-flex justify-content-end mb-3">
                                <div class="bg-primary text-white p-3 rounded" style="max-width: 80%;">
                                    {{ $message->message }}
                                    <div class="text-end text-xs text-light mt-1">{{ $message->created_at->format('H:i d/m/Y') }}</div>
                                </div>
                            </div>
                        @else
                            <div class="d-flex justify-content-start mb-3" style="max-width: 80%;">
                                <img src="{{ $message->sender->avt ? asset('uploads/avatars/'.$message->sender->avt) : asset('assets/images/default.png') }}" class="rounded-circle me-2" style="width:40px;height:40px;object-fit:cover;" alt="Avatar">
                                <div class="bg-light p-3 rounded" style="width:100%;">
                                    <strong>{{ $message->sender->name ?? 'Người dùng' }}:</strong> {{ $message->message }}
                                    <div class="text-end text-xs text-muted mt-1">{{ $message->created_at->format('H:i d/m/Y') }}</div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="card-footer">
                    <form id="admin-chat-form" action="{{ route('admin.chat.reply', $conversation) }}" method="POST">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="message" class="form-control" placeholder="Nhập tin nhắn..." required>
                            <button class="btn btn-primary" type="submit">Gửi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const adminConversationId = {{ $conversation->id }};
    const adminUserId = {{ auth()->id() }};
    const adminChatBody = document.getElementById('admin-chat-body');
    const adminChatForm = document.getElementById('admin-chat-form');
    const adminMessageInput = adminChatForm.querySelector('[name="message"]');
    const adminCsrfToken = adminChatForm.querySelector('[name="_token"]').value;
    const adminWs = new WebSocket('{{ env('WS_ENDPOINT', 'ws://127.0.0.1:6001') }}');
    const adminPendingMessages = [];
    const adminDefaultAvatar = '{{ asset('assets/images/default.png') }}';

    function adminAppendMessage(message, isPending = false) {
        if (message.id && !message.id.toString().startsWith('temp')) {
            const existing = adminChatBody.querySelector(`[data-message-id="${message.id}"]`);
            if (existing) {
                return;
            }
        }

        const wrapper = document.createElement('div');
        const isAdmin = message.sender_id === adminUserId;
        wrapper.className = isAdmin ? 'd-flex justify-content-end mb-3' : 'd-flex justify-content-start mb-3';
        if (isPending) {
            wrapper.dataset.tempId = message.id;
            wrapper.classList.add('pending-message');
        } else if (message.id) {
            wrapper.dataset.messageId = message.id;
        }

        const avatarUrl = message.sender_avatar || adminDefaultAvatar;
        wrapper.innerHTML = `
            <div class="d-flex ${isAdmin ? 'justify-content-end' : ''} align-items-start" style="max-width: 80%;">
                ${isAdmin ? '' : `<img src="${avatarUrl}" class="rounded-circle me-2" style="width:40px;height:40px;object-fit:cover;" alt="Avatar">`}
                <div class="${isAdmin ? 'bg-primary text-white' : 'bg-light'} p-3 rounded" style="width:100%;">
                    ${isAdmin ? '' : `<strong>${message.sender_name || 'Người dùng'}:</strong> `}${message.message}
                    <div class="text-end text-xs ${isAdmin ? 'text-light' : 'text-muted'} mt-1 timestamp">${message.created_at}</div>
                </div>
            </div>
        `;
        adminChatBody.appendChild(wrapper);
        adminChatBody.scrollTop = adminChatBody.scrollHeight;
    }

    function adminResolvePendingMessage(message) {
        const pendingIndex = adminPendingMessages.findIndex(p => p.text === message.message && p.sender_id === adminUserId);
        if (pendingIndex === -1) {
            return false;
        }

        const pending = adminPendingMessages[pendingIndex];
        const existing = adminChatBody.querySelector(`[data-temp-id="${pending.tempId}"]`);
        if (existing) {
            existing.dataset.messageId = message.id;
            existing.removeAttribute('data-temp-id');
            existing.classList.remove('pending-message');
            const timestamp = existing.querySelector('.timestamp');
            if (timestamp) {
                timestamp.textContent = message.created_at;
            }
            adminPendingMessages.splice(pendingIndex, 1);
            return true;
        }

        adminPendingMessages.splice(pendingIndex, 1);
        return false;
    }

    adminChatForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const messageText = adminMessageInput.value.trim();
        if (!messageText) {
            return;
        }

        const tempId = 'temp-' + Date.now();
        const now = new Date();
        const localCreatedAt = now.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' }) + ' ' + now.toLocaleDateString('en-GB');
        const pendingMessage = {
            id: tempId,
            conversation_id: adminConversationId,
            sender_id: adminUserId,
            sender_name: @json(auth()->user()->name),
            message: messageText,
            created_at: localCreatedAt,
        };
        adminPendingMessages.push({ tempId, text: messageText, sender_id: adminUserId });
        adminAppendMessage(pendingMessage, true);

        try {
            const response = await fetch(adminChatForm.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': adminCsrfToken,
                },
                body: JSON.stringify({ message: messageText }),
            });

            if (!response.ok) {
                console.error('Admin send failed', response.status);
                return;
            }

            const data = await response.json();
            if (!(data && data.status === 'ok')) {
                console.error('Admin send invalid response', data);
            }
            adminMessageInput.value = '';
            adminMessageInput.focus();
        } catch (error) {
            console.error('Admin send error', error);
        }
    });

    adminWs.addEventListener('open', () => {
        adminWs.send(JSON.stringify({ type: 'subscribe', conversation_id: adminConversationId }));
    });

    adminWs.addEventListener('message', (event) => {
        try {
            const data = JSON.parse(event.data);
            if (data.type !== 'message' || data.conversation_id !== adminConversationId) {
                return;
            }

            if (data.message.sender_id === adminUserId && adminResolvePendingMessage(data.message)) {
                return;
            }

            adminAppendMessage(data.message);
        } catch (error) {
            console.error('Invalid websocket message', error);
        }
    });
    adminChatBody.scrollTop = adminChatBody.scrollHeight;
</script>
@endpush
@endsection
