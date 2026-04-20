@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1><i class="bi bi-people"></i> Danh sách chat</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Người dùng</th>
                                <th>Tin nhắn mới nhất</th>
                                <th>Cập nhật</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($conversations as $conversation)
                                <tr>
                                    <td>{{ $conversation->id }}</td>
                                    <td>{{ $conversation->user->name ?? 'Người dùng' }}</td>
                                    <td>{{ optional($conversation->messages()->latest()->first())->message ?? 'Chưa có tin nhắn' }}</td>
                                    <td>{{ $conversation->updated_at->format('H:i d/m/Y') }}</td>
                                    <td>
                                        @if($conversation->admin_id && $conversation->admin_id !== auth()->id())
                                            <span class="badge bg-secondary">Đã xử lý bởi {{ $conversation->admin->name ?? 'admin khác' }}</span>
                                        @else
                                            <a href="{{ route('admin.chat.show', $conversation) }}" class="btn btn-sm btn-outline-primary">Mở chat</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
