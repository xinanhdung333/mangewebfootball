@extends('layouts.app')

@section('content')

<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-pencil-square"></i> Chỉnh Sửa Trạng Thái</h1>
    </div>
</div>

{{-- ALERT --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif


@if(isset($order))

@php
$statusMap = [
    'pending' => 'Chờ xác nhận',
    'confirmed' => 'Xác nhận',
    'completed' => 'Hoàn thành',
];
@endphp

{{-- ================= ORDER INFO ================= --}}
<div class="row">
    <div class="col-md-8">

        <div class="card mb-4">
            <div class="card-header">
                <h5>Đơn hàng #{{ $order->id }}</h5>
            </div>

            <div class="card-body">

                <p><b>Khách hàng:</b> {{ $order->user->name ?? 'N/A' }}</p>
                <p><b>SĐT:</b> {{ $order->user->phone ?? 'N/A' }}</p>
                <p><b>Ngày tạo:</b> {{ $order->created_at->format('d/m/Y H:i') }}</p>

                <p>
                    <b>Trạng thái hiện tại:</b>
                    <span class="badge bg-info">
                        {{ $statusMap[$order->status] ?? $order->status }}
                    </span>
                </p>

                <p><b>Tổng tiền:</b> {{ number_format($order->total_amount, 0, ',', '.') }} đ</p>

                <hr>

                {{-- ================= UPDATE ORDER STATUS ================= --}}
                <form method="POST" action="{{ route('admin.update.order.status') }}">
                    @csrf

                    <input type="hidden" name="order_id" value="{{ $order->id }}">

                    <div class="mb-3">
                        <label class="form-label">Cập nhật trạng thái đơn</label>

                        <select name="status" class="form-select" required>
                            <option value="">-- Chọn --</option>

                            @foreach($statusMap as $key => $value)
                                <option value="{{ $key }}" @selected($order->status == $key)>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button class="btn btn-primary">
                        Cập nhật đơn hàng
                    </button>
                </form>

            </div>
        </div>


        {{-- ================= ORDER ITEMS ================= --}}
        <div class="card">

            <div class="card-header">
                <h5>Trạng thái từng dịch vụ</h5>
            </div>

            <div class="card-body">

                <form method="POST" action="{{ route('admin.update.order.items.status') }}">
                    @csrf

                    <input type="hidden" name="order_id" value="{{ $order->id }}">

                    <table class="table table-bordered align-middle">

                        <thead>
                            <tr>
                                <th>Dịch vụ</th>
                                <th>SL</th>
                                <th>Giá</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>

                        <tbody>

                        @foreach($order->items as $item)
                            <tr>

                                <td>
                                    {{ $item->service->name ?? 'N/A' }}
                                </td>

                                <td>
                                    {{ $item->quantity }}
                                </td>

                                <td>
                                    {{ number_format($item->price, 0, ',', '.') }} đ
                                </td>

                                <td>
                                    <select name="items[{{ $item->id }}]" class="form-select">

                                        <option value="pending" @selected($item->status == 'pending')>
                                            Pending
                                        </option>
                                        <option value="confirmed" @selected($item->status == 'confirmed')>
                                            Confirmed
                                        </option>
                                        <option value="processing" @selected($item->status == 'processing')>
                                            Processing
                                        </option>

                                        <option value="completed" @selected($item->status == 'completed')>
                                            Completed
                                        </option>

                                        <option value="cancelled" @selected($item->status == 'cancelled')>
                                            Cancelled
                                        </option>

                                    </select>
                                </td>

                            </tr>
                        @endforeach

                        </tbody>

                    </table>

                    <button class="btn btn-success">
                        Cập nhật từng dịch vụ
                    </button>

                </form>

            </div>
        </div>

    </div>
</div>

@else

<div class="alert alert-warning">
    Không tìm thấy đơn hàng
</div>

@endif
</br>
</br>
</br>
</br>
</br>

@endsection