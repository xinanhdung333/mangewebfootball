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
                <h6 class="mb-2">Tổng số booking</h6>
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
                            <img src="{{ $image }}" alt="{{ $booking->field->name ?? 'San bong' }}" class="img-fluid h-100 w-100 rounded-start" style="object-fit: cover; min-height: 180px;">
                        </div>
                        <div class="col-sm-8">
                            <div class="card-body d-flex flex-column h-100">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                    <h5 class="card-title mb-0">{{ $booking->field->name ?? 'Khong co san' }}</h5>
                                    <span class="badge bg-{{ $badgeClass }}">{{ $label }}</span>
                                </div>
                                <p class="text-muted mb-2">Ngày đặt : {{ optional($booking->booking_date)->format('d/m/Y') ?? $booking->booking_date }}</p>
                                <p class="text-muted mb-2">Khung giờ : {{ $booking->start_time }} - {{ $booking->end_time }}</p>
                                <p class="text-muted mb-3">Mã booking: #{{ $booking->id }}</p>
                                <p class="text-muted mb-3">Phương thức thanh toán: {{ $booking->payment->payment_method ?? 'Chưa xác định' }}</p>
                                <div class="mt-auto d-flex justify-content-between align-items-center">
                                    <strong>{{ number_format($booking->total_price, 0, ',', '.') }}d</strong>
                                    <a href="{{ route('user.bookingdetail', $booking->id) }}" class="btn btn-sm btn-outline-primary">Xem chi tiet</a>
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
            <p class="text-muted mb-3">Không có booking nào.</p>
            <a href="{{ route('user.fields') }}" class="btn btn-primary">Đặt sân ngay</a>
        </div>
    </div>
@endif

@if($myBookings->hasPages())
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mt-4">
        <div class="text-white small">
            Hiển thị {{ $myBookings->firstItem() }}-{{ $myBookings->lastItem() }} trong tổng {{ $myBookings->total() }} booking
        </div>
        <div class="d-flex flex-column align-items-center gap-2">
            <div class="text-white small">Trang {{ $myBookings->currentPage() }} / {{ $myBookings->lastPage() }}</div>
        <div class="booking-pagination bg-white rounded-3 px-3 py-2 shadow-sm d-flex flex-wrap justify-content-center gap-2">
            @if(!$myBookings->onFirstPage())
                <a href="{{ $myBookings->previousPageUrl() }}" class="btn btn-sm btn-outline-secondary">Truoc</a>
            @endif
            @foreach($myBookings->getUrlRange(1, $myBookings->lastPage()) as $page => $url)
                <a href="{{ $url }}" class="btn btn-sm {{ $page === $myBookings->currentPage() ? 'btn-primary' : 'btn-outline-primary' }}">{{ $page }}</a>
            @endforeach
            @if($myBookings->hasMorePages())
                <a href="{{ $myBookings->nextPageUrl() }}" class="btn btn-sm btn-outline-secondary">Sau</a>
            @endif
            </div>
        </div>
    </div>
@endif
