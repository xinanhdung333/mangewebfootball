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
                <div class="mb-3"><span class="badge bg-info text-dark"><i class="bi bi-tag"></i> {{ $service->category?->name ?? 'Tổng hợp' }}</span></div>
                @if($discountPercent > 0)
    <div class="mb-2">
        <span class="badge bg-danger">
            🔥 Flash Sale
        </span>
    </div>
    @else
    <div class="mb-2">
        <span class="badge bg-danger">
            ⏰ Hết khung giờ giảm giá
</span>
    </div>
        @endif
                @if($service->brand)
                <p class="text-muted mb-3">Brand: <strong>{{ $service->brand }}</strong></p>
                @endif

                <!-- PRICE -->
              <div class="mb-4">

    @if($discountPercent > 0)
        <!-- GIÁ SAU GIẢM -->
        <div class="d-flex align-items-center gap-3">

            <div class="fs-2 fw-bold text-danger">
                {{ number_format($finalPrice, 0, ',', '.') }} VNĐ
            </div>

            <!-- % giảm -->
            <div class="badge bg-danger fs-6 px-3 py-2">
                -{{ round($discountPercent) }}%
            </div>

        </div>

        <!-- GIÁ GỐC -->
        <div class="text-muted mt-1">
            <span class="text-decoration-line-through fs-5">
                {{ number_format($originalPrice, 0, ',', '.') }} VNĐ
            </span>

            <span class="ms-2 text-success fw-bold">
                Tiết kiệm {{ number_format($originalPrice - $finalPrice, 0, ',', '.') }}đ
            </span>
        </div>

    @else
        <!-- KHÔNG GIẢM -->
        <div class="fs-2 fw-bold text-dark">
            {{ number_format($service->price, 0, ',', '.') }} VNĐ
        </div>
    @endif

</div>

                @if($service->location)
                <p class="mb-2">
                    <i class="bi bi-geo-alt"></i> <strong>Địa chỉ:</strong> {{ $service->location }}
                </p>
                @endif

                <div class="alert alert-info mb-4">
                    <i class="bi bi-shield-check"></i> 100% Authentic • 30 Days Free Return
                </div>

                <div class="mb-3">
                    <span class="text-muted">Tồn kho:</span>
                    @if($service->quantity > 0)
                        <strong class="text-success">{{ $service->quantity }}</strong>
                    @else
                        <strong class="text-danger">Hết hàng</strong>
                    @endif
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

                    <div class="col-12">
                        <button id="btnWishlist"
                                type="button"
                                class="btn btn-outline-danger btn-lg w-100"
                                aria-pressed="false"
                                data-id="{{ $service->id }}">
                            <i class="bi bi-heart"></i> <span>Y&ecirc;u th&iacute;ch</span>
                        </button>
                        <div id="wishlistMessage" class="wishlist-message" role="status" aria-live="polite"></div>
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

    <section class="service-reviews" aria-labelledby="reviews-title">
        <div class="reviews-summary">
            <div>
                <h3 id="reviews-title" class="mb-1">Đánh giá sản phẩm</h3>
                <div class="review-score">
                    {{ number_format((float) ($service->avg_rating ?? 0), 1, ',', '.') }}
                    <span class="review-stars">★★★★★</span>
                </div>
                <div class="text-muted">{{ $service->feedbacks->count() }} lượt đánh giá</div>
            </div>F
        </div>

        @auth
            @if($reviewOrderItemId)
                <form method="POST" action="{{ route('user.sendFeedback') }}" class="review-form">
                    @csrf
                    <input type="hidden" name="feedback_type" value="service">
                    <input type="hidden" name="item_id" value="{{ $reviewOrderItemId }}">
                    <label class="form-label fw-bold">Gửi đánh giá của bạn</label>
                    <div class="rating-input mb-2">
                        @for($star = 5; $star >= 1; $star--)
                            <input type="radio" id="rating-{{ $star }}" name="rating" value="{{ $star }}" required>
                            <label for="rating-{{ $star }}" title="{{ $star }} sao">★</label>
                        @endfor
                    </div>
                    <textarea name="message" class="form-control mb-2" rows="3" maxlength="2000" required placeholder="Chia sẻ trải nghiệm của bạn..."></textarea>
                    <button class="btn btn-primary" type="submit">Gửi đánh giá</button>
                </form>
            @endif
        @else
            <p class="text-muted">Đăng nhập và mua sản phẩm để gửi đánh giá.</p>
        @endauth

        <div class="review-list">
            @forelse($service->feedbacks->sortByDesc('created_at') as $feedback)
                <article class="review-item">
                    <strong>{{ $feedback->user?->name ?? 'Khách hàng' }}</strong>
                    <div class="review-stars small">{{ str_repeat('★', (int) $feedback->rating) }}{{ str_repeat('☆', 5 - (int) $feedback->rating) }}</div>
                    <p class="mb-1">{{ $feedback->message }}</p>
                    <small class="text-muted">{{ optional($feedback->created_at)->format('d/m/Y H:i') }}</small>
                    @if($feedback->admin_reply)
                        <div class="admin-reply">
                            <strong>SportsHub trả lời:</strong>
                            <p class="mb-0">{{ $feedback->admin_reply }}</p>
                        </div>
                    @endif
                </article>
            @empty
                <p class="text-muted">Sản phẩm chưa có bình luận nào.</p>
            @endforelse
        </div>
    </section>
