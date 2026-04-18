@php
    $statusLabels = [
        'pending' => ['warning', 'Chờ xử lý'],
        'confirmed' => ['success', 'Đã xác nhận'],
        'completed' => ['primary', 'Hoàn thành'],
        'cancelled' => ['danger', 'Đã hủy'],
    ];
@endphp

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="mb-2">Tổng đơn sân</h6>
                <div class="fs-4 fw-semibold">{{ $myBookings->total() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="mb-2">Chờ xử lý</h6>
                <div class="fs-4 fw-semibold">{{ $pendingCount ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="mb-2">Đã xác nhận</h6>
                <div class="fs-4 fw-semibold">{{ $paidCount ?? 0 }}</div>
            </div>
        </div>
    </div>
</div>

@if($myBookings->count())
    <div class="row g-4">
        @foreach($myBookings as $booking)
            @php
                [$badgeClass, $label] = $statusLabels[$booking->status] ?? ['secondary', ucfirst($booking->status)];
                $image = !empty($booking->field?->image)
                    ? asset('uploads/fields/' . $booking->field->image)
                    : asset('assets/images/banner.jpg');
            @endphp

            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="row g-0 h-100">
                        <div class="col-sm-4">
                            <img
                                src="{{ $image }}"
                                alt="{{ $booking->field->name ?? 'Sân bóng' }}"
                                class="img-fluid h-100 w-100 rounded-start"
                                style="object-fit: cover; min-height: 180px;"
                            >
                        </div>
                        <div class="col-sm-8">
                            <div class="card-body d-flex flex-column h-100">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                    <h5 class="card-title mb-0">{{ $booking->field->name ?? 'Không có sân' }}</h5>
                                    <span class="badge bg-{{ $badgeClass }}">{{ $label }}</span>
                                </div>

                                <p class="text-muted mb-2">Ngày đặt: {{ optional($booking->booking_date)->format('d/m/Y') ?? $booking->booking_date }}</p>
                                <p class="text-muted mb-2">Khung giờ: {{ $booking->start_time }} - {{ $booking->end_time }}</p>
                                <p class="text-muted mb-3">Mã booking: #{{ $booking->id }}</p>

                                <div class="mt-auto d-flex justify-content-between align-items-center">
                                    <strong>{{ number_format($booking->total_price, 0, ',', '.') }}đ</strong>
                                    <a href="{{ route('user.bookingdetail', $booking->id) }}" class="btn btn-sm btn-outline-primary">
                                        Xem chi tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <h5 class="mb-2">Chưa có sân nào</h5>
            <p class="text-muted mb-3">Không tìm thấy lịch đặt sân phù hợp với bộ lọc hiện tại.</p>
            <a href="{{ route('user.fields') }}" class="btn btn-primary">Đặt sân ngay</a>
        </div>
    </div>
@endif

@if($myBookings->hasPages())
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mt-4">
        <div class="text-white small">
            Hiển thị {{ $myBookings->firstItem() }}-{{ $myBookings->lastItem() }} / {{ $myBookings->total() }} booking
        </div>
        <div class="bg-white rounded-3 px-3 py-2 shadow-sm">
            {{ $myBookings->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endif
