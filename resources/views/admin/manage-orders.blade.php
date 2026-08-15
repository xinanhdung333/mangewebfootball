@extends('layouts.app')

@section('content')

@php
$statusMap = [
    'pending' => ['warning', 'Chờ xác nhận'],
    'confirmed' => ['info', 'Xác nhận'],
    'in_progress' => ['secondary', 'Đang giao'],
    'completed' => ['success', 'Hoàn thành'],
    'cancelled' => ['danger', 'Hủy'],
];
@endphp

<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-bag-check"></i> Quản lý đơn hàng</h1>
    </div>
</div>

{{-- ALERT --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- FILTER --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.manage.orders') }}" class="row g-3">

            <div class="col-md-4">
                <label class="form-label">Khách hàng</label>
                <select name="user_id" class="form-select">
                    <option value="">Tất cả</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả</option>
                    @foreach($statusMap as $key => $value)
                        <option value="{{ $key }}" @selected(request('status') == $key)>
                            {{ $value[1] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 d-flex align-items-end gap-2">
                <button class="btn btn-primary flex-grow-1">
                    Lọc
                </button>
                <a href="{{ route('admin.manage.orders') }}" class="btn btn-secondary">
                    Reset
                </a>
            </div>

        </form>
    </div>
</div>

{{-- TABLE --}}
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Danh sách đơn hàng ({{ $orders->total() }})</h5>
    </div>

    <div class="card-body">
        @if($orders->count())
        @include('partials.admin-table-search', ['tableId' => 'admin-orders-table', 'placeholder' => 'Tìm khách hàng, sản phẩm, đơn hàng...'])
        <div class="table-responsive">
            <table id="admin-orders-table" class="table table-striped align-middle">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Khách</th>
                        <th>SĐT</th>
                        <th>Dịch vụ</th>
                        <th>Trạng thái</th>
                        <th>Tổng tiền</th>
                        <th>Ngày</th>
                        <th>Hành động</th>
                    </tr>
                </thead>

                <tbody>
                @foreach($orders as $order)

                    @php
                        [$class, $text] = $statusMap[$order->status] ?? ['secondary', $order->status];
                    @endphp

                    <tr>
                        <td>#{{ $order->id }}</td>

                        <td>
                            <div class="fw-bold">{{ $order->user->name ?? 'N/A' }}</div>
                            <small class="text-muted">{{ $order->user->email ?? '' }}</small>
                        </td>

                        <td>{{ $order->user->phone ?? '---' }}</td>

                        <td>
                            @foreach($order->items as $item)
                                <div>
                                    • {{ $item->service->name ?? 'N/A' }} (x{{ $item->quantity }})
                                </div>
                            @endforeach
                        </td>

                        <td>
                            <span class="badge bg-{{ $class }}">{{ $text }}</span>
                        </td>

                        <td>
                            <strong>{{ number_format($order->total_amount) }} đ</strong>
                        </td>

                        <td>
                            {{ $order->created_at->format('d/m/Y H:i') }}
                        </td>

                        <td>
                            <a href="{{ route('admin.edit.status.order', $order->id) }}"
                               class="btn btn-sm btn-warning">
                                Sửa
                            </a>

                            <button class="btn btn-sm btn-info"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modal{{ $order->id }}">
                                Xem
                            </button>
                        </td>
                    </tr>

                @endforeach
                </tbody>

            </table>
        </div>
        @else
            <div class="text-center py-5 text-muted">
                Không có đơn hàng
            </div>
        @endif
    </div>
</div>

{{-- MODALS (TÁCH RIÊNG → FIX VỠ GIAO DIỆN) --}}
@foreach($orders as $order)

@php
    [$class, $text] = $statusMap[$order->status] ?? ['secondary', $order->status];
@endphp

<div class="modal fade" id="modal{{ $order->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Đơn #{{ $order->id }}</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <p><b>Khách:</b> {{ $order->user->name }}</p>
                <p><b>Email:</b> {{ $order->user->email }}</p>
                <p><b>SĐT:</b> {{ $order->user->phone }}</p>

                <p>
                    <b>Trạng thái:</b>
                    <span class="badge bg-{{ $class }}">{{ $text }}</span>
                </p>

                <hr>

                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Dịch vụ</th>
                            <th>SL</th>
                            <th>Giá</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->service->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->price) }} đ</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <h5 class="text-end">
                    Tổng: {{ number_format($order->total_amount) }} đ
                </h5>

            </div>

        </div>
    </div>
</div>

@endforeach

{{-- PAGINATION --}}
<div class="mt-3">
    {{ $orders->links() }}
</div>

@endsection
