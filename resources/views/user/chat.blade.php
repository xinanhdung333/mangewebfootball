@extends('layouts.app')

@section('content')
@include('chat._messenger-styles')
<div class="messenger-page">
    <div class="messenger-shell">
        <aside class="messenger-sidebar">
            <div class="messenger-sidebar-header">
                <p class="messenger-sidebar-title">Tin nhắn</p>
                <div class="messenger-search"><i class="bi bi-search"></i><span>Tìm kiếm cuộc trò chuyện...</span></div>
            </div>
            <div class="messenger-conversations">
                <a class="messenger-conversation active" href="{{ route('user.chat.index') }}">
                    <span class="messenger-avatar"><i class="bi bi-headset"></i></span>
                    <span class="messenger-conversation-copy">
                        <span class="messenger-conversation-name">{{ $conversation->admin->name ?? 'Hỗ trợ SportsHub' }}</span>
                        <span class="messenger-conversation-preview">Hỗ trợ khách hàng</span>
                    </span>
                    <i class="bi bi-chevron-right small"></i>
                </a>
            </div>
            <div class="messenger-sidebar-note"><strong><i class="bi bi-stars"></i> Gợi ý hỗ trợ</strong><br>Đặt câu hỏi về sân, đơn hàng hoặc dịch vụ. Đội ngũ hỗ trợ sẽ phản hồi sớm.</div>
        </aside>
        <section class="messenger-main">
            <header class="messenger-header">
                <span class="messenger-avatar">
                    @if($conversation->admin && $conversation->admin->avt)
                        <img src="{{ asset('uploads/avatars/'.$conversation->admin->avt) }}" alt="Avatar">
                    @else
                        <i class="bi bi-headset"></i>
                    @endif
                </span>
                <div id="user-chat-admin-info">
                    @if($conversation->admin)
                        <h1 id="assigned-admin-name">Chat với {{ $conversation->admin->name }}</h1>
                        <small id="assigned-admin-status"><i class="bi bi-circle-fill messenger-status"></i> Đang trực tuyến</small>
                    @else
                        <div id="assigned-admin-placeholder"><h1>Hỗ trợ SportsHub</h1><small>Thường phản hồi trong vài phút</small></div>
                    @endif
                </div>
                <div class="messenger-actions"><i class="bi bi-telephone"></i><i class="bi bi-camera-video"></i><i class="bi bi-info-circle"></i></div>
            </header>
            <div id="user-chat-body" class="messenger-body">
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
                                        @if($message->message !== '')
                                            <div>{{ $message->message }}</div>
                                        @endif
                                        @include('chat._message_attachment', ['message' => $message])
                                        <div class="text-end text-xs text-light mt-1">{{ $message->created_at->format('H:i d/m/Y') }}</div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="mb-3">
                                <div class="d-flex justify-content-start align-items-start">
                                    <img src="{{ $senderAvatar }}" alt="Avatar" class="rounded-circle me-2" style="width:40px;height:40px;object-fit:cover;">
                                    <div class="bg-light p-3 rounded" style="max-width: 80%;">
                                        <strong>Admin:</strong>
                                        @if($message->message !== '')
                                            <div>{{ $message->message }}</div>
                                        @endif
                                        @include('chat._message_attachment', ['message' => $message])
                                        <div class="text-end text-xs text-muted mt-1">{{ $message->created_at->format('H:i d/m/Y') }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="messenger-compose">
                    <form id="chat-form" action="{{ route('user.chat.send') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="attachment" id="chat-attachment" class="d-none">
                        <div class="input-group">
                            <button class="btn btn-outline-secondary" type="button" id="chat-file-button" title="Gui anh hoac file">
                                <i class="bi bi-paperclip"></i>
                            </button>
                            <input type="text" name="message" class="form-control" placeholder="Nhập tin nhắn..." required>
                            <button class="btn btn-primary" type="submit">Gửi</button>
                        </div>
                        <div id="chat-file-preview" class="small text-muted mt-2 d-none"></div>
                    </form>
                </div>
        </section>
    </div>
</div>

@push('scripts')
<script>
    const userConversationId = {{ $conversation->id }};
    const currentUserId = {{ auth()->id() }};
    const userChatBody = document.getElementById('user-chat-body');
    const chatForm = document.getElementById('chat-form');
    const messageInput = chatForm.querySelector('[name="message"]');
    const attachmentInput = document.getElementById('chat-attachment');
    const fileButton = document.getElementById('chat-file-button');
    const filePreview = document.getElementById('chat-file-preview');
    const csrfToken = chatForm.querySelector('[name="_token"]').value;
    const wsEndpoint = '{{ env('WS_ENDPOINT', 'ws://127.0.0.1:6001') }}';
    const currentUserAvatar = '{{ auth()->user()->avt ? asset('uploads/avatars/'.auth()->user()->avt) : asset('assets/images/default.png') }}';
    const defaultAvatar = '{{ asset('assets/images/default.png') }}';
    const pendingMessages = [];

    messageInput.required = false;
    
    console.log('🔌 Connecting to WebSocket:', wsEndpoint);
    const userWs = new WebSocket(wsEndpoint);

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        }[char]));
    }

    function renderAttachment(message) {
        if (!message.attachment_url && !message.attachment_name) {
            return '';
        }

        const name = escapeHtml(message.attachment_name || 'File dinh kem');
        if (!message.attachment_url) {
            return `<div class="small mt-2"><i class="bi bi-paperclip"></i> ${name}</div>`;
        }

        const url = encodeURI(message.attachment_url);

        if (message.attachment_is_image) {
            return `<a href="${url}" target="_blank" class="d-block mt-2" title="Xem anh">
                <img src="${url}" alt="${name}" class="img-fluid rounded" style="max-height:220px;object-fit:contain;">
            </a>`;
        }

        return `<a href="${url}" target="_blank" download="${name}" class="btn btn-sm btn-light text-dark border mt-2">
            <i class="bi bi-download"></i> Tai file: ${name}
        </a>`;
    }

    function renderMessageBody(message, prefix = '') {
        return `${prefix}${message.message ? `<div>${escapeHtml(message.message)}</div>` : ''}${renderAttachment(message)}`;
    }

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
                    <div class="message-content">${renderMessageBody(message, isCurrentUser ? '' : '<strong>Admin:</strong> ')}</div>
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
        adminInfo.innerHTML = `<h1 id="assigned-admin-name">Chat với ${escapeHtml(adminName || 'Admin')}</h1>
            <small id="assigned-admin-status"><i class="bi bi-circle-fill messenger-status"></i> Đang trực tuyến</small>`;
    }

    function resolvePendingMessage(message) {
        const pendingIndex = pendingMessages.findIndex(p => p.clientTempId && p.clientTempId === message.client_temp_id);
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
            const content = existing.querySelector('.message-content');
            if (content) {
                content.innerHTML = renderMessageBody(message);
            }
            pendingMessages.splice(pendingIndex, 1);
            console.log('✅ Resolved pending message to actual id', message.id);
            return true;
        }

        pendingMessages.splice(pendingIndex, 1);
        return false;
    }

    fileButton.addEventListener('click', () => attachmentInput.click());

    attachmentInput.addEventListener('change', () => {
        const file = attachmentInput.files[0];
        if (!file) {
            filePreview.classList.add('d-none');
            filePreview.textContent = '';
            return;
        }

        filePreview.classList.remove('d-none');
        filePreview.textContent = `Da chon: ${file.name}`;
    });

    chatForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const messageText = messageInput.value.trim();
        const selectedFile = attachmentInput.files[0] || null;
        if (!messageText && !selectedFile) {
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
            attachment_name: selectedFile ? selectedFile.name : null,
            attachment_url: null,
            attachment_is_image: selectedFile ? selectedFile.type.startsWith('image/') : false,
            created_at: localCreatedAt,
        };
        pendingMessages.push({ tempId, clientTempId: tempId, sender_id: currentUserId });
        appendMessage(pendingMessage, true);

        const payload = new FormData(chatForm);
        payload.set('message', messageText);
        payload.set('client_temp_id', tempId);

        console.log('📤 Sending chat message', payload);

        try {
            const response = await fetch(chatForm.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: payload,
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
            } else {
                resolvePendingMessage(data.message);
            }

            messageInput.value = '';
            attachmentInput.value = '';
            filePreview.classList.add('d-none');
            filePreview.textContent = '';
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

    // Messenger-style: chỉ cuộn bên trong khung tin nhắn, không thay đổi vị trí trang.
    setTimeout(() => {
        if (userChatBody) {
            userChatBody.scrollTop = userChatBody.scrollHeight;
        }
    }, 100);

</script>
@endpush
@endsection
