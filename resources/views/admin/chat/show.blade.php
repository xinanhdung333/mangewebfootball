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
                                    @if($message->message !== '')
                                        <div>{{ $message->message }}</div>
                                    @endif
                                    @include('chat._message_attachment', ['message' => $message])
                                    <div class="text-end text-xs text-light mt-1">{{ $message->created_at->format('H:i d/m/Y') }}</div>
                                </div>
                            </div>
                        @else
                            <div class="d-flex justify-content-start mb-3" style="max-width: 80%;">
                                <img src="{{ $message->sender->avt ? asset('uploads/avatars/'.$message->sender->avt) : asset('assets/images/default.png') }}" class="rounded-circle me-2" style="width:40px;height:40px;object-fit:cover;" alt="Avatar">
                                <div class="bg-light p-3 rounded" style="width:100%;">
                                    <strong>{{ $message->sender->name ?? 'Người dùng' }}:</strong> {{ $message->message }}
                                    @include('chat._message_attachment', ['message' => $message])
                                    <div class="text-end text-xs text-muted mt-1">{{ $message->created_at->format('H:i d/m/Y') }}</div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="card-footer">
                    <form id="admin-chat-form" action="{{ route('admin.chat.reply', $conversation) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="attachment" id="admin-chat-attachment" class="d-none">
                        <div class="input-group">
                            <button class="btn btn-outline-secondary" type="button" id="admin-chat-file-button" title="Gui anh hoac file">
                                <i class="bi bi-paperclip"></i>
                            </button>
                            <input type="text" name="message" class="form-control" placeholder="Nhập tin nhắn..." required>
                            <button class="btn btn-primary" type="submit">Gửi</button>
                        </div>
                        <div id="admin-chat-file-preview" class="small text-muted mt-2 d-none"></div>
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
    const adminAttachmentInput = document.getElementById('admin-chat-attachment');
    const adminFileButton = document.getElementById('admin-chat-file-button');
    const adminFilePreview = document.getElementById('admin-chat-file-preview');
    const adminCsrfToken = adminChatForm.querySelector('[name="_token"]').value;
    const adminWs = new WebSocket('{{ env('WS_ENDPOINT', 'ws://127.0.0.1:6001') }}');
    const adminPendingMessages = [];
    const adminDefaultAvatar = '{{ asset('assets/images/default.png') }}';

    adminMessageInput.required = false;

    function adminEscapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        }[char]));
    }

    function adminRenderAttachment(message) {
        if (!message.attachment_url && !message.attachment_name) {
            return '';
        }

        const name = adminEscapeHtml(message.attachment_name || 'File dinh kem');
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

    function adminRenderMessageBody(message, prefix = '') {
        return `${prefix}${message.message ? `<div>${adminEscapeHtml(message.message)}</div>` : ''}${adminRenderAttachment(message)}`;
    }

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
                    <div class="message-content">${adminRenderMessageBody(message, isAdmin ? '' : `<strong>${adminEscapeHtml(message.sender_name || 'Nguoi dung')}:</strong> `)}</div>
                    <div class="text-end text-xs ${isAdmin ? 'text-light' : 'text-muted'} mt-1 timestamp">${message.created_at}</div>
                </div>
            </div>
        `;
        adminChatBody.appendChild(wrapper);
        adminChatBody.scrollTop = adminChatBody.scrollHeight;
    }

    function adminResolvePendingMessage(message) {
        const pendingIndex = adminPendingMessages.findIndex(p => p.clientTempId && p.clientTempId === message.client_temp_id);
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
            const content = existing.querySelector('.message-content');
            if (content) {
                content.innerHTML = adminRenderMessageBody(message);
            }
            adminPendingMessages.splice(pendingIndex, 1);
            return true;
        }

        adminPendingMessages.splice(pendingIndex, 1);
        return false;
    }

    adminFileButton.addEventListener('click', () => adminAttachmentInput.click());

    adminAttachmentInput.addEventListener('change', () => {
        const file = adminAttachmentInput.files[0];
        if (!file) {
            adminFilePreview.classList.add('d-none');
            adminFilePreview.textContent = '';
            return;
        }

        adminFilePreview.classList.remove('d-none');
        adminFilePreview.textContent = `Da chon: ${file.name}`;
    });

    adminChatForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const messageText = adminMessageInput.value.trim();
        const selectedFile = adminAttachmentInput.files[0] || null;
        if (!messageText && !selectedFile) {
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
            attachment_name: selectedFile ? selectedFile.name : null,
            attachment_url: null,
            attachment_is_image: selectedFile ? selectedFile.type.startsWith('image/') : false,
            created_at: localCreatedAt,
        };
        adminPendingMessages.push({ tempId, clientTempId: tempId, sender_id: adminUserId });
        adminAppendMessage(pendingMessage, true);

        const payload = new FormData(adminChatForm);
        payload.set('message', messageText);
        payload.set('client_temp_id', tempId);

        try {
            const response = await fetch(adminChatForm.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': adminCsrfToken,
                },
                body: payload,
            });

            if (!response.ok) {
                console.error('Admin send failed', response.status);
                return;
            }

            const data = await response.json();
            if (!(data && data.status === 'ok')) {
                console.error('Admin send invalid response', data);
            } else {
                adminResolvePendingMessage(data.message);
            }
            adminMessageInput.value = '';
            adminAttachmentInput.value = '';
            adminFilePreview.classList.add('d-none');
            adminFilePreview.textContent = '';
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
