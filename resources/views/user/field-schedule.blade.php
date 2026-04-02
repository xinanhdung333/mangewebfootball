@extends('layouts.app')
@section('content')

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1><i class="bi bi-calendar-check"></i> Lịch đặt sân</h1>
            <p class="text-muted">Danh sách toàn bộ khung giờ đã được đặt của các sân.</p>
        </div>
    </div>

    @if($fields && count($fields) > 0)
        @foreach($fields as $field)
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-geo-alt"></i>
                        <strong>{{ $field->name }}</strong>
                        @if($field->location)
                            — <span>{{ $field->location }}</span>
                        @endif
                    </h5>
                </div>

                <div class="card-body">
                    @if(isset($bookingMap[$field->id]) && count($bookingMap[$field->id]) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-custom mb-0">
                                <thead>
                                    <tr>
                                        <th>Ngày đặt</th>
                                        <th>Bắt đầu</th>
                                        <th>Kết thúc</th>
                                        <th>Người đặt</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bookingMap[$field->id] as $booking)
                                        <tr>
                                            <td>
                                                <strong>{{ Carbon\Carbon::parse($booking->booking_date)->format('d/m/Y') }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $booking->start_time }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $booking->end_time }}</span>
                                            </td>
                                            <td>{{ $booking->user?->name ?? 'Khách' }}</td>
                                            <td>
                                                @if($booking->status === 'confirmed')
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle"></i> Xác nhận
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="bi bi-clock"></i> Chờ xác nhận
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i>
                            Hiện chưa có khung giờ nào được đặt cho sân này.
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <div class="alert alert-warning text-center py-5">
            <i class="bi bi-exclamation-triangle" style="font-size: 3rem;"></i>
            <p class="mt-3">Hiện tại không có sân nào hoặc không có lịch đặt sân.</p>
        </div>
    @endif
</div>

<style>
.card {
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15) !important;
}

.badge {
    font-weight: 500;
    padding: 0.5rem 0.75rem;
}
</style>

@endsection
