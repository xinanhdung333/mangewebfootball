@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-chat-dots"></i> Quản Lý Phản Hồi</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Danh sách phản hồi từ người dùng</h5>
            </div>
            <div class="card-body">
                @if ($feedbacks && $feedbacks->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Người gửi</th>
                                    <th>Ngày gửi</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($feedbacks as $feedback)
                                    <tr>
                                        <td>#{{ $feedback->id }}</td>
                                        <td>{{ $feedback->user_name ?? 'Anonymous' }}</td>
                                        <td>{{ $feedback->created_at }}</td>
                                        <td><span class="badge bg-secondary">Chưa xử lý</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary">Xem</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if ($feedbacks->hasPages())
                        <div class="mt-3">
                            {{ $feedbacks->links() }}
                        </div>
                    @endif
                @else
                    <p class="text-muted">Không có phản hồi nào</p>
                @endif
            </div>
        </div>
    </div>
</div>
