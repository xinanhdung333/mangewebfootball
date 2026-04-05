<div class="container-fluid">    <div class="row">

        <!-- LEFT: danh sách dịch vụ -->
        <div class="col-md-8">
            @if($myServices->count())               
                <div class="history-list">
                    @foreach($myServices as $h)
                        <div class="card history-item mb-3">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <img src="{{ !empty($h['image']) ? asset('uploads/services/' . $h['image']) : asset('images/default.png') }}"
                                            style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px;">
                                    </div>

                                    <div class="col">
                                        <h5>{{ $h['name'] }}</h5>

                                        <strong>Trạng thái:</strong>
                                        @if($h['status'] === 'pending')
                                            <span class="badge bg-warning text-dark">Chờ xử lý</span>
                                        @elseif($h['status'] === 'paid')
                                            <span class="badge bg-success">Đã thanh toán</span>
                                        @elseif($h['status'] === 'cancelled')
                                            <span class="badge bg-danger">Đã huỷ</span>
                                        @endif

                                        <p class="mb-1">
                                            <strong>Số lượng:</strong> {{ $h['quantity'] }}
                                        </p>

                                        <p class="mb-1">
                                            <strong>Tổng đơn:</strong>
                                            <span class="text-success">
                                                {{ number_format($h['total_amount'], 0, ',', '.') }} VNĐ
                                            </span>
                                        </p>

                                        <p>
                                            <strong>Ngày mua:</strong>
                                            {{ $h['created_at']->format('d/m/Y H:i') }}
                                        </p>

                                        <a href="{{ route('user.orderDetail', ['id' => $h['order_id']]) }}"
                                           class="btn btn-sm btn-info">
                                           👁 Xem chi tiết
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- RIGHT: sidebar -->
        <div class="col-md-4" id="ll">

            <!-- Thống kê -->
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h5>📊 Thống kê</h5>
                    <p>Tổng dịch vụ: {{ $myServices->total() }}</p>
                    <p>Chờ xử lý: 
                        {{ $myServices->where('status','pending')->count() }}
                    </p>
                    <p>Đã thanh toán: 
                        {{ $myServices->where('status','paid')->count() }}
                    </p>
                </div>
            </div>

            <!-- Khuyến mãi -->
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h5>🎁 Khuyến mãi</h5>
                    <p>Giảm 20% dịch vụ hôm nay</p>
                    <a href="{{ route('user.services') }}" class="btn btn-primary btn-sm">
                        Xem dịch vụ
                    </a>
                </div>
            </div>

        </div>

    </div>
</div>

   <div class="mt-3">
               {{ $myServices->links('pagination::bootstrap-5') }}
               </div>
<style>
    #ll{
        position: fixed;
        top:250px;
        width: 500px !important;
        right: 20px;
        width: 300px;

    }
.container-cart {
    max-width: 900px;
    margin: 20px 0;
}
.history-item {
    border-left: 4px solid #0d6efd;
    transition: 0.2s;
}

.history-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.cart-item, .history-item {
    transition: all 0.3s ease;
    border-left: 4px solid #007bff;
}

.cart-item:hover {
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
}

.quantity-control {
    display: inline-flex;
    gap: 8px;
    align-items: center;
}

.qty-btn {
    padding: 4px 10px;
    font-size: 14px;
}

@media (max-width: 768px) {
    .cart-item .row {
        flex-direction: column;
    }

    .cart-item .col-auto:last-child {
        margin-left: 0 !important;
    }
}
.row{   
    margin-left: 0;
    margin-right: 0;
}
</style>

     

