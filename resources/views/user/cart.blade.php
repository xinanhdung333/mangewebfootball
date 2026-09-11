@extends('layouts.app')
@section('content')

<style>
.cart-product-img { width: 120px; height: 120px; object-fit: cover; border-radius: 8px; }
.select-item { width:20px; height:20px; cursor:pointer; }
@media (max-width: 576px) {
    /* Layout hàng ngang compact: checkbox + ảnh nhỏ + thông tin */
    .cart-item-row { flex-wrap: nowrap; align-items: flex-start !important; gap: 0; }
    .cart-checkbox-col { flex: 0 0 auto; padding: 4px 4px 0 0; }
    .cart-img-col { flex: 0 0 auto; }
    .cart-product-img { width: 70px; height: 70px; object-fit: cover; border-radius: 6px; }
    .cart-info-col { flex: 1 1 auto; padding-left: 8px !important; }
    .cart-info-col h5 { font-size: 13px; margin-bottom: 2px; }
    .cart-info-col .card-text { font-size: 12px; }
    .quantity-control { display: flex; gap: 4px; }
    .qty-btn { padding: 2px 7px; font-size: 12px; }
    .item-total { font-size: 12px; }
    /* Thu gọn card padding */
    .cart-item .card-body { padding: 8px 10px; }
}
</style>
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
                                >
                                <div class="card-body">
                                    <div class="row align-items-center cart-item-row">
                                        <div class="col-auto cart-checkbox-col">
                                            <input type="checkbox" class="form-check-input select-item" name="selected_items[]" value="{{ $item['id'] }}">
                                        </div>
                                        <div class="col-auto cart-img-col">
                                            <img src="{{ !empty($item['image']) ? asset('uploads/services/' . $item['image']) : asset('images/default.png') }}" 
                                                 alt="{{ $item['name'] }}" class="cart-product-img">
                                        </div>
                                        <div class="col cart-info-col">
                                            <h5 class="card-title">{{ $item['name'] }}</h5>
                                          <div class="card-text">
@if(($item['discount_percent'] ?? 0) > 0)

    <div style="color:#e53935; font-weight:bold;">
        {{ number_format($item['price'],0,',','.') }} đ
    </div>

    <div style="text-decoration:line-through; font-size:12px; color:#999;">
        {{ number_format($item['original_price'] ?? $item['price'],0,',','.') }} đ
    </div>

@else

    <div style="font-weight:bold; color:#e53935;">
        {{ number_format($item['price'],0,',','.') }} đ
    </div>

@endif
</div>
                                            <div class="quantity-control mb-2 mt-2">
                                                <button class="btn btn-sm btn-outline-secondary qty-btn decrease" 
                                                        data-item-id="{{ $item['id'] }}">-</button>
                                                <span class="qty mx-2">{{ $item['quantity'] }}</span>
                                                <button class="btn btn-sm btn-outline-secondary qty-btn increase" 
                                                        data-item-id="{{ $item['id'] }}">+</button>
                                            </div>

                                            <p class="card-text mb-2">
                                                Tổng: <span class="item-total fw-bold text-primary">
                                                    {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }} VNĐ
                                                </span>
                                            </p>

                                            <form method="POST" action="{{ route('user.removeFromCart') }}" class="remove-cart-form" style="display: inline;">
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
    <button type="submit" id="checkout-selected-btn" disabled class="btn btn-primary">
       <i class="bi bi-credit-card"></i>Thanh toán sản phẩm đã chọn
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
    /* Trên tablet: giảm ảnh xuống 90px */
    .cart-product-img { width: 90px; height: 90px; }
    .cart-item .card-body { padding: 10px 12px; }
}
</style>

<script>
   document.addEventListener('DOMContentLoaded', function () {
    function recalculateCartTotal() {
        let total = 0;
        document.querySelectorAll('.cart-item').forEach(row => {
            const qty = parseInt(row.querySelector('.qty')?.textContent || '0');
            const price = parseFloat(row.dataset.price || '0');
            total += qty * price;
        });

        const totalEl = document.getElementById('cart-total');
        if (totalEl) {
            totalEl.textContent = total.toLocaleString('vi-VN') + ' VND';
        }
    }

    // ======================
    // QTY UPDATE
    // ======================
    document.querySelectorAll('.qty-btn').forEach(btn => {

        btn.addEventListener('click', function () {

            const id = this.dataset.itemId;
            const isIncrease = this.classList.contains('increase');
            const row = this.closest('.cart-item');
            const qtyEl = row.querySelector('.qty');
            const totalEl = row.querySelector('.item-total');

            let qty = parseInt(qtyEl.textContent);

            qty = isIncrease ? qty + 1 : Math.max(1, qty - 1);

            qtyEl.textContent = qty;

            const price = parseFloat(row.dataset.price);
            totalEl.textContent = (price * qty).toLocaleString('vi-VN') + ' VNĐ';
            recalculateCartTotal();

            fetch("{{ route('user.cart.updateItem') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    cart_item_id: id,
                    quantity: qty
                })
            });
        });

    });

    // ======================
    // CHECKBOX SELECT
    // ======================
    const checkboxes = document.querySelectorAll('.select-item');

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

    // ======================
    // CHECKOUT ALL
    // ======================
    const checkoutAllForm = document.getElementById('checkout-all-form');

    checkoutAllForm.addEventListener('submit', function () {

        const allIds = Array.from(checkboxes).map(i => i.value);

        if (allIds.length === 0) {
            alert('Giỏ hàng trống');
            return;
        }

        document.getElementById('selected-items-all').value = allIds.join(',');
    });

    document.querySelectorAll('.remove-cart-form').forEach(form => {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            if (!confirm('Ban muon xoa san pham nay khoi gio hang?')) {
                return;
            }

            const row = this.closest('.cart-item');
            const button = this.querySelector('button[type="submit"]');
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = 'Dang xoa...';

            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: new FormData(this),
                });
                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(data.message || 'Khong the xoa san pham');
                }

                row.remove();
                recalculateCartTotal();
            } catch (error) {
                alert(error.message);
                button.disabled = false;
                button.innerHTML = originalText;
            }
        });
    });
});
</script>

@endsection

