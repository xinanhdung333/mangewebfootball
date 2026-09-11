@extends('layouts.app')

@section('content')

<div style="max-width:1200px; margin:20px auto; padding:0 10px;">

    <!-- ===== HEADER SHOPEE STYLE ===== -->
    <div class="services-header-wrap">

        <!-- CART -->
        <div style="flex:0 0 auto; position:relative;">
            <a href="{{ route('user.cart') }}" class="cart-icon-link">
                <i class="bi bi-cart-fill"></i>

              
                   <span class="cart-count"
      style="position:absolute; top:-6px; right:-10px; background:#ff0000; color:#fff; font-size:12px; padding:2px 6px; border-radius:50%;">
    {{ $totalItems ?? 0 }}
</span>
              
            </a>
        </div>

        <!-- FLASH SALE -->
        @if($flashPercent > 0)
        <div class="flash-sale-banner">

            <div class="flash-sale-inner">

                <div style="font-weight:bold;">
                    🔥 FLASH SALE
                    <span style="background:rgba(255,255,255,0.2); padding:2px 6px; border-radius:6px; font-size:13px; margin-left:5px;">
                        {{ $flashStart }} - {{ $flashEnd }}
                    </span>
                </div>

                <div style="font-weight:bold;">
                    -{{ round($flashPercent) }}%
                </div>

            </div>

            @if($flashnote)
                <div style="font-size:12px; margin-top:5px; opacity:0.9;">
                    {{ $flashnote }}
                </div>
            @endif

        </div>
        @endif

    </div>

    <!-- ===== SEARCH ===== -->
    <form id="searchForm" class="search-form-wrap">

        <input type="text" name="q" value="{{ request('q') }}"
               placeholder="Tìm theo tên..."
               class="search-input">

        <select id="serviceSort" name="sort" class="search-select">
            <option value="name">Tên</option>
            <option value="priceAsc">Giá thấp → cao</option>
            <option value="priceDesc">Giá cao → thấp</option>
            <option value="rating">Đánh giá</option>
        </select>

        <select name="category_id" class="search-select">
            <option value="">Tất cả danh mục</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>

        <button type="submit" class="search-btn">
            Tìm
        </button>

    </form>
</br>
    <!-- ===== PRODUCTS ===== -->
    <div id="productList" class="product-grid">

        @foreach($services as $service)

        <div class="product-card" data-price="{{ $service->price }}" data-rating="{{ $service->avg_rating ?? 0 }}">

            <a href="{{ route('user.serviceDetail', $service->id) }}">
                <img loading="lazy" src="{{ $service->image ? asset('uploads/services/'.$service->image) : asset('assets/images/default.png') }}"
                     style="width:100%; height:160px; object-fit:cover;">
            </a>

            <div style="padding:8px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                {{ $service->name }}
            </div>
            <div style="padding:0 8px; font-size:12px; color:#666;"><i class="bi bi-tag"></i> {{ $service->category?->name ?? 'Tổng hợp' }}</div>

            @php
                $rating = round((float) ($service->avg_rating ?? 0), 1);
                $reviewCount = (int) ($service->total_reviews ?? 0);
                $ratingStars = (int) round($rating);
            @endphp
            <div class="product-rating" aria-label="{{ $rating }} trên 5 sao, {{ $reviewCount }} lượt đánh giá">
                <span class="rating-stars" aria-hidden="true">
                    @for($star = 1; $star <= 5; $star++)
                        <span class="{{ $star <= $ratingStars ? 'is-filled' : '' }}">★</span>
                    @endfor
                </span>
                @if($reviewCount > 0)
                    <span>{{ number_format($rating, 1, ',', '.') }} ({{ $reviewCount }})</span>
                @else
                    <span>Chưa có đánh giá</span>
                @endif
            </div>

            @if(($service->discount_percent ?? 0) > 0)
                <div style="position:absolute; top:10px; left:10px; background:#ff0000; color:#fff; font-size:12px; padding:3px 6px; border-radius:5px;">
                    -{{ round($service->discount_percent) }}%
                </div>
            @endif

            <div style="padding:5px 8px;">
                @if(($service->discount_percent ?? 0) > 0)
                    <div style="color:#e53935; font-weight:bold;">
                        {{ number_format($service->final_price,0,',','.') }} đ
                    </div>
                    <div style="text-decoration:line-through; font-size:12px; color:#999;">
                        {{ number_format($service->price,0,',','.') }} đ
                    </div>
                @else
                    <div style="font-weight:bold; color:#e53935;">
                        {{ number_format($service->price,0,',','.') }} đ
                    </div>
                @endif
            </div>

            <div style="padding:0 8px 12px; display:flex; align-items:center; justify-content:space-between; gap:10px;">
                <span style="font-size:12px; color:{{ $service->quantity > 0 ? '#6c757d' : '#dc3545' }};">
                    {{ $service->quantity > 0 ? 'Còn: ' . $service->quantity : 'Hết hàng' }}
                </span>

                <button class="btn-add-cart"
                        data-service-id="{{ $service->id }}"
                        data-stock="{{ $service->quantity }}"
                        @if($service->quantity <= 0) disabled @endif>
                    +
                </button>
            </div>

        </div>

        @endforeach

    </div>

    @if($services->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $services->links('pagination::bootstrap-5') }}
        </div>
    @endif