</div>
<style>
    .badge.bg-danger {
    background: linear-gradient(45deg, #ff4d4f, #ff0000);
    box-shadow: 0 2px 6px rgba(255,0,0,0.3);
}

.text-decoration-line-through {
    opacity: 0.7;
}

.service-reviews {
    max-width: 1200px;
    margin: 24px auto 0;
    padding: 24px 30px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.review-score {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 28px;
    font-weight: 700;
}

.review-stars { color: #ffc107; letter-spacing: 0; }
.review-form { max-width: 600px; margin: 20px 0; padding: 16px; background: #f8f9fa; border-radius: 10px; }
.rating-input { display: flex; flex-direction: row-reverse; justify-content: flex-end; }
.rating-input input { position: absolute; opacity: 0; }
.rating-input label { color: #ccc; cursor: pointer; font-size: 25px; }
.rating-input label:hover, .rating-input label:hover ~ label, .rating-input input:checked ~ label { color: #ffc107; }
.review-item { padding: 16px 0; border-top: 1px solid #eee; }
.admin-reply { margin-top: 10px; padding: 10px 12px; background: #fff4ef; border-left: 3px solid #f4512a; border-radius: 6px; }
.wishlist-message {
    min-height: 20px;
    margin-top: 8px;
    color: #6c757d;
    font-size: 14px;
}
#btnWishlist.active {
    background: #dc3545;
    border-color: #dc3545;
    color: #fff;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const qtyEl = document.getElementById('qty');
    const buyNowQtyEl = document.getElementById('buyNowQty');
    const maxQty = {{ $service->quantity }};
    const addToCartBtn = document.getElementById('btnAddToCart');
    const increaseBtn = document.getElementById('increaseQty');
    const decreaseBtn = document.getElementById('decreaseQty');
    const wishlistBtn = document.getElementById('btnWishlist');
    const wishlistMessage = document.getElementById('wishlistMessage');
    const wishlistCacheKey = 'sportsHubWishlist';
    const wishlistItem = {
        id: {{ $service->id }},
        name: @json($service->name),
        price: {{ (float) $finalPrice }},
        original_price: {{ (float) $originalPrice }},
        image: @json(!empty($service->image) ? asset('uploads/services/' . $service->image) : asset('images/default.png')),
        url: @json(route('user.serviceDetail', $service->id)),
        added_at: null
    };

    function readWishlistCache() {
        try {
            const cached = JSON.parse(localStorage.getItem(wishlistCacheKey) || '[]');
            return Array.isArray(cached) ? cached : [];
        } catch (error) {
            return [];
        }
    }

    function writeWishlistCache(items) {
        localStorage.setItem(wishlistCacheKey, JSON.stringify(items));
    }

    function serviceInWishlist(items) {
        return items.some(item => String(item.id) === String(wishlistItem.id));
    }

    function updateWishlistButton() {
        if (!wishlistBtn) return;

        const active = serviceInWishlist(readWishlistCache());
        wishlistBtn.classList.toggle('active', active);
        wishlistBtn.setAttribute('aria-pressed', active ? 'true' : 'false');
        wishlistBtn.querySelector('i').className = active ? 'bi bi-heart-fill' : 'bi bi-heart';
        wishlistBtn.querySelector('span').innerHTML = active ? '&Dstrok;&atilde; y&ecirc;u th&iacute;ch' : 'Y&ecirc;u th&iacute;ch';
    }

    function showWishlistMessage(message) {
        if (!wishlistMessage) return;

        wishlistMessage.textContent = message;
        clearTimeout(showWishlistMessage.timer);
        showWishlistMessage.timer = setTimeout(() => {
            wishlistMessage.textContent = '';
        }, 2200);
    }

    updateWishlistButton();

    if (wishlistBtn) {
        wishlistBtn.addEventListener('click', function() {
            const items = readWishlistCache();
            const exists = serviceInWishlist(items);

            if (exists) {
                writeWishlistCache(items.filter(item => String(item.id) !== String(wishlistItem.id)));
                showWishlistMessage('Da xoa khoi wishlist.');
            } else {
                writeWishlistCache([
                    ...items,
                    {
                        ...wishlistItem,
                        added_at: new Date().toISOString()
                    }
                ]);
                showWishlistMessage('Da luu vao wishlist.');
            }

            updateWishlistButton();
        });
    }

    if (maxQty <= 0) {
        if (addToCartBtn) addToCartBtn.disabled = true;
        if (increaseBtn) increaseBtn.disabled = true;
        if (decreaseBtn) decreaseBtn.disabled = true;
        if (buyNowQtyEl) buyNowQtyEl.value = 0;
        if (qtyEl) qtyEl.innerText = '0';
    }

    const buyNowForm = document.querySelector('form[action="{{ route('user.add.checkoutBuyNow') }}"]');
    if (buyNowForm) {
        buyNowForm.addEventListener('submit', function(e) {
            if (maxQty <= 0) {
                e.preventDefault();
                alert('Dich vu nay da het hang');
                return;
            }
        }, true);
    }

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
