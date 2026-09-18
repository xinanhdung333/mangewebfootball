@extends('layouts.app')

@section('content')
@include('chat._messenger-styles')
<div class="messenger-page">
    <div class="messenger-shell">
        <aside class="messenger-sidebar">
            <div class="messenger-sidebar-header">
                <p class="messenger-sidebar-title">Tin nhắn khách hàng</p>
                <div class="messenger-search"><i class="bi bi-search"></i><span>Tìm người dùng...</span></div>
            </div>
            <div class="messenger-conversations">
                @foreach($conversations as $conversation)
                    <a class="messenger-conversation" href="{{ route('admin.chat.show', $conversation) }}">
                        <span class="messenger-avatar">{{ strtoupper(substr($conversation->user->name ?? 'U', 0, 1)) }}</span>
                        <span class="messenger-conversation-copy">
                            <span class="messenger-conversation-name">{{ $conversation->user->name ?? 'Người dùng' }}</span>
                            <span class="messenger-conversation-preview">{{ $conversation->admin_id ? 'Đã được tiếp nhận' : 'Đang chờ hỗ trợ' }}</span>
                        </span>
                        <i class="bi bi-chevron-right small"></i>
                    </a>
                @endforeach
            </div>
        </aside>
        <section class="messenger-main justify-content-center align-items-center text-center p-4">
            <i class="bi bi-chat-square-text display-4 text-primary mb-3"></i>
            <h1 class="h5 fw-bold">Chọn một cuộc trò chuyện</h1>
            <p class="text-muted small">Chọn khách hàng ở danh sách bên trái để bắt đầu hỗ trợ.</p>
        </section>
    </div>
</div>
@endsection