</div>

<style>
.services-header-wrap {
    display:flex; align-items:center; gap:15px; flex-wrap:wrap;
}

.cart-icon-link {
    display:flex; align-items:center; font-size:45px; text-decoration:none; color:#333; position:relative;
}

.flash-sale-banner {
    flex:1; min-width:0; text-align:center; background:linear-gradient(90deg,#ff4d4f,#ff0000); color:#fff; padding:10px 15px; border-radius:10px;
}
.flash-sale-inner {
    display:flex; justify-content:center; align-items:center; gap:10px; flex-wrap:wrap;
}

.search-form-wrap {
    display:flex; gap:10px; flex-wrap:wrap; margin-top:15px;
}
.search-input {
    flex:1; min-width: 200px; padding:10px; border:1px solid #ddd; border-radius:6px;
}
.search-select {
    padding:10px; border:1px solid #ddd; border-radius:6px;
}
.search-btn {
    padding:10px 15px; background:#007bff; color:#fff; border:none; border-radius:6px; white-space:nowrap;
}

.product-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(180px,1fr));
    gap:15px;
}

.product-card{
    position:relative;
    background:#fff;
    border-radius:10px;
    overflow:hidden;
    box-shadow:0 2px 10px rgba(0,0,0,0.08);
    transition:0.2s;
}

.product-card:hover{
    transform:translateY(-3px);
    box-shadow:0 6px 18px rgba(0,0,0,0.15);
}

.product-rating{
    display:flex;
    align-items:center;
    gap:6px;
    padding:6px 8px 2px;
    color:#777;
    font-size:12px;
}

.rating-stars{
    color:#d7d7d7;
    font-size:15px;
    letter-spacing:0;
    line-height:1;
}

.rating-stars .is-filled{
    color:#ffc107;
}

.btn-add-cart{
    width:36px;
    height:36px;
    border-radius:50%;
    border:none;
    background:#28a745;
    color:#fff;
    font-weight:bold;
    flex:0 0 auto;
}

.btn-add-cart:disabled{
    background:#adb5bd;
    cursor:not-allowed;
}

@media (max-width: 768px) {
    .product-grid { grid-template-columns: repeat(3, 1fr); }
}

