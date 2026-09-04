@extends('layouts.app')
@section('content')
<style>
/* ===== E-COMMERCE DASHBOARD — SHOPEE MALL STYLE ===== */
:root {
    --ec-primary: #EE4D2D;
    --ec-primary-hover: #D73211;
    --ec-bg: #F5F5F5;
    --ec-card: #FFFFFF;
    --ec-dark: #222222;
    --ec-muted: #757575;
    --ec-border: #e8e8e8;
    --ec-success: #2DC258;
    --ec-warning: #FFBF00;
    --ec-danger: #D0021B;
}

.ec-search-bar,
.ec-search-bar *,
.ec-main,
.ec-main * {
    box-sizing: border-box;
}

/* ---------- SEARCH BAR ---------- */
.ec-search-bar {
    background: var(--ec-primary);
    padding: 14px 0;
}
.ec-search-bar .inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 16px;
    display: flex;
    align-items: center;
    gap: 16px;
}
.ec-brand {
    color: #fff;
    font-size: 1.45rem;
    font-weight: 800;
    letter-spacing: .3px;
    text-decoration: none;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 6px;
}
.ec-brand:hover { color: #fff; }
.ec-search-wrap {
    flex: 1;
    min-width: 0;
    display: flex;
    background: #fff;
    border-radius: 6px;
    overflow: hidden;
}
.ec-search-wrap input {
    flex: 1;
    min-width: 0;
    border: none;
    outline: none;
    padding: 10px 14px;
    font-size: .95rem;
    color: var(--ec-dark);
}
.ec-search-wrap input::placeholder { color: #bbb; }
.ec-search-btn {
    background: var(--ec-primary-hover);
    border: none;
    color: #fff;
    padding: 0 18px;
    font-size: 1.1rem;
    cursor: pointer;
    transition: background .2s;
}
.ec-search-btn:hover { background: #b8260e; }
.ec-cart-link {
    color: #fff;
    font-size: 1.5rem;
    position: relative;
    text-decoration: none;
}
.ec-cart-link:hover { color: #ffe0d6; }

/* ---------- MAIN LAYOUT ---------- */
.ec-main {
    max-width: 1200px;
    margin: 0 auto;
    padding: 16px;
    display: flex;
    gap: 16px;
    align-items: flex-start;
}
.ec-sidebar {
    width: 240px;
    flex-shrink: 0;
    background: var(--ec-card);
    border-radius: 4px;
    box-shadow: 0 1px 4px rgba(0,0,0,.08);
    position: sticky;
    top: 80px;
}
.ec-sidebar-header {
    padding: 14px 16px 10px;
    font-size: .85rem;
    font-weight: 700;
    color: var(--ec-dark);
    text-transform: uppercase;
    letter-spacing: .5px;
    display: flex;
    align-items: center;
    gap: 8px;
    border-bottom: 1px solid var(--ec-border);
}
.ec-cat-list {
    list-style: none;
    margin: 0;
    padding: 0;
}
.ec-cat-item {
    display: flex;
    align-items: center;
    padding: 11px 16px;
    gap: 10px;
    color: var(--ec-dark);
    text-decoration: none;
    font-size: .9rem;
    font-weight: 500;
    border-left: 3px solid transparent;
    transition: all .15s;
    cursor: pointer;
}
.ec-cat-item:hover,
.ec-cat-item.active {
    color: var(--ec-primary);
    border-left-color: var(--ec-primary);
    background: #fef6f4;
}
.ec-cat-item i.cat-icon {
    font-size: 1.15rem;
    width: 24px;
    text-align: center;
    color: var(--ec-muted);
}
.ec-cat-item:hover i.cat-icon,
.ec-cat-item.active i.cat-icon {
    color: var(--ec-primary);
}
.ec-cat-item .cat-name { flex: 1; }
.ec-cat-item .cat-arrow {
    font-size: .75rem;
    color: #ccc;
}
.ec-cat-item + .ec-cat-item {
    border-top: 1px solid #f5f5f5;
}
.ec-sidebar-footer {
    padding: 12px 16px;
    border-top: 1px solid var(--ec-border);
}
.ec-sidebar-footer a {
    display: block;
    text-align: center;
    color: var(--ec-primary);
    font-weight: 600;
    font-size: .88rem;
    text-decoration: none;
    padding: 8px;
    border-radius: 6px;
    transition: background .15s;
}
.ec-sidebar-footer a:hover {
    background: #fef6f4;
}

/* ---------- RIGHT CONTENT ---------- */
.ec-content {
    flex: 1;
    min-width: 0;
}

/* Stats row */
.ec-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 16px;
}
.ec-stat-card {
    background: var(--ec-card);
    border-radius: 4px;
    padding: 18px 16px;
    box-shadow: 0 1px 4px rgba(0,0,0,.08);
    display: flex;
    align-items: center;
    gap: 14px;
}
.ec-stat-icon {
    width: 46px;
    height: 46px;
    border-radius: 8px;
    background: #fef0ec;
    color: var(--ec-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
}
.ec-stat-info h6 {
    margin: 0;
    font-size: .8rem;
    color: var(--ec-muted);
    font-weight: 500;
}
.ec-stat-info .ec-stat-val {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--ec-dark);
    margin: 0;
    line-height: 1.3;
}

/* Flash sale banner */
.ec-flash {
    background: var(--ec-card);
    border-radius: 4px;
    box-shadow: 0 1px 4px rgba(0,0,0,.08);
    padding: 16px 20px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}
.ec-flash-badge {
    background: var(--ec-primary);
    color: #fff;
    padding: 5px 12px;
    border-radius: 4px;
    font-weight: 700;
    font-size: .85rem;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 5px;
}
.ec-flash-text {
    flex: 1;
    font-size: .9rem;
    color: var(--ec-dark);
    min-width: 180px;
}
.ec-flash-text strong { color: var(--ec-primary); }

/* Flat CTA button */
.ec-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 18px;
    border-radius: 6px;
    font-size: .88rem;
    font-weight: 600;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: background .15s, color .15s;
}
.ec-btn-primary {
    background: var(--ec-primary);
    color: #fff;
}
.ec-btn-primary:hover {
    background: var(--ec-primary-hover);
    color: #fff;
}
.ec-btn-outline {
    background: transparent;
    color: var(--ec-primary);
    border: 1px solid var(--ec-primary);
}
.ec-btn-outline:hover {
    background: #fef6f4;
    color: var(--ec-primary-hover);
}
.ec-btn-ghost {
    background: transparent;
    color: var(--ec-primary);
    padding: 8px 12px;
}
.ec-btn-ghost:hover {
    background: #fef6f4;
}

/* Section header */
.ec-section-title {
    font-size: .95rem;
    font-weight: 700;
    color: var(--ec-dark);
    text-transform: uppercase;
    letter-spacing: .4px;
    padding: 14px 0 10px;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.ec-section-title::before {
    content: '';
    width: 4px;
    height: 18px;
    background: var(--ec-primary);
    border-radius: 2px;
}

/* Product grid */
.ec-product-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-bottom: 16px;
}
.ec-category-group {
    margin-bottom: 18px;
}
.ec-category-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin: 18px 0 10px;
}
.ec-product-card {
    background: var(--ec-card);
    border: 1px solid var(--ec-border);
    border-radius: 4px;
    overflow: hidden;
    text-decoration: none;
    color: var(--ec-dark);
    transition: transform .15s, box-shadow .15s;
}
.ec-product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,.1);
    color: var(--ec-dark);
}
.ec-product-img {
    width: 100%;
    aspect-ratio: 1/1;
    object-fit: cover;
    display: block;
    background: #fafafa;
}
.ec-product-info {
    padding: 10px;
}
.ec-product-name {
    font-size: .82rem;
    font-weight: 500;
    color: var(--ec-dark);
    margin: 0 0 6px;
    line-height: 1.35;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.ec-product-price {
    color: var(--ec-primary);
    font-weight: 700;
    font-size: .95rem;
    margin: 0;
}
.ec-product-meta {
    font-size: .72rem;
    color: var(--ec-muted);
    margin-top: 2px;
}

/* Orders table */
.ec-orders-block {
    background: var(--ec-card);
    border-radius: 4px;
    box-shadow: 0 1px 4px rgba(0,0,0,.08);
    padding: 16px;
    margin-bottom: 16px;
}
.ec-orders-block h5 {
    font-size: .95rem;
    font-weight: 700;
    margin: 0 0 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--ec-dark);
}
.ec-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .88rem;
}
.ec-table thead th {
    background: #f8f8f8;
    color: var(--ec-muted);
    font-weight: 600;
    font-size: .8rem;
    text-transform: uppercase;
    letter-spacing: .3px;
    padding: 10px 12px;
    border-bottom: 1px solid var(--ec-border);
    text-align: left;
}
.ec-table tbody td {
    padding: 10px 12px;
    border-bottom: 1px solid #f5f5f5;
    color: var(--ec-dark);
}
.ec-table tbody tr:hover {
    background: #fefefe;
}
.ec-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 4px;
    font-size: .75rem;
    font-weight: 600;
}
.ec-badge-success { background: #e8f8ee; color: var(--ec-success); }
.ec-badge-warning { background: #fff8e0; color: #b38600; }
.ec-badge-danger  { background: #fde8e8; color: var(--ec-danger); }
.ec-orders-footer {
    padding-top: 12px;
    text-align: right;
}

.voucher-overlay {
    position: fixed;
    inset: 0;
    background: rgba(17, 17, 17, 0.62);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    z-index: 99999;
    animation: overlayFadeIn .35s ease both;
}
.voucher-overlay.is-closing,
.shipping-overlay.is-closing {
    opacity: 0;
    transition: opacity .25s ease;
}
.voucher-modal {
    position: relative;
    width: min(480px, 100%);
    background: linear-gradient(135deg, #fff9f0 0%, #fff 100%);
    border: 1px solid rgba(255, 138, 61, 0.3);
    border-radius: 22px;
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.22);
    padding: 28px 24px 20px;
    text-align: center;
    animation: voucherPopIn .55s cubic-bezier(.22, 1, .36, 1) both;
}
@keyframes overlayFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes voucherPopIn {
    0% { opacity: 0; transform: translateY(32px) scale(.9) rotate(-1deg); }
    65% { opacity: 1; transform: translateY(-5px) scale(1.02) rotate(.3deg); }
    100% { opacity: 1; transform: translateY(0) scale(1) rotate(0); }
}
.voucher-modal .close-btn {
    position: absolute;
    top: 12px;
    right: 14px;
    border: none;
    background: transparent;
    color: #7d7d7d;
    font-size: 1.5rem;
    line-height: 1;
    cursor: pointer;
}
.voucher-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 999px;
    background: #fff3dd;
    color: #b96310;
    font-size: .75rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
}
.voucher-title {
    margin: 14px 0 10px;
    font-size: clamp(1.4rem, 3vw, 2rem);
    font-weight: 800;
    color: #222;
}
.voucher-subtitle {
    margin: 0 0 18px;
    color: #555;
    line-height: 1.6;
    font-size: .95rem;
}
.voucher-code-box {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin: 18px auto 16px;
    border: 2px dashed #ff8a3d;
    background: #fff;
    border-radius: 12px;
    padding: 12px 14px;
    max-width: 260px;
    font-size: 1.15rem;
    font-weight: 800;
    color: #d9670a;
    letter-spacing: .08em;
}
.voucher-copy-btn {
    border: none;
    background: linear-gradient(135deg, #ff8a3d, #ff5a3c);
    color: #fff;
    border-radius: 999px;
    padding: 11px 18px;
    font-weight: 700;
    cursor: pointer;
    transition: opacity .2s;
}
.voucher-copy-btn:hover { opacity: .95; }
.voucher-note {
    margin-top: 12px;
    color: #666;
    font-size: .8rem;
}
    
/* Toast */
.toast-noti {
    position: fixed;
    top: -100px;
    right: 20px;
    width: 300px;
    background: #fff;
    border-left: 4px solid var(--ec-primary);
    box-shadow: 0 4px 16px rgba(0,0,0,.12);
    border-radius: 6px;
    display: flex;
    align-items: center;
    padding: 12px;
    gap: 10px;
    z-index: 9999;
    transition: opacity .35s ease, transform .55s cubic-bezier(.22, 1, .36, 1);
    transform: translate3d(0, -24px, 0) scale(.96);
    opacity: 0;
}
.toast-noti.show {
    top: 80px;
    opacity: 1;
    transform: translate3d(0, 0, 0) scale(1);
}
.toast-content { flex: 1; font-size: .82rem; }
.toast-content strong { display: block; margin-bottom: 2px; }
.toast-content p { margin: 0; font-size: .78rem; color: var(--ec-muted); }
.toast-close { cursor: pointer; font-size: 1.1rem; color: #bbb; padding: 4px; }
.toast-close:hover { color: var(--ec-dark); }

.shipping-overlay {
    position: fixed;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    z-index: 100000;
    pointer-events: none;
}
.shipping-modal {
    pointer-events: auto;
    position: relative;
    width: min(440px, 100%);
    padding: 28px 24px 24px;
    text-align: center;
    color: #fff;
    border-radius: 24px;
    background: linear-gradient(135deg, #ff5a3c, #ff8a3d 58%, #ffc857);
    box-shadow: 0 24px 70px rgba(225, 74, 35, .38);
    animation: shippingPopIn .65s cubic-bezier(.22, 1, .36, 1) both;
}
@keyframes shippingPopIn {
    0% { opacity: 0; transform: translateY(42px) scale(.78) rotate(2deg); }
    70% { opacity: 1; transform: translateY(-7px) scale(1.03) rotate(-.3deg); }
    100% { opacity: 1; transform: translateY(0) scale(1) rotate(0); }
}
.shipping-modal .close-btn { color: rgba(255,255,255,.8); }
.shipping-icon {
    display: inline-flex;
    width: 66px;
    height: 66px;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    border-radius: 50%;
    background: rgba(255,255,255,.2);
    font-size: 2rem;
}
.shipping-modal h3 { margin: 0 0 10px; font-size: 1.65rem; font-weight: 800; }
.shipping-modal p { margin: 0; line-height: 1.6; color: rgba(255,255,255,.95); }
.shipping-modal strong { color: #fff; font-size: 1.12em; }

/* ---------- RESPONSIVE ---------- */
@media (max-width: 991px) {
    .ec-sidebar { display: none; }
    .ec-product-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 767px) {
    .ec-stats { grid-template-columns: 1fr; }
    .ec-product-grid { grid-template-columns: repeat(2, 1fr); }
    .ec-search-bar .inner {
        gap: 10px;
        flex-wrap: wrap;
    }
    .ec-brand { font-size: 1.1rem; }
    .ec-search-wrap {
        order: 3;
        flex: 0 0 100%;
    }
    .ec-search-btn { padding: 0 14px; }
    .ec-flash { flex-direction: column; align-items: flex-start; }
    .ec-table { font-size: .8rem; }
    .ec-table thead th,
    .ec-table tbody td { padding: 8px; }
    .ec-orders-block { overflow-x: auto; }
}

/* Fit within parent layout — stretch wider */
.ec-search-bar {
    width: 100vw;
    margin: -1rem calc(50% - 50vw) 0;
}
.ec-main {
    width: 100vw;
    max-width: none;
    margin: 0 calc(50% - 50vw);
    padding-left: clamp(12px, 2vw, 32px);
    padding-right: clamp(12px, 2vw, 32px);
}

@media (max-width: 360px) {
    .ec-product-grid { grid-template-columns: 1fr; }
    .toast-noti {
        left: 12px;
        right: 12px;
        width: auto;
    }
}
</style>

{{-- ========== SEARCH BAR ========== --}}
<div class="ec-search-bar">
    <div class="inner">
        <a href="{{ route('user.dashboard') }}" class="ec-brand">
            <i class="bi bi-shop"></i> SportsHub Mall
        </a>
        <form class="ec-search-wrap" action="{{ route('user.services') }}" method="GET">
            <input type="text" name="search" placeholder="Tìm kiếm sản phẩm...">
            <button type="submit" class="ec-search-btn"><i class="bi bi-search"></i></button>
        </form>
        <a href="{{ route('cart.index') }}" class="ec-cart-link"><i class="bi bi-cart3"></i></a>
    </div>
</div>

@if($homeVoucher)
    <div id="voucherOverlay" class="voucher-overlay" aria-live="polite">
        <div class="voucher-modal" role="dialog" aria-modal="true" aria-labelledby="voucherTitle">
            <button type="button" class="close-btn" aria-label="Đóng" onclick="closeVoucherOverlay()">×</button>
            <div class="voucher-badge">Voucher hot</div>
            <h3 id="voucherTitle" class="voucher-title">Ưu đãi dành cho bạn</h3>
            <p class="voucher-subtitle">
                Giảm ngay <strong>{{ number_format($homeVoucher->discount_amount, 0, ',', '.') }}đ</strong>
                cho đơn hàng từ <strong>{{ number_format($homeVoucher->min_order_amount, 0, ',', '.') }}đ</strong>.
            </p>
            <div class="voucher-code-box">
                <span id="voucherCodeText">{{ $homeVoucher->code }}</span>
            </div>
            <button type="button" class="voucher-copy-btn" onclick="copyVoucherCode()">Sao chép mã</button>
            <div class="voucher-note">
                HSD: {{ $homeVoucher->expires_at ? \Carbon\Carbon::parse($homeVoucher->expires_at)->format('d/m/Y H:i') : 'Không giới hạn' }}
            </div>
        </div>
    </div>
@endif

@if($freeShippingThreshold > 0)
    <div id="shippingOverlay" class="shipping-overlay" aria-live="polite">
        <div class="shipping-modal" role="dialog" aria-modal="true" aria-labelledby="shippingTitle">
            <button type="button" class="close-btn" aria-label="Đóng" onclick="closeShippingOverlay()">×</button>
            <div class="shipping-icon"><i class="bi bi-truck"></i></div>
            <h3 id="shippingTitle">Free ship cho bạn!</h3>
            <p>Đơn hàng từ <strong>{{ number_format($freeShippingThreshold, 0, ',', '.') }}đ</strong><br>được miễn phí vận chuyển.</p>
        </div>
    </div>
@endif

{{-- ========== MAIN LAYOUT ========== --}}
<div class="ec-main">

    {{-- ===== LEFT SIDEBAR — DANH MỤC ===== --}}
    <aside class="ec-sidebar">
        <div class="ec-sidebar-header">
            <i class="bi bi-grid-3x3-gap"></i> DANH MỤC
        </div>
        <ul class="ec-cat-list">
            @forelse($categories as $cat)
                <a href="{{ route('user.services', ['category' => $cat->id]) }}" class="ec-cat-item">
                    <i class="bi {{ $cat->icon ?? 'bi-tag' }} cat-icon"></i>
                    <span class="cat-name">{{ $cat->name }}</span>
                    <i class="bi bi-chevron-right cat-arrow"></i>
                </a>
            @empty
                <li class="ec-cat-item" style="justify-content:center; color:var(--ec-muted);">Chưa có danh mục</li>
            @endforelse
        </ul>
        <div class="ec-sidebar-footer">
            <a href="{{ route('user.services') }}">Xem tất cả sản phẩm</a>
        </div>
    </aside>

    {{-- ===== RIGHT CONTENT ===== --}}
    <div class="ec-content">

        {{-- Stats --}}
        <div class="ec-stats">
            <div class="ec-stat-card">
                <div class="ec-stat-icon"><i class="bi bi-bag"></i></div>
                <div class="ec-stat-info">
                    <h6>Đơn hàng</h6>
                    <p class="ec-stat-val">{{ $stats_total }}</p>
                </div>
            </div>
            <div class="ec-stat-card">
                <div class="ec-stat-icon"><i class="bi bi-bag-check"></i></div>
                <div class="ec-stat-info">
                    <h6>Đã xác nhận</h6>
                    <p class="ec-stat-val">{{ $stats_confirmed }}</p>
                </div>
            </div>
            <div class="ec-stat-card">
                <div class="ec-stat-icon"><i class="bi bi-cash-stack"></i></div>
                <div class="ec-stat-info">
                    <h6>Tổng chi tiêu</h6>
                    <p class="ec-stat-val">{{ formatCurrency($stats_revenue) }}</p>
                </div>
            </div>
        </div>

        {{-- Flash Sale --}}
        <div class="ec-flash">
            <span class="ec-flash-badge"><i class="bi bi-lightning-fill"></i> FLASH SALE</span>
            <div class="ec-flash-text">
                Khung giờ vàng <strong>{{ optional($rule)->start_time }} — {{ optional($rule)->end_time }}</strong>
                @if(optional($ruleService)->note)
                    · {{ optional($ruleService)->note }}
                @endif
            </div>
            <a href="{{ route('user.services') }}" class="ec-btn ec-btn-primary">Mua ngay</a>
        </div>

        {{-- GỢI Ý CHO BẠN --}}
        <h4 class="ec-section-title">GỢI Ý CHO BẠN</h4>
        <div class="ec-product-grid">
            @forelse($featuredServices as $service)
                <a href="{{ route('user.serviceDetail', $service->id) }}" class="ec-product-card">
                    @if($service->image)
                        <img src="{{ asset('uploads/services/' . $service->image) }}" alt="{{ $service->name }}" class="ec-product-img">
                    @else
                        <div class="ec-product-img" style="display:flex;align-items:center;justify-content:center;color:#ccc;font-size:2rem;">
                            <i class="bi bi-image"></i>
                        </div>
                    @endif
                    <div class="ec-product-info">
                        <p class="ec-product-name">{{ $service->name }}</p>
                        <p class="ec-product-price">{{ formatCurrency($service->price) }}</p>
                        @if($service->quantity > 0)
                            <p class="ec-product-meta">Còn {{ $service->quantity }} sản phẩm</p>
                        @else
                            <p class="ec-product-meta" style="color:var(--ec-danger);">Hết hàng</p>
                        @endif
                    </div>
                </a>
            @empty
                <div style="grid-column:1/-1; text-align:center; padding:32px; color:var(--ec-muted);">
                    Chưa có sản phẩm nào. <a href="{{ route('user.services') }}" style="color:var(--ec-primary);">Xem cửa hàng</a>
                </div>
            @endforelse
        </div>

        @foreach($categories as $category)
            @if($category->services->isNotEmpty())
                <div class="ec-category-group">
                    <div class="ec-category-header">
                        <h4 class="ec-section-title" style="padding:0; margin:0;">{{ $category->name }}</h4>
                        <a href="{{ route('user.services', ['category' => $category->id]) }}" class="ec-btn ec-btn-ghost" style="padding:6px 12px; font-size:.8rem;">Xem tất cả</a>
                    </div>
                    <div class="ec-product-grid">
                        @foreach($category->services as $service)
                            <a href="{{ route('user.serviceDetail', $service->id) }}" class="ec-product-card">
                                @if($service->image)
                                    <img src="{{ asset('uploads/services/' . $service->image) }}" alt="{{ $service->name }}" class="ec-product-img">
                                @else
                                    <div class="ec-product-img" style="display:flex;align-items:center;justify-content:center;color:#ccc;font-size:2rem;">
                                        <i class="bi bi-image"></i>
                                    </div>
                                @endif
                                <div class="ec-product-info">
                                    <p class="ec-product-name">{{ $service->name }}</p>
                                    <p class="ec-product-price">{{ formatCurrency($service->price) }}</p>
                                    @if($service->quantity > 0)
                                        <p class="ec-product-meta">Còn {{ $service->quantity }} sản phẩm</p>
                                    @else
                                        <p class="ec-product-meta" style="color:var(--ec-danger);">Hết hàng</p>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach

        {{-- ĐƠN HÀNG GẦN ĐÂY --}}
        <div class="ec-orders-block">
            <h5><i class="bi bi-receipt"></i> Đơn hàng gần đây</h5>
            @if($bookings && count($bookings) > 0)
                <table class="ec-table">
                    <thead>
                        <tr>
                            <th>Sân</th>
                            <th>Ngày</th>
                            <th>Giờ</th>
                            <th>Giá</th>
                            <th>Trạng thái</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(array_slice($bookings->toArray(), 0, 6) as $b)
                        <tr>
                            <td>{{ $b['field']['name'] ?? 'N/A' }}</td>
                            <td>{{ date('d/m/Y', strtotime($b['booking_date'])) }}</td>
                            <td>{{ $b['start_time'] }} – {{ $b['end_time'] }}</td>
                            <td style="font-weight:600;">{{ formatCurrency($b['total_price']) }}</td>
                            <td>
                                @if($b['status'] == 'confirmed')
                                    <span class="ec-badge ec-badge-success">Xác nhận</span>
                                @elseif($b['status'] == 'pending')
                                    <span class="ec-badge ec-badge-warning">Chờ</span>
                                @else
                                    <span class="ec-badge ec-badge-danger">Hủy</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('user.bookingdetail', ['id' => $b['id']]) }}" class="ec-btn ec-btn-ghost" style="padding:4px 10px; font-size:.82rem;">Xem</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div style="padding:24px; text-align:center; color:var(--ec-muted);">
                    Chưa có đơn hàng nào. <a href="{{ route('user.fields') }}" style="color:var(--ec-primary);">Đặt ngay</a>
                </div>
            @endif
            <div class="ec-orders-footer">
                <a href="{{ route('user.myBookings') }}" class="ec-btn ec-btn-outline">Xem tất cả đơn hàng</a>
            </div>
        </div>

    </div>
</div>

{{-- ========== TOAST NOTIFICATIONS ========== --}}
<a href="{{ route('user.fields') }}">
    <div id="toast-rule" class="toast-noti">
        <i class="bi bi-megaphone-fill" style="color:var(--ec-primary); font-size:1.2rem;"></i>
        <div class="toast-content">
            <strong>Thông báo</strong>
            <p>🔥 Giờ cao điểm {{ optional($rule)->start_time }} - {{ optional($rule)->end_time }} (giá x{{ optional($rule)->multiplier }})</p>
        </div>
        <span class="toast-close" onclick="event.preventDefault(); hideToast('toast-rule')">×</span>
    </div>
</a>
<a href="{{ route('user.services') }}">
    <div id="toast-news" class="toast-noti">
        <i class="bi bi-info-circle-fill" style="color:var(--ec-primary); font-size:1.2rem;"></i>
        <div class="toast-content">
            <strong>Ưu đãi mới</strong>
            <p>🔥 {{ optional($ruleService)->note }}</p>
        </div>
        <span class="toast-close" onclick="event.preventDefault(); hideToast('toast-news')">×</span>
    </div>
</a>

<script>
function showToast(id, index = 0) {
    const toast = document.getElementById(id);
    if (!toast) return;
    toast.style.top = (80 + index * 80) + "px";
    toast.classList.add('show');
    setTimeout(() => hideToast(id), 10000);
}
function hideToast(id) {
    const toast = document.getElementById(id);
    if (!toast) return;
    toast.classList.remove('show');
    toast.style.top = "-100px";
}
function closeVoucherOverlay() {
    const overlay = document.getElementById('voucherOverlay');
    if (overlay) {
        overlay.classList.add('is-closing');
        setTimeout(() => { overlay.style.display = 'none'; }, 260);
    }
}
function closeShippingOverlay() {
    const overlay = document.getElementById('shippingOverlay');
    if (overlay) {
        overlay.classList.add('is-closing');
        setTimeout(() => { overlay.style.display = 'none'; }, 260);
    }
}
function copyVoucherCode() {
    const code = document.getElementById('voucherCodeText')?.innerText;
    if (!code) return;

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(code).then(() => {
            const btn = document.querySelector('.voucher-copy-btn');
            if (!btn) return;
            const originalText = btn.textContent;
            btn.textContent = 'Đã sao chép';
            setTimeout(() => { btn.textContent = originalText; }, 1200);
        }).catch(() => {});
        return;
    }

    const temp = document.createElement('textarea');
    temp.value = code;
    document.body.appendChild(temp);
    temp.select();
    document.execCommand('copy');
    document.body.removeChild(temp);
}
document.addEventListener('DOMContentLoaded', function () {
    setTimeout(() => showToast('toast-rule', 0), 800);
    setTimeout(() => showToast('toast-news', 1), 1200);

    const voucherOverlay = document.getElementById('voucherOverlay');
    if (voucherOverlay) {
        voucherOverlay.style.opacity = '1';
    }

    const shippingOverlay = document.getElementById('shippingOverlay');
    if (shippingOverlay) {
        shippingOverlay.style.opacity = '1';
    }
});
</script>
@endsection
