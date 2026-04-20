@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1><i class="bi bi-chat-dots"></i> Chat với Admin</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white border-bottom py-3">
                    <div id="user-chat-admin-info" class="d-flex align-items-center gap-3">
                        @if($conversation->admin)
                            <img id="assigned-admin-avatar" src="{{ $conversation->admin->avt ? asset('uploads/avatars/'.$conversation->admin->avt) : asset('assets/images/default.png') }}" alt="Avatar" class="rounded-circle" style="width:48px;height:48px;object-fit:cover;">
                            <div>
                                <h5 id="assigned-admin-name" class="mb-0">Chat với {{ $conversation->admin->name }}</h5>
                                <small id="assigned-admin-status" class="text-muted">Đã được gán cho admin này</small>
                            </div>
                        @else
                            <div id="assigned-admin-placeholder" class="text-muted">Chờ admin bất kỳ trả lời. Sau khi admin trả lời, cuộc trò chuyện sẽ chính thức thuộc về admin đó.</div>
                        @endif
                    </div>
                </div>
                <div id="user-chat-body" class="card-body" style="min-height: 400px; max-height: 600px; overflow-y: auto;">
                    @if($messages->isEmpty())
                        <div class="text-muted">Chưa có tin nhắn nào. Hãy gửi tin nhắn để bắt đầu.</div>
                    @endif
                    @foreach($messages as $message)
                        @php
                            $senderAvatar = $message->sender && $message->sender->avt
                                ? asset('uploads/avatars/'.$message->sender->avt)
                                : asset('assets/images/default.png');
                        @endphp
                        @if($message->sender_id === auth()->id())
                            <div class="mb-3">
                                <div class="d-flex justify-content-end align-items-end">
                                    <div class="bg-primary text-white p-3 rounded" style="max-width: 80%;">
                                        {{ $message->message }}
                                        <div class="text-end text-xs text-light mt-1">{{ $message->created_at->format('H:i d/m/Y') }}</div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="mb-3">
                                <div class="d-flex justify-content-start align-items-start">
                                    <img src="{{ $senderAvatar }}" alt="Avatar" class="rounded-circle me-2" style="width:40px;height:40px;object-fit:cover;">
                                    <div class="bg-light p-3 rounded" style="max-width: 80%;">
                                        <strong>Admin:</strong> {{ $message->message }}
                                        <div class="text-end text-xs text-muted mt-1">{{ $message->created_at->format('H:i d/m/Y') }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="card-footer">
                    <form id="chat-form" action="{{ route('user.chat.send') }}" method="POST">
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
    const userConversationId = {{ $conversation->id }};
    const currentUserId = {{ auth()->id() }};
    const userChatBody = document.getElementById('user-chat-body');
    const chatForm = document.getElementById('chat-form');
    const messageInput = chatForm.querySelector('[name="message"]');
    const csrfToken = chatForm.querySelector('[name="_token"]').value;
    const wsEndpoint = '{{ env('WS_ENDPOINT', 'ws://127.0.0.1:6001') }}';
    const currentUserAvatar = '{{ auth()->user()->avt ? asset('uploads/avatars/'.auth()->user()->avt) : asset('assets/images/default.png') }}';
    const defaultAvatar = '{{ asset('assets/images/default.png') }}';
    const pendingMessages = [];
    
    console.log('🔌 Connecting to WebSocket:', wsEndpoint);
    const userWs = new WebSocket(wsEndpoint);

    function appendMessage(message, isPending = false) {
        console.log('🧩 appendMessage', message, 'pending=', isPending);

        if (message.id && !message.id.toString().startsWith('temp')) {
            const existing = userChatBody.querySelector(`[data-message-id="${message.id}"]`);
            if (existing) {
                console.log('⚠️ Duplicate message skipped', message.id);
                return;
            }
        }

        const wrapper = document.createElement('div');
        const isCurrentUser = message.sender_id === currentUserId;
        const avatarUrl = message.sender_avatar || (isCurrentUser ? currentUserAvatar : defaultAvatar);
        wrapper.className = 'mb-3';
        if (isPending) {
            wrapper.dataset.tempId = message.id;
            wrapper.classList.add('pending-message');
        } else if (message.id) {
            wrapper.dataset.messageId = message.id;
        }
        wrapper.innerHTML = `
            <div class="d-flex ${isCurrentUser ? 'justify-content-end align-items-end' : 'justify-content-start align-items-start'}">
                ${isCurrentUser ? '' : `<img src="${avatarUrl}" class="rounded-circle me-2" style="width:40px;height:40px;object-fit:cover;" alt="Avatar">`}
                <div class="${isCurrentUser ? 'bg-primary text-white' : 'bg-light'} p-3 rounded" style="max-width: 80%;">
                    ${isCurrentUser ? '' : '<strong>Admin:</strong> '}${message.message}
                    <div class="text-end text-xs ${isCurrentUser ? 'text-light' : 'text-muted'} mt-1 timestamp">${message.created_at}</div>
                </div>
                ${isCurrentUser ? `<img src="${avatarUrl}" class="rounded-circle ms-2" style="width:40px;height:40px;object-fit:cover;" alt="Avatar">` : ''}
            </div>
        `;
        userChatBody.appendChild(wrapper);
        userChatBody.scrollTop = userChatBody.scrollHeight;
    }

    function updateAssignedAdminInfo(adminName, adminAvatar) {
        const adminInfo = document.getElementById('user-chat-admin-info');
        adminInfo.innerHTML = `
            <img id="assigned-admin-avatar" src="${adminAvatar || defaultAvatar}" alt="Avatar" class="rounded-circle me-3" style="width:48px;height:48px;object-fit:cover;">
            <div>
                <h5 id="assigned-admin-name" class="mb-0">Chat với ${adminName || 'Admin'}</h5>
                <small id="assigned-admin-status" class="text-muted">Đã được gán cho admin này</small>
            </div>
        `;
    }

    function resolvePendingMessage(message) {
        const pendingIndex = pendingMessages.findIndex(p => p.text === message.message && p.sender_id === currentUserId);
        if (pendingIndex === -1) {
            return false;
        }

        const pending = pendingMessages[pendingIndex];
        const existing = userChatBody.querySelector(`[data-temp-id="${pending.tempId}"]`);
        if (existing) {
            existing.dataset.messageId = message.id;
            existing.removeAttribute('data-temp-id');
            existing.classList.remove('pending-message');
            const timestamp = existing.querySelector('.timestamp');
            if (timestamp) {
                timestamp.textContent = message.created_at;
            }
            pendingMessages.splice(pendingIndex, 1);
            console.log('✅ Resolved pending message to actual id', message.id);
            return true;
        }

        pendingMessages.splice(pendingIndex, 1);
        return false;
    }

    chatForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const messageText = messageInput.value.trim();
        if (!messageText) {
            return;
        }

        const tempId = 'temp-' + Date.now();
        const now = new Date();
        const localCreatedAt = now.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' }) + ' ' + now.toLocaleDateString('en-GB');
        const pendingMessage = {
            id: tempId,
            conversation_id: userConversationId,
            sender_id: currentUserId,
            sender_name: '',
            message: messageText,
            created_at: localCreatedAt,
        };
        pendingMessages.push({ tempId, text: messageText, sender_id: currentUserId });
        appendMessage(pendingMessage, true);

        const payload = {
            message: messageText,
        };

        console.log('📤 Sending chat message', payload);

        try {
            const response = await fetch(chatForm.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(payload),
            });

            console.log('📶 Send response status', response.status, response.statusText);

            if (!response.ok) {
                const errorData = await response.json().catch(() => null);
                console.error('Send failed', errorData);
                return;
            }

            const data = await response.json().catch((err) => {
                console.error('Response parse failed', err);
                return null;
            });
            console.log('📥 Send response body', data);

            if (!(data && data.status === 'ok')) {
                console.error('Send returned invalid response', data);
            }

            messageInput.value = '';
            messageInput.focus();
        } catch (error) {
            console.error('Send error', error);
        }
    });

    userWs.addEventListener('open', () => {
        console.log('✅ WebSocket connected');
        userWs.send(JSON.stringify({ type: 'subscribe', conversation_id: userConversationId }));
        console.log('📢 Subscribed to conversation:', userConversationId);
    });

    userWs.addEventListener('message', (event) => {
        try {
            const data = JSON.parse(event.data);
            console.log('📨 Received message:', data);
            
            if (data.type !== 'message' || data.conversation_id !== userConversationId) {
                return;
            }

            if (data.message.sender_id === currentUserId && resolvePendingMessage(data.message)) {
                return;
            }

            if (data.message.sender_id !== currentUserId) {
                updateAssignedAdminInfo(data.message.sender_name, data.message.sender_avatar);
            }

            appendMessage(data.message);
        } catch (error) {
            console.error('❌ Invalid websocket message:', error);
        }
    });

    userWs.addEventListener('error', (error) => {
        console.error('❌ WebSocket error:', error);
        console.error('Details:', {
            readyState: userWs.readyState,
            url: userWs.url,
            protocol: userWs.protocol
        });
    });

    userWs.addEventListener('close', () => {
        console.warn('⚠️ WebSocket connection closed');
    });

    // Khi page load lại, tự vào phần chat và cuộn xuống tin nhắn cuối cùng
    setTimeout(() => {
        if (userChatBody) {
            userChatBody.scrollTop = userChatBody.scrollHeight;
        }
        if (messageInput) {
            messageInput.focus();
        }
        const chatCard = document.querySelector('#user-chat-body');
        if (chatCard) {
            window.scrollTo({ top: chatCard.getBoundingClientRect().top + window.scrollY - 20, behavior: 'smooth' });
        }
    }, 100);

</script>
@endpush
@endsection