@media (max-width: 576px) {
    .product-grid { 
        display: block; /* remove grid for masonry */
        column-count: 2;
        column-gap: 10px;
    }
    .product-card {
        border-radius: 12px; /* bo tròn nhẹ */
        margin-bottom: 10px;
        break-inside: avoid;
        page-break-inside: avoid;
        display: inline-block;
        width: 100%;
        overflow: hidden;
    }
    .product-card img {
        height: auto !important;
        aspect-ratio: auto; /* natural height for masonry */
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }
    .product-card > div:nth-child(2) {
        /* Title */
        font-size: 13px;
        white-space: normal !important;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.3;
        padding: 5px 8px !important;
    }
    .cart-icon-link { font-size: 35px; }
    .flash-sale-inner { flex-direction: column; gap: 4px; }
    .search-input { flex-basis: 100%; min-width: 100%; }
    .search-select { flex: 1; }
    .search-btn { width: 100%; }
    .services-header-wrap { gap: 10px; }
    .product-rating { flex-wrap: wrap; padding: 0 8px 4px !important; }
    .product-card > div:nth-child(6) {
        /* Price div */
        padding: 0px 8px 5px !important;
    }
    .product-card > div:last-child {
        /* add to cart button div */
        padding: 0 8px 8px !important;
    }
    .btn-add-cart {
        width: 30px;
        height: 30px;
        font-size: 14px;
        line-height: 1;
    }
}

@media (max-width: 360px) {
    .product-grid { grid-template-columns: 1fr; }
    .search-select { flex-basis: 100%; }
}
</style>
<a href="{{route('user.fields')}}">
<div id="toast-noti" class="toast-noti">
    <i class="bi bi-megaphone-fill"></i>
    <div class="toast-content">
        <strong>Thông báo</strong>
        <p>🔥 Giờ cao điểm {{$rule->start_time}} - {{$rule->end_time}} (giá x{{$rule->multiplier}})</p>
    </div>
    <span class="toast-close" onclick="hideToast()">×</span>
</div>
</a>

<style>
.toast-noti {
    position: fixed;
    bottom: -100px;
    right: 20px;
    width: 280px;
    background: #fff;
    border-left: 5px solid #ee4d2d;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    border-radius: 10px;
    display: flex;
    align-items: center;
    padding: 12px;
    gap: 10px;
    z-index: 9999;
    transition: all 0.4s ease;
    opacity: 0;
}

.toast-noti {
    top: -100px;
    bottom: auto;
    right: 20px;
    transition: all 0.4s ease;
}

.toast-noti.show {
    top: 80px;
    opacity: 1;
}

.toast-content {
    flex: 1;
    font-size: 13px;
}

.toast-content p {
    margin: 0;
    font-size: 12px;
    color: #555;
}

.toast-close {
    cursor: pointer;
    font-size: 18px;
    color: #999;
}
</style>
    <script>
function showToast() {
    const toast = document.getElementById('toast-noti');
    toast.classList.add('show');

    // tự ẩn sau 5s
    setTimeout(() => {
        hideToast();
    }, 10000);
}

function hideToast() {
    const toast = document.getElementById('toast-noti');
    toast.classList.remove('show');
}

// tự chạy khi load trang
document.addEventListener('DOMContentLoaded', function () {
    setTimeout(showToast, 800); // delay nhẹ cho giống Shopee
});
</script>
<script>
// ===== ADD TO CART AJAX =====
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.btn-add-cart').forEach(btn => {

        let isLoading = false;

        btn.addEventListener('click', function () {

            const stock = parseInt(this.dataset.stock || '0', 10);
            if (stock <= 0) {
                alert('Dich vu nay da het hang');
                return;
            }

            if(isLoading) return;
            isLoading = true;

            const serviceId = this.dataset.serviceId;

            fetch("{{ route('user.cart.add.ajax') }}", { // ✅ sửa ở đây
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    service_id: serviceId,
                    quantity: 1
                })
            })
            .then(res => res.json())
            .then(data => {

                console.log(data); // 👈 debug

                if(data.success){

                    const cartCount = document.querySelector('.cart-count');
                    if(cartCount){
                        cartCount.innerText = data.totalItems;
                    }

                }else{
                    alert(data.error);
                }

            })
            .finally(() => {
                isLoading = false;
            });

        });

    });

});

// ===== TOAST CONTROL =====
function showToast() {
    const toast = document.getElementById('toast-noti');
    if(!toast) return;

    toast.classList.add('show');

    setTimeout(() => {
        hideToast();
    }, 3000);
}

function hideToast() {
    const toast = document.getElementById('toast-noti');
    if(!toast) return;

    toast.classList.remove('show');
}
</script>
@endsection
 
