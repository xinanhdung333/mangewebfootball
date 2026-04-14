@extends('layouts.app')
@section('content')

<div class="container-fluid py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

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

    <div style="max-width: 1200px; margin: 30px auto; background: #fff; padding: 30px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">

        @if(request()->get('bought') === '1')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill"></i> Mua thành công!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">
            <!-- LEFT: IMAGE -->
            <div class="col-lg-7">
                <div class="position-relative">
                    <img id="mainImg" src="{{ !empty($service->image) ? asset('uploads/services/' . $service->image) : asset('images/default.png') }}" 
                         alt="{{ $service->name }}" class="imageservicedetail">
                </div>
                
                <!-- Thumbnail -->
                <div id="thumbs" class="mt-3 d-flex gap-2">
                    <img class="thumb active" 
                         src="{{ !empty($service->image) ? asset('uploads/services/' . $service->image) : asset('images/default.png') }}" 
                         alt="Thumbnail"
                         style="width: 100px; height: 100px; object-fit: cover; border: 2px solid #007bff; border-radius: 8px; cursor: pointer;">
                </div>
            </div>

            <!-- RIGHT: INFO -->
            <div class="col-lg-5">
                <h2 class="fw-bold mb-2">{{ $service->name }}</h2>
                
                @if($service->brand)
                <p class="text-muted mb-3">Brand: <strong>{{ $service->brand }}</strong></p>
                @endif

                <!-- PRICE -->
                <div class="mb-4">
                    <div class="d-flex align-items-baseline gap-3">
                        <div class="fs-2 fw-bold text-danger">{{ number_format($service->price, 0, ',', '.') }} VNĐ</div>
                        @if($service->old_price && $service->old_price > $service->price)
                            <div class="text-decoration-line-through text-muted">{{ number_format($service->old_price, 0, ',', '.') }} VNĐ</div>
                            <div class="badge bg-danger">
                                -{{ round(($service->old_price - $service->price) / $service->old_price * 100) }}%
                            </div>
                        @endif
                    </div>
                </div>

                @if($service->location)
                <p class="mb-2">
                    <i class="bi bi-geo-alt"></i> <strong>Địa chỉ:</strong> {{ $service->location }}
                </p>
                @endif

                <div class="alert alert-info mb-4">
                    <i class="bi bi-shield-check"></i> 100% Authentic • 30 Days Free Return
                </div>

                <!-- QUANTITY SELECTOR -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Số lượng</label>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-outline-secondary btn-sm" id="decreaseQty" style="width: 40px;">−</button>
                        <span id="qty" class="fw-bold fs-5" style="min-width: 40px; text-align: center;">1</span>
                        <button class="btn btn-outline-secondary btn-sm" id="increaseQty" style="width: 40px;">+</button>
                        <span class="text-muted ms-2">Còn: <strong>{{ $service->quantity }}</strong></span>
                    </div>
                </div>

                <!-- BUTTONS -->
                <div class="row g-2 mb-4">
                    <!-- ADD TO CART -->
                    <div class="col-6">
                        <button id="btnAddToCart" 
                                data-id="{{ $service->id }}"
                                class="btn btn-outline-primary btn-lg w-100">
                            <i class="bi bi-bag-plus"></i> Thêm giỏ
                        </button>
                    </div>

                    <!-- BUY NOW -->
                    <div class="col-6">
<form method="POST" action="{{ route('user.add.checkoutBuyNow') }}">
    @csrf
    <input type="hidden" name="type" value="buy_now">
    <input type="hidden" name="service_id" value="{{ $service->id }}">
<input type="hidden" id="buyNowQty" name="quantity" value="1">
    <button type="submit" class="btn btn-primary btn-lg w-100">
        <i class="bi bi-lightning-fill"></i> Mua ngay
    </button>
</form>
                    </div>
                </div>

                @if($service->shipping_fee)
                <p class="text-muted">
                    <i class="bi bi-truck"></i> Phí vận chuyển: {{ number_format($service->shipping_fee, 0, ',', '.') }} VNĐ
                </p>
                @endif

                <!-- SERVICE DETAILS -->
                @if($service->description)
                <hr>
                <h6 class="fw-bold mt-4">Mô tả chi tiết</h6>
                <p class="text-muted">{{ $service->description }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const qtyEl = document.getElementById('qty');
    const buyNowQtyEl = document.getElementById('buyNowQty');
    const maxQty = {{ $service->quantity }};

    // Thumbnail click
    document.querySelectorAll('.thumb').forEach(thumb => {
        thumb.addEventListener('click', function() {
            document.getElementById('mainImg').src = this.src;
            document.querySelectorAll('.thumb').forEach(t => t.style.borderColor = '#ddd');
            this.style.borderColor = '#007bff';
        });
    });

    // Increase quantity
   document.getElementById('increaseQty').addEventListener('click', function() {

    let qty = parseInt(qtyEl.innerText);

    if (qty < maxQty) {

        qty++;

        qtyEl.innerText = qty;

        if (buyNowQtyEl) buyNowQtyEl.value = qty;

    }

});

    // Decrease quantity
    document.getElementById('decreaseQty').addEventListener('click', function() {
        let qty = parseInt(qtyEl.innerText);
        if (qty > 1) {
            qty--;
            qtyEl.innerText = qty;
            buyNowQtyEl.value = qty;
        }
    });

    // Add to Cart AJAX
    document.getElementById('btnAddToCart').addEventListener('click', async function() {
        const serviceId = this.dataset.id;
        const quantity = parseInt(qtyEl.innerText);

        const formData = new FormData();
        formData.append('service_id', serviceId);
        formData.append('quantity', quantity);

        try {
            const response = await fetch('{{ route("user.cart.add.ajax") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                window.location.href = '{{ route("user.cart") }}';
            } else {
                alert(data.error || 'Không thể thêm vào giỏ');
            }
        } catch (err) {
            console.error('Lỗi:', err);
            alert('Lỗi kết nối server!');
        }
    });

    // Update Buy Now Qty on form submit
    document.querySelector('form').addEventListener('submit', function() {
        document.getElementById('buyNowQty').value = parseInt(qtyEl.innerText);
    });
});
</script>

@endsection
