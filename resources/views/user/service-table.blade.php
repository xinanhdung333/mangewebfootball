<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="mb-2">Tổng số dịch vụ</h6>
                <div class="fs-4 fw-semibold">{{ $myServices->total() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="mb-2">Chờ xử lý</h6>
                <div class="fs-4 fw-semibold">{{ $myServices->getCollection()->filter(fn ($item) => optional($item->order)->status === 'pending')->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="mb-2">Đã xác nhận</h6>
                <div class="fs-4 fw-semibold">{{ $myServices->getCollection()->filter(fn ($item) => optional($item->order)->status === 'confirmed')->count() }}</div>
            </div>
        </div>
    </div>
</div>

@if($myServices->count())
    <div class="row g-4">
        @foreach($myServices as $item)
            @php
                $orderStatus = optional($item->order)->status;
                $statusMap = [
                    'pending' => ['warning', 'Chờ xử lý'],
                    'confirmed' => ['success', 'Đã xác nhận'],
                    'completed' => ['primary', 'Hoàn thành'],
                    'cancelled' => ['danger', 'Đã hủy'],
                ];
                [$badgeClass, $label] = $statusMap[$orderStatus] ?? ['secondary', 'Không xác định'];
                $image = $item->image ? asset('uploads/services/' . $item->image) : 'https://via.placeholder.com/160x120?text=Service';
            @endphp

            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="row g-0 h-100">
                        <div class="col-sm-4">
                            <img src="{{ $image }}" alt="{{ $item->service->name ?? 'Dich vu' }}" class="img-fluid h-100 w-100 rounded-start" style="object-fit: cover; min-height: 160px;">
                        </div>
                        <div class="col-sm-8">
                            <div class="card-body d-flex flex-column h-100">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                    <h5 class="card-title mb-0">{{ $item->service->name ?? 'Dich vu' }}</h5>
                                    <span class="badge bg-{{ $badgeClass }}">{{ $label }}</span>
                                </div>
                                <p class="text-muted mb-2">Số lượng: {{ $item->quantity }}</p>
                                <p class="text-muted mb-3">Đơn giá: {{ number_format($item->price, 0, ',', '.') }}d</p>
                                <p class="text-muted mb-3">Mã dịch vụ: #{{ $item->id }}</p>
                                 <p class="text-muted mb-3">Phương thức thanh toán: {{ $item->order->payment->payment_method ?? 'Chưa xác định' }}</p>
                                <div class="mt-auto d-flex justify-content-between align-items-center">
                                    <strong>{{ number_format($item->total_amount, 0, ',', '.') }}d</strong>
                                    <a href="{{ route('user.orderDetail', $item->order_id) }}" class="btn btn-sm btn-outline-primary">Xem đơn hàng</a>
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
            <h5 class="mb-2">Chưa có dịch vụ nào</h5>
            <p class="text-muted mb-3">Không tìm thấy dịch vụ phù hợp với bộ lọc hiện tại.</p>
            <a href="{{ route('user.services') }}" class="btn btn-primary">Xem dịch vụ</a>
        </div>
    </div>
@endif

@if($myServices->hasPages())
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mt-4">
        <div class="text-white small">
            Hiển thị {{ $myServices->firstItem() }}-{{ $myServices->lastItem() }} trong tổng {{ $myServices->total() }} dịch vụ
        </div>
        <div class="d-flex flex-column align-items-center gap-2">
            <div class="text-white small">Trang {{ $myServices->currentPage() }} / {{ $myServices->lastPage() }}</div>
        <div class="service-pagination bg-white rounded-3 px-3 py-2 shadow-sm d-flex flex-wrap justify-content-center gap-2">
            @if(!$myServices->onFirstPage())
                <a href="{{ $myServices->previousPageUrl() }}" class="btn btn-sm btn-outline-secondary">Truoc</a>
            @endif
            @foreach($myServices->getUrlRange(1, $myServices->lastPage()) as $page => $url)
                <a href="{{ $url }}" class="btn btn-sm {{ $page === $myServices->currentPage() ? 'btn-primary' : 'btn-outline-primary' }}">{{ $page }}</a>
            @endforeach
            @if($myServices->hasMorePages())
                <a href="{{ $myServices->nextPageUrl() }}" class="btn btn-sm btn-outline-secondary">Sau</a>
            @endif
            </div>
        </div>
    </div>
@endif
