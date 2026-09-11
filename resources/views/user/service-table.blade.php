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
                <div class="fs-4 fw-semibold">{{ $myServices->getCollection()->where('status', 'pending')->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="mb-2">Đã xác nhận</h6>
                <div class="fs-4 fw-semibold">{{ $myServices->getCollection()->whereIn('status', ['confirmed', 'completed'])->count() }}</div>
            </div>
        </div>
    </div>
</div>

@if($myServices->count())
    <div class="row g-4">
        @foreach($myServices as $order)
            @php
            $orderStatus = $order->status;
                $statusMap = [
                    'pending' => ['warning', 'Chờ xử lý'],
                    'confirmed' => ['success', 'Đã xác nhận'],
                    'completed' => ['primary', 'Hoàn thành'],
                    'cancelled' => ['danger', 'Đã hủy'],
                ];
                [$badgeClass, $label] = $statusMap[$orderStatus] ?? ['secondary', 'Không xác định'];
                $items = $order->items;
            @endphp

            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="row g-0 h-100 align-items-center">
                        <div class="col-sm-4 p-3">
                            <div class="order-image-stack" aria-label="{{ $items->count() }} sản phẩm trong đơn">
                                @foreach($items->take(4) as $index => $item)
                                    @php
                                        $image = $item->service?->image
                                            ? asset('uploads/services/' . $item->service->image)
                                            : asset('images/default.png');
                                    @endphp
                                    <img src="{{ $image }}" alt="{{ $item->service->name ?? 'Dich vu' }}" class="order-stack-image" style="--stack-index: {{ $index }};">
                                @endforeach
                                @if($items->count() > 4)
                                    <span class="order-image-more">+{{ $items->count() - 4 }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-8">
                            <div class="card-body d-flex flex-column h-100">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                    <h5 class="card-title mb-0">Đơn hàng #{{ $order->id }}</h5>
                                    <span class="badge bg-{{ $badgeClass }}">{{ $label }}</span>
                                </div>
                                <p class="text-muted mb-2">{{ $items->count() }} sản phẩm trong đơn</p>
                                <p class="text-muted mb-2">Tổng số lượng: {{ $items->sum('quantity') }}</p>
                                <p class="text-muted mb-3">Phương thức thanh toán: {{ $order->payment->payment_method ?? 'Chưa xác định' }}</p>
                                <div class="mt-auto">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1">
                                        <strong class="order-price">{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong>
                                    </div>
                                    <div class="order-btn-row">
                                        @if($order->status === 'pending')
                                            <a href="{{ route('user.payment.order', $order->id) }}" class="btn btn-sm btn-primary">Thanh toán</a>
                                        @endif
                                        <a href="{{ route('user.orderDetail', $order->id) }}" class="btn btn-sm btn-outline-primary">Xem chi tiết</a>
                                    </div>
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
    @php
        $cur  = $myServices->currentPage();
        $last = $myServices->lastPage();
        $window = 2; // pages shown each side of current
        $pages = [];
        for ($p = max(1, $cur - $window); $p <= min($last, $cur + $window); $p++) {
            $pages[] = $p;
        }
    @endphp
    <div class="service-pagination-wrap">
        <div class="spag-info">Trang {{ $cur }} / {{ $last }} &nbsp;·&nbsp; {{ $myServices->total() }} đơn</div>
        <div class="service-pagination">
            {{-- First + Prev --}}
            @if(!$myServices->onFirstPage())
                <a href="{{ $myServices->url(1) }}" class="spag-btn">«</a>
                <a href="{{ $myServices->previousPageUrl() }}" class="spag-btn">‹</a>
            @endif

            {{-- Leading ellipsis --}}
            @if($pages[0] > 1)
                <span class="spag-ellipsis">…</span>
            @endif

            {{-- Windowed pages --}}
            @foreach($pages as $p)
                <a href="{{ $myServices->url($p) }}"
                   class="spag-btn {{ $p === $cur ? 'active' : '' }}">
                    {{ $p }}
                </a>
            @endforeach

            {{-- Trailing ellipsis --}}
            @if(end($pages) < $last)
                <span class="spag-ellipsis">…</span>
            @endif

            {{-- Next + Last --}}
            @if($myServices->hasMorePages())
                <a href="{{ $myServices->nextPageUrl() }}" class="spag-btn">›</a>
                <a href="{{ $myServices->url($last) }}" class="spag-btn">»</a>
            @endif
        </div>
    </div>
@endif

<style>
.order-image-stack {
    position: relative;
    width: 150px;
    height: 150px;
    margin: 0 auto;
}

.order-stack-image {
    position: absolute;
    top: calc(var(--stack-index) * 8px);
    left: calc(var(--stack-index) * 8px);
    z-index: calc(10 - var(--stack-index));
    width: 124px;
    height: 124px;
    object-fit: cover;
    border: 3px solid #fff;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, .16);
}

.order-image-more {
    position: absolute;
    right: 0;
    bottom: 0;
    z-index: 20;
    min-width: 32px;
    padding: 5px 8px;
    color: #fff;
    background: #f4512a;
    border-radius: 16px;
    font-weight: 700;
    text-align: center;
}

/* ===== Compact smart pagination ===== */
.service-pagination-wrap {
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}
.spag-info {
    font-size: .78rem;
    color: #888;
}
.service-pagination {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
    justify-content: center;
}
.spag-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 34px;
    height: 34px;
    padding: 0 6px;
    border-radius: 8px;
    font-size: .82rem;
    font-weight: 500;
    border: 1.5px solid #dee2e6;
    background: #fff;
    color: #333;
    text-decoration: none;
    transition: background .15s, border-color .15s, color .15s;
}
.spag-btn:hover {
    background: #f0f0f0;
    color: #333;
}
.spag-btn.active {
    background: #ee4d2d;
    border-color: #ee4d2d;
    color: #fff;
    font-weight: 700;
}
.spag-ellipsis {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 34px;
    color: #aaa;
    font-size: .85rem;
    user-select: none;
}

/* Mobile-only: fix button row layout */
@media (max-width: 767px) {
    .order-btn-row {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 6px;
    }
    .order-btn-row .btn {
        flex: 1;
        min-width: 80px;
        text-align: center;
        font-size: .78rem !important;
        padding: 6px 10px !important;
        border-radius: 20px !important;
        white-space: nowrap;
    }
    .order-price {
        font-size: .9rem;
        color: #ee4d2d;
    }
    .order-image-stack {
        margin-bottom: 8px;
    }
}
@media (min-width: 768px) {
    .order-btn-row {
        display: flex;
        gap: 6px;
        margin-top: 4px;
    }
}
</style>
