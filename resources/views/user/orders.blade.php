@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">
                <i class="fas fa-receipt me-2"></i> Đơn Hàng Của Tôi
            </h1>
            <p class="text-muted small mt-1">Quản lý và xem chi tiết tất cả đơn hàng của bạn</p>
        </div>
    </div>

    @if($orders->isEmpty())
        <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <div>
                Bạn chưa có đơn hàng nào. <a href="{{ route('user.services') }}" class="alert-link">Bắt đầu mua sắm ngay</a>
            </div>
        </div>
    @else
        <!-- Filter Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="btn-group" role="group">
                    <a href="{{ route('user.orders') }}" class="btn btn-outline-primary {{ !request('status') ? 'active' : '' }}">
                        Tất cả ({{ $orders->count() }})
                    </a>
                    <a href="{{ route('user.orders', ['status' => 'pending']) }}" class="btn btn-outline-primary {{ request('status') === 'pending' ? 'active' : '' }}">
                        Chờ xử lý ({{ $orders->where('status', 'pending')->count() }})
                    </a>
                    <a href="{{ route('user.orders', ['status' => 'processing']) }}" class="btn btn-outline-primary {{ request('status') === 'processing' ? 'active' : '' }}">
                        Đang xử lý ({{ $orders->where('status', 'processing')->count() }})
                    </a>
                    <a href="{{ route('user.orders', ['status' => 'completed']) }}" class="btn btn-outline-primary {{ request('status') === 'completed' ? 'active' : '' }}">
                        Hoàn thành ({{ $orders->where('status', 'completed')->count() }})
                    </a>
                    <a href="{{ route('user.orders', ['status' => 'cancelled']) }}" class="btn btn-outline-primary {{ request('status') === 'cancelled' ? 'active' : '' }}">
                        Đã hủy ({{ $orders->where('status', 'cancelled')->count() }})
                    </a>
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Mã Đơn Hàng</th>
                                <th>Ngày Đặt</th>
                                <th>Số Mặt Hàng</th>
                                <th>Tổng Cộng</th>
                                <th>Trạng Thái</th>
                                <th>Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td>
                                        <strong>#{{ $order->id }}</strong>
                                    </td>
                                    <td>
                                        {{ $order->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td>
                                        {{ $order->items->count() }} mặt hàng
                                    </td>
                                    <td>
                                        <strong class="text-primary">{{ number_format($order->total_amount, 0, ',', '.') }} ₫</strong>
                                    </td>
                                    <td>
                                        @switch($order->status)
                                            @case('pending')
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-clock me-1"></i> Chờ xử lý
                                                </span>
                                                @break
                                            @case('processing')
                                                <span class="badge bg-info">
                                                    <i class="fas fa-spinner me-1"></i> Đang xử lý
                                                </span>
                                                @break
                                            @case('completed')
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i> Hoàn thành
                                                </span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times me-1"></i> Đã hủy
                                                </span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        <a href="{{ route('user.order-detail', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i> Xem Chi Tiết
                                        </a>
                                        @if($order->status === 'completed')
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportInvoice({{ $order->id }})">
                                                <i class="fas fa-download me-1"></i> In Hóa Đơn
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($orders instanceof \Illuminate\Pagination\Paginator)
                    <nav class="mt-4">
                        {{ $orders->links() }}
                    </nav>
                @endif
            </div>
        </div>
    @endif
</div>

<script>
function exportInvoice(orderId) {
    window.location.href = `/user/order/${orderId}/export`;
}
</script>
@endsection
