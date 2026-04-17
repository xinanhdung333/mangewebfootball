@extends('layouts.app')
@section('content')

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="{{ route('user.myBookings') }}" class="btn btn-secondary mb-3">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
            <h1>Chi tiết đặt sân #{{ $booking->id }}</h1>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Lỗi!</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(request()->get('msg') === 'success')
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> Đặt sân thành công! Vui lòng chờ xác nhận từ quản lý.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">

            <!-- ẢNH SÂN -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Hình ảnh sân</h5>
                    @if($booking->field && $booking->field->image)
                        <img src="{{ asset('uploads/fields/' . $booking->field->image) }}" 
                             class="img-fluid rounded" alt="{{ $booking->field->name }}" style="max-height: 400px; object-fit: cover;">
                    @else
                        <p class="text-muted">Không có ảnh sân</p>
                    @endif
                </div>
            </div>

            <!-- THÔNG TIN SÂN -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Thông tin sân</h5>
                    @if($booking->field)
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold">Tên sân:</td>
                                    <td>{{ $booking->field->name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Địa chỉ:</td>
                                    <td>{{ $booking->field->location ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Mô tả:</td>
                                    <td>{{ $booking->field->description ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">Thông tin sân không khả dụng</p>
                    @endif
                </div>
            </div>
            
            <!-- THÔNG TIN ĐẶT SÂN -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Thông tin đặt sân</h5>
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">Ngày đặt:</td>
                                <td>{{ $booking->booking_date->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Thời gian:</td>
                                <td>{{ $booking->start_time }} - {{ $booking->end_time }}</td>
                            </tr>
                            @if($booking->field)
                            <tr>
                                <td class="fw-bold">Giá/giờ:</td>
                                <td>{{ number_format($booking->field->price_per_hour, 0, ',', '.') }} VNĐ</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="fw-bold">Tổng giá:</td>
                                <td>
                                    <span class="text-success fw-bold fs-5">
                                        {{ number_format($booking->total_price, 0, ',', '.') }} VNĐ
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- DỊCH VỤ KÈM THEO -->
            @if($booking->services && count($booking->services) > 0)
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Dịch vụ kèm theo</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>Dịch vụ</th>
                                        <th>Số lượng</th>
                                        <th>Giá</th>
                                        <th>Tổng</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($booking->services as $service)
                                        <tr>
                                            <td>
                                                <strong>{{ $service->name }}</strong>
                                                @if($service->image)
                                                    <br>
                                                    <img src="{{ asset('uploads/services/' . $service->image) }}" 
                                                         alt="{{ $service->name }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; margin-top: 5px;">
                                                @endif
                                            </td>
                                            <td>{{ $service->pivot->quantity }}</td>
                                            <td>{{ number_format($service->pivot->price, 0, ',', '.') }} VNĐ</td>
                                            <td class="fw-bold">{{ number_format($service->pivot->quantity * $service->pivot->price, 0, ',', '.') }} VNĐ</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

        </div>
        
        <!-- SIDEBAR TRẠNG THÁI -->
        <div class="col-md-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-body">
                    <h5 class="card-title mb-3">Trạng thái</h5>
                    
                    <div class="mb-3">
                        @php
                            $statusColors = [
                                'pending' => 'warning',
                                'confirmed' => 'success',
                                'in_progress' => 'primary',
                                'completed' => 'info',
                                'cancelled' => 'danger'
                            ];
                            $statusTexts = [
                                'pending' => 'Chờ xác nhận',
                                'confirmed' => 'Đã xác nhận',
                                'in_progress' => 'Đang diễn ra',
                                'completed' => 'Hoàn thành',
                                'cancelled' => 'Đã hủy'
                            ];
                        @endphp
                        <span class="badge bg-{{ $statusColors[$booking->status] ?? 'secondary' }} fs-6 p-2">
                            {{ $statusTexts[$booking->status] ?? 'Không xác định' }}
                        </span>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Ngày tạo</label>
                        <p class="mb-0">{{ $booking->created_at->format('d/m/Y H:i') }}</p>
                    </div>

                    @if($booking->updated_at)
                    <div class="mb-3">
                        <label class="form-label text-muted small">Cập nhật lần cuối</label>
                        <p class="mb-0">{{ $booking->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @endif

                    <hr>

                    <!-- NÚT HỦY: CHỈ CHO PHÉP KHI PENDING -->
                    @if($booking->status === 'pending')
                        <form method="POST" action="{{ route('user.cancelBooking', ['id' => $booking->id]) }}" 
                              onsubmit="return confirm('Bạn chắc chắn muốn hủy đặt sân này?');">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-x-circle"></i> Hủy đặt sân
                            </button>
                        </form>
                    @else
                        <div class="alert alert-info small mb-0">
                            <i class="bi bi-info-circle"></i> Không thể hủy vì sân đã được xử lý.
                        </div>
                    @endif

                    <!-- NÚT XUẤT HÓA ĐƠN CHO ĐẶT SÂN ĐÃ HOÀN THÀNH -->
                    @if($booking->status === 'confirmed' || $booking->status === 'completed')
                        <hr>
                        <a href="{{ route('user.exportInvoicebooking', $booking->id) }}" 
                           target="_blank" 
                           class="btn btn-primary w-100">
                            <i class="bi bi-file-pdf"></i> Xuất hóa đơn
                        </a>
                    @endif
                </div>
            </div>

            <!-- THÔNG TIN LIÊN HỆ -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title mb-3">Hỗ trợ</h6>
                    <p class="small text-muted mb-2">
                        <i class="bi bi-telephone"></i> Liên hệ: <strong>0123 456 789</strong>
                    </p>
                    <p class="small text-muted mb-0">
                        <i class="bi bi-envelope"></i> Email: <strong>support@footballbooking.com</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
