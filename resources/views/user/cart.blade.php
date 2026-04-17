@extends('layouts.app')
@section('content')

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1><i class="bi bi-bag-plus"></i> Giỏ hàng</h1>
        </div>
    </div>

    <div class="container-cart">
        <!-- ==== TAB MENU ==== -->
        <ul class="nav nav-tabs mb-4" id="cartTabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#cartTab">Giỏ hàng</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#historyTab">Lịch sử dịch vụ đã mua</a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- ==========================================
                 TAB 1: GIỎ HÀNG
            ============================================ -->
            <div id="cartTab" class="tab-pane fade show active">
                <h2>Giỏ hàng của bạn</h2>

                @if($cartItems && count($cartItems) > 0)
                    <div class="cart-list">
                        @foreach($cartItems as $item)
                            <div class="card cart-item mb-3" 
                                 data-id="{{ $item['id'] }}" 
                                 data-price="{{ $item['price'] }}" 
                                 data-stock="{{ $item['stock'] }}">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <input type="checkbox"
class="form-check-input select-item"
name="selected_items[]"
value="{{ $item['id'] }}"
style="width:25px;height:25px;cursor:pointer;">
                                        </div>
                                        <div class="col-auto">
                                            <img src="{{ !empty($item['image']) ? asset('uploads/services/' . $item['image']) : asset('images/default.png') }}" 
                                                 alt="{{ $item['name'] }}" style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px;">
                                        </div>
                                        <div class="col">
                                            <h5 class="card-title">{{ $item['name'] }}</h5>
                                            <p class="card-text">
                                                Giá: <span class="price text-success fw-bold">{{ number_format($item['price'], 0, ',', '.') }} VNĐ</span>
                                            </p>

                                            <div class="quantity-control mb-2">
                                                <button class="btn btn-sm btn-outline-secondary qty-btn decrease" 
                                                        data-item-id="{{ $item['id'] }}">-</button>
                                                <span class="qty mx-2">{{ $item['quantity'] }}</span>
                                                <button class="btn btn-sm btn-outline-secondary qty-btn increase" 
                                                        data-item-id="{{ $item['id'] }}">+</button>
                                            </div>

                                            <p class="card-text">
                                                Tổng: <span class="item-total fw-bold text-primary">
                                                    {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }} VNĐ
                                                </span>
                                            </p>

                                            <form method="POST" action="{{ route('user.removeFromCart') }}" style="display: inline;">
                                                @csrf
                                                <input type="hidden" name="cart_item_id" value="{{ $item['id'] }}">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i> Xóa
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="card mt-4 bg-light">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    Tổng thanh toán: <span id="cart-total" class="text-primary fw-bold">
                                        {{ number_format($totalPrice, 0, ',', '.') }} VNĐ
                                    </span>
                                </h5>
                               <form method="POST" action="{{ route('user.cart.add.checkoutSelected') }}" id="checkout-all-form">
    @csrf
    <input type="hidden" name="selected_items" id="selected-items-all" value="">
    <button type="submit" class="btn btn-primary" id="checkout-all-btn">
        <i class="bi bi-credit-card"></i> Thanh toán tất cả
    </button>
</form>
                            </div>
                        </div>
                    </div>

         <form method="POST" action="{{ route('user.cart.add.checkoutSelected') }}" id="checkout-selected-form">
    @csrf
    <input type="hidden" name="selected_items" id="selected-items" value="">
    <button type="submit" id="checkout-selected-btn" disabled>
        Thanh toán sản phẩm đã chọn
    </button>
</form>
                @else
                    <div class="alert alert-info text-center py-5">
                        <i class="bi bi-bag-slash" style="font-size: 3rem;"></i>
                        <p class="mt-3">Giỏ hàng trống. <a href="{{ route('user.services') }}">Tiếp tục mua sắm</a></p>
                    </div>
                @endif
            </div>

            <!-- ==========================================
                 TAB 2: LỊCH SỬ DỊCH VỤ ĐÃ MUA
            ============================================ -->
            <div id="historyTab" class="tab-pane fade">
                <h2>Lịch sử dịch vụ đã mua</h2>

                @if($serviceHistory && count($serviceHistory) > 0)
                    <div class="history-list">
                        @foreach($serviceHistory as $h)
                            <div class="card history-item mb-3">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <img src="{{ !empty($h['image']) ? asset('uploads/services/' . $h['image']) : asset('images/default.png') }}"
                                                 alt="{{ $h['name'] }}" style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px;">
                                        </div>
                                        <div class="col">
                                            <h5 class="card-title">{{ $h['name'] }}</h5>
                                            <p class="card-text mb-1">
                                                <strong>Số lượng:</strong> {{ $h['quantity'] }}
                                            </p>
                                            <p class="card-text mb-1">
                                                <strong>Tổng đơn:</strong> <span class="text-success">{{ number_format($h['total_amount'], 0, ',', '.') }} VNĐ</span>
                                            </p>
                                            <p class="card-text mb-2">
                                                <strong>Ngày mua:</strong> {{ $h['created_at']->format('d/m/Y H:i') }}
                                            </p>
                                            <a href="{{ route('user.orderDetail', ['id' => $h['order_id']]) }}" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i> Xem chi tiết
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info text-center py-5">
                        <i class="bi bi-bag-check" style="font-size: 3rem;"></i>
                        <p class="mt-3">Bạn chưa mua dịch vụ nào. <a href="{{ route('user.services') }}">Khám phá dịch vụ</a></p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.container-cart {
    max-width: 900px;
    margin: 20px auto;
    padding: 0 10px;
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const checkboxes = document.querySelectorAll('.select-item');

    // ===== Thanh toán sản phẩm đã chọn =====
    const selectedInput = document.getElementById('selected-items');
    const checkoutBtn = document.getElementById('checkout-selected-btn');

    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            const selected = Array.from(checkboxes)
                .filter(i => i.checked)
                .map(i => i.value);

            selectedInput.value = selected.join(',');
            checkoutBtn.disabled = selected.length === 0;
        });
    });

    // ===== Thanh toán tất cả =====
    const checkoutAllForm = document.getElementById('checkout-all-form');
    const selectedAllInput = document.getElementById('selected-items-all');

    checkoutAllForm.addEventListener('submit', function () {

        const allIds = Array.from(checkboxes)
            .map(i => i.value);

        selectedAllInput.value = allIds.join(',');

    });

});
</script>

@endsection
