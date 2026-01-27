@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-calendar"></i> Quản lý đặt sân</h1>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Status Filter Form -->
<form method="GET" class="mb-3" style="max-width: 300px;">
    <select name="status" class="form-select" onchange="this.form.submit()">
        <option value="">-- Tất cả --</option>
        <option value="pending" {{ $filterStatus == 'pending' ? 'selected' : '' }}>Chờ xác nhận</option>
        <option value="confirmed" {{ $filterStatus == 'confirmed' ? 'selected' : '' }}>Xác nhận</option>
        <option value="in_progress" {{ $filterStatus == 'in_progress' ? 'selected' : '' }}>Đang diễn ra</option>
        <option value="completed" {{ $filterStatus == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
        <option value="cancelled" {{ $filterStatus == 'cancelled' ? 'selected' : '' }}>Hủy</option>
    </select>
</form>

<div class="row">
    <div class="col-md-12">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Khách hàng</th>
                        <th>Sân</th>
                        <th>Ngày</th>
                        <th>Thời gian</th>
                        <th>Giá</th>
                        <th>Trạng thái</th>
                        <th>Dịch vụ</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($bookings->count() > 0)
                        @foreach ($bookings as $booking)
                            <tr>
                                <td>#{{ $booking->id }}</td>
                                <td>
                                    <strong>{{ htmlspecialchars($booking->user->name) }}</strong><br>
                                    <small>{{ $booking->user->phone ?? 'N/A' }}</small>
                                </td>
                                <td>{{ htmlspecialchars($booking->field->name ?? 'N/A') }}</td>
                                <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('d/m/Y') }}</td>
                                <td>{{ $booking->start_time }} - {{ $booking->end_time }}</td>
                                <td>{{ number_format($booking->total_price, 0, ',', '.') }}đ</td>
                                <td>
                                    @php
                                        $statusMap = [
                                            'pending'     => ['warning', 'Chờ xác nhận'],
                                            'confirmed'   => ['primary', 'Đã xác nhận'],
                                            'in_progress' => ['secondary', 'Đang diễn ra'],
                                            'completed'   => ['success', 'Hoàn thành'],
                                            'cancelled'   => ['dark', 'Hủy'],
                                            'expired'     => ['danger', 'Hết hạn'],
                                        ];
                                        $color = $statusMap[$booking->status][0] ?? 'secondary';
                                        $label = $statusMap[$booking->status][1] ?? $booking->status;
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ $label }}</span>
                                </td>
                                <td>
                                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#serviceModal{{ $booking->id }}">
                                        Xem
                                    </button>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('boss.update-booking-status') }}" style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                            @foreach ($statusMap as $key => $value)
                                                <option value="{{ $key }}" {{ $booking->status == $key ? 'selected' : '' }}>
                                                    {{ $value[1] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                            </tr>

                            <!-- Modal xem dịch vụ -->
                            <div class="modal fade" id="serviceModal{{ $booking->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Dịch vụ đã chọn - Đơn #{{ $booking->id }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            @php
                                                $services = DB::table('booking_services as bs')
                                                    ->join('services as s', 'bs.service_id', '=', 's.id')
                                                    ->select('s.name', 's.price', 's.image', 'bs.quantity')
                                                    ->where('bs.booking_id', $booking->id)
                                                    ->get();
                                            @endphp
                                            @if ($services->count() > 0)
                                                @foreach ($services as $service)
                                                    <div class="d-flex align-items-center border-bottom py-2">
                                                        <img src="{{ asset('uploads/services/' . $service->image) }}" alt="{{ $service->name }}"
                                                            style="width: 70px; height: 70px; object-fit: cover; border-radius: 5px; margin-right: 15px;">
                                                        <div>
                                                            <strong>{{ $service->name }}</strong> x {{ $service->quantity }}<br>
                                                            <small>{{ number_format($service->price, 0, ',', '.') }}đ</small>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <p class="text-muted">Không có dịch vụ nào.</p>
                                            @endif
                                        </div>
                                        <div class="modal-footer">
                                            <button class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="9" class="text-center">Không có đặt sân nào</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
