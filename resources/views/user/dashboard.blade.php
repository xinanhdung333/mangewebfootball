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

/* Stats carousel — thẻ cuộn ngang trên mobile, dạng lưới trên desktop. */
.ec-stats-section {
    margin-bottom: 16px;
}
.ec-stats-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}
.ec-stats-heading h4 {
    margin: 0;
    color: var(--ec-dark);
    font-size: .95rem;
    font-weight: 800;
    letter-spacing: .35px;
}
.ec-stats-heading span {
    color: var(--ec-muted);
    font-size: .78rem;
}
.ec-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 8px;
}
.ec-stat-card {
    position: relative;
    overflow: hidden;
    background: var(--ec-card);
    border-radius: 12px;
    padding: 18px 16px;
    box-shadow: 0 1px 4px rgba(0,0,0,.08);
    display: flex;
    align-items: center;
    gap: 14px;
}
.ec-stat-card::after {
    position: absolute;
    right: -22px;
    bottom: -30px;
    width: 86px;
    height: 86px;
    border-radius: 50%;
    background: rgba(238, 77, 45, .06);
    content: "";
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
.ec-stats-indicator {
    display: none;
    justify-content: center;
    gap: 5px;
    padding-top: 2px;
}
.ec-stats-indicator span {
    width: 18px;
    height: 5px;
    border-radius: 999px;
    background: #dedede;
}
.ec-stats-indicator span.active {
    width: 30px;
    background: var(--ec-primary);
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

/* ========== SHARED OVERLAY ========== */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    z-index: 99999;
    opacity: 0;
    transition: opacity .3s ease;
    pointer-events: none;
}
.modal-overlay.is-open {
    opacity: 1;
    pointer-events: auto;
}
.modal-overlay.is-closing {
    opacity: 0;
    pointer-events: none;
}

/* ========== VOUCHER MODAL ========== */
.voucher-modal {
    position: relative;
    width: min(440px, 100%);
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.18);
    overflow: hidden;
    transform: translateY(40px) scale(0.93);
    opacity: 0;
    transition: transform .45s cubic-bezier(0.22, 1, 0.36, 1), opacity .35s ease;
}
.modal-overlay.is-open .voucher-modal {
    transform: translateY(0) scale(1);
    opacity: 1;
}
.voucher-modal-header {
    background: #ee4d2d;
    padding: 20px 24px 16px;
    text-align: center;
    position: relative;
}
.voucher-modal-header .close-btn {
    position: absolute;
    top: 10px;
    right: 12px;
    border: none;
    background: rgba(255,255,255,0.2);
    color: #fff;
    font-size: 1.1rem;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    transition: background .2s;
}
.voucher-modal-header .close-btn:hover {
    background: rgba(255,255,255,0.35);
}
.voucher-modal-header-label {
    display: inline-block;
    background: rgba(255,255,255,0.2);
    color: #fff;
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    padding: 4px 10px;
    border-radius: 999px;
    margin-bottom: 10px;
}
.voucher-modal-header h3 {
    margin: 0 0 4px;
    font-size: clamp(1.2rem, 3vw, 1.55rem);
    font-weight: 800;
    color: #fff;
}
.voucher-modal-header p {
    margin: 0;
    color: rgba(255,255,255,.85);
    font-size: .88rem;
}
.voucher-modal-body {
    padding: 20px 24px 24px;
    text-align: center;
}
.voucher-code-box {
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 18px;
    border: 2px dashed #ee4d2d;
    background: #fff8f6;
    border-radius: 10px;
    padding: 12px 18px;
    max-width: 280px;
    font-size: 1.2rem;
    font-weight: 800;
    color: #ee4d2d;
    letter-spacing: .1em;
}
.voucher-copy-btn {
    border: none;
    background: #ee4d2d;
    color: #fff;
    border-radius: 10px;
    padding: 12px 28px;
    font-weight: 700;
    font-size: .95rem;
    cursor: pointer;
    transition: background .2s, transform .15s;
    width: 100%;
    max-width: 280px;
}
.voucher-copy-btn:hover {
    background: #d94426;
    transform: translateY(-1px);
}
.voucher-note {
    margin-top: 12px;
    color: #888;
    font-size: .78rem;
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

/* ========== SHIPPING MODAL ========== */
.shipping-modal {
    position: relative;
    width: min(400px, 100%);
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.18);
    overflow: hidden;
    transform: translateY(40px) scale(0.93);
    opacity: 0;
    transition: transform .45s cubic-bezier(0.22, 1, 0.36, 1), opacity .35s ease;
}
.modal-overlay.is-open .shipping-modal {
    transform: translateY(0) scale(1);
    opacity: 1;
}
.shipping-modal-header {
    background: #1a73e8;
    padding: 24px 24px 20px;
    text-align: center;
    position: relative;
}
.shipping-modal-header .close-btn {
    position: absolute;
    top: 10px;
    right: 12px;
    border: none;
    background: rgba(255,255,255,0.2);
    color: #fff;
    font-size: 1.1rem;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    transition: background .2s;
}
.shipping-modal-header .close-btn:hover {
    background: rgba(255,255,255,0.35);
}
.shipping-icon {
    display: inline-flex;
    width: 60px;
    height: 60px;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
    border-radius: 50%;
    background: rgba(255,255,255,0.18);
    font-size: 1.8rem;
    color: #fff;
}
.shipping-modal-header h3 {
    margin: 0;
    font-size: 1.45rem;
    font-weight: 800;
    color: #fff;
}
.shipping-modal-body {
    padding: 20px 24px 24px;
    text-align: center;
}
.shipping-modal-body p {
    margin: 0 0 18px;
    color: #555;
    font-size: .95rem;
    line-height: 1.7;
}
.shipping-modal-body strong {
    color: #1a73e8;
    font-size: 1.05em;
}
.shipping-modal-cta {
    display: inline-block;
    background: #1a73e8;
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 12px 28px;
    font-weight: 700;
    font-size: .95rem;
    cursor: pointer;
    text-decoration: none;
    transition: background .2s, transform .15s;
    width: 100%;
    max-width: 280px;
    text-align: center;
}
.shipping-modal-cta:hover {
    background: #1558c0;
    color: #fff;
    transform: translateY(-1px);
}

/* ---------- RESPONSIVE ---------- */
@media (max-width: 991px) {
    .ec-sidebar { display: none; }
    .ec-product-grid { grid-template-columns: repeat(3, 1fr); }
    .ec-main { padding-left: 12px; padding-right: 12px; }
}
@media (max-width: 767px) {
    .ec-stats {
        display: flex;
        gap: 10px;
        margin-right: -10px;
        padding: 2px 10px 6px 2px;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        scrollbar-width: none;
    }
    .ec-stats::-webkit-scrollbar { display: none; }
    .ec-stat-card {
        flex: 0 0 min(82vw, 285px);
        min-height: 92px;
        padding: 14px 12px;
        scroll-snap-align: start;
    }
    .ec-stat-info .ec-stat-val { font-size: 1.1rem; }
    .ec-stats-indicator { display: flex; }
    .ec-product-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; }
    .ec-search-bar .inner {
        gap: 8px;
        flex-wrap: wrap;
        padding: 0 10px;
    }
    .ec-brand { font-size: 1rem; }
    .ec-search-wrap {
        order: 3;
        flex: 0 0 100%;
    }
    .ec-search-wrap input { padding: 8px 10px; font-size: .88rem; }
    .ec-search-btn { padding: 0 12px; }
    .ec-flash { flex-direction: column; align-items: flex-start; padding: 12px 14px; gap: 10px; }
    .ec-flash-badge { font-size: .78rem; padding: 4px 10px; }
    .ec-flash-text { font-size: .85rem; min-width: 0; }
    .ec-table { font-size: .78rem; }
    .ec-table thead th,
    .ec-table tbody td { padding: 6px 8px; }
    .ec-orders-block { overflow-x: auto; padding: 12px; }
    .ec-orders-block h5 { font-size: .88rem; }
    .ec-section-title { font-size: .85rem; padding: 10px 0 8px; }
    .ec-btn { padding: 6px 14px; font-size: .82rem; }
    .ec-product-info { padding: 8px; }
    .ec-product-name { font-size: .78rem; }
    .ec-product-price { font-size: .88rem; }

    /* Voucher modal */
    .voucher-modal { width: min(480px, 92vw); padding: 20px 16px 16px; }
    .voucher-title { font-size: clamp(1.2rem, 4vw, 1.6rem); }
    .voucher-code-box { max-width: 220px; font-size: 1rem; padding: 10px 12px; }

    /* Shipping modal */
    .shipping-modal { width: min(400px, 92vw); padding: 22px 18px 18px; }
    .shipping-modal h3 { font-size: 1.35rem; }

    /* Toast */
    .toast-noti { width: 260px; right: 10px; }
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
    padding-left: clamp(8px, 2vw, 32px);
    padding-right: clamp(8px, 2vw, 32px);
}

@media (max-width: 480px) {
    .ec-product-grid { grid-template-columns: repeat(2, 1fr); gap: 6px; }
    .ec-stat-card { gap: 10px; }
    .ec-stat-icon { width: 38px; height: 38px; font-size: 1.1rem; }
    .ec-product-img { aspect-ratio: 1/1; }
}

@media (max-width: 767px) {
    /* MASONRY LAYOUT FOR PRODUCTS */
    .ec-product-grid {
        display: block !important;
        column-count: 2;
        column-gap: 8px;
    }
    .ec-product-card {
        border-radius: 12px !important;
        margin-bottom: 8px;
        break-inside: avoid;
        page-break-inside: avoid;
        display: inline-block;
        width: 100%;
        overflow: hidden;
    }
    .ec-product-img {
        height: auto !important;
        aspect-ratio: auto !important; /* let it stagger */
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }
}
@media (max-width: 480px) {
    .ec-product-grid { column-gap: 6px; }
    .ec-product-card { margin-bottom: 6px; }
}
@media (max-width: 360px) {
    .ec-product-grid { column-count: 1; }
    .toast-noti {
        left: 8px;
        right: 8px;
        width: auto;
    }
    .ec-search-bar { padding: 10px 0; }
    .ec-main { padding-left: 6px; padding-right: 6px; }
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
<div id="voucherOverlay" class="modal-overlay" aria-live="polite">
    <div class="voucher-modal" role="dialog" aria-modal="true" aria-labelledby="voucherTitle">
        <div class="voucher-modal-header">
            <button type="button" class="close-btn" aria-label="Đóng" onclick="closeVoucherOverlay()">×</button>
            <div class="voucher-modal-header-label">🎟 Voucher đặc biệt</div>
            <h3 id="voucherTitle">Ưu đãi dành cho bạn!</h3>
            <p>Giảm <strong style="color:#fff; font-size:1.1em;">{{ number_format($homeVoucher->discount_amount, 0, ',', '.') }}đ</strong>
               cho đơn từ {{ number_format($homeVoucher->min_order_amount, 0, ',', '.') }}đ</p>
        </div>
        <div class="voucher-modal-body">
            <div class="voucher-code-box">
                <span id="voucherCodeText">{{ $homeVoucher->code }}</span>
            </div>
            <button type="button" class="voucher-copy-btn" onclick="copyVoucherCode()">Sao chép mã</button>
            <div class="voucher-note">
                HSD: {{ $homeVoucher->expires_at ? \Carbon\Carbon::parse($homeVoucher->expires_at)->format('d/m/Y') : 'Không giới hạn' }}
            </div>
        </div>
    </div>
</div>
@endif

@if($freeShippingThreshold > 0)
<div id="shippingOverlay" class="modal-overlay" aria-live="polite">
    <div class="shipping-modal" role="dialog" aria-modal="true" aria-labelledby="shippingTitle">
        <div class="shipping-modal-header">
            <button type="button" class="close-btn" aria-label="Đóng" onclick="closeShippingOverlay()">×</button>
            <div class="shipping-icon"><i class="bi bi-truck"></i></div>
            <h3 id="shippingTitle">Free Ship cho bạn!</h3>
        </div>
        <div class="shipping-modal-body">
            <p>Đơn hàng từ <strong>{{ number_format($freeShippingThreshold, 0, ',', '.') }}đ</strong><br>được miễn phí vận chuyển.</p>
            <a href="{{ route('user.services') }}" class="shipping-modal-cta">Mua ngay</a>
        </div>
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

        {{-- Thống kê dạng carousel trên mobile --}}
        <section class="ec-stats-section" aria-labelledby="stats-title">
            <div class="ec-stats-heading">
                <h4 id="stats-title">TỔNG QUAN</h4>
                <span>Vuốt để xem thêm</span>
            </div>
            <div class="ec-stats">
                <article class="ec-stat-card">
                    <div class="ec-stat-icon"><i class="bi bi-bag"></i></div>
                    <div class="ec-stat-info">
                        <h6>Đơn hàng</h6>
                        <p class="ec-stat-val">{{ $stats_total }}</p>
                    </div>
                </article>
                <article class="ec-stat-card">
                    <div class="ec-stat-icon"><i class="bi bi-bag-check"></i></div>
                    <div class="ec-stat-info">
                        <h6>Đã xác nhận</h6>
                        <p class="ec-stat-val">{{ $stats_confirmed }}</p>
                    </div>
                </article>
                <article class="ec-stat-card">
                    <div class="ec-stat-icon"><i class="bi bi-cash-stack"></i></div>
                    <div class="ec-stat-info">
                        <h6>Tổng chi tiêu</h6>
                        <p class="ec-stat-val">{{ formatCurrency($stats_revenue) }}</p>
                    </div>
                </article>
            </div>
            <div class="ec-stats-indicator" aria-hidden="true">
                <span class="active"></span><span></span><span></span>
            </div>
        </section>

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
                        <img loading="lazy" src="{{ asset('uploads/services/' . $service->image) }}" alt="{{ $service->name }}" class="ec-product-img">
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
function _openOverlay(overlay) {
    if (!overlay) return;
    overlay.style.display = 'flex';
    // Force reflow for animation to trigger
    overlay.offsetHeight;
    overlay.classList.add('is-open');
}

function _closeOverlay(overlay, callback) {
    if (!overlay) return;
    overlay.classList.remove('is-open');
    overlay.classList.add('is-closing');
    setTimeout(() => {
        overlay.style.display = 'none';
        overlay.classList.remove('is-closing');
        if (typeof callback === 'function') callback();
    }, 350);
}

function closeVoucherOverlay() {
    const voucherOverlay = document.getElementById('voucherOverlay');
    const shippingOverlay = document.getElementById('shippingOverlay');
    _closeOverlay(voucherOverlay, () => {
        // Sau khi đóng Voucher → mới hiện Free Ship (không chồng)
        if (shippingOverlay) {
            setTimeout(() => _openOverlay(shippingOverlay), 150);
        }
    });
}

function closeShippingOverlay() {
    const overlay = document.getElementById('shippingOverlay');
    _closeOverlay(overlay);
}

function copyVoucherCode() {
    const code = document.getElementById('voucherCodeText')?.innerText?.trim();
    if (!code) return;

    const btn = document.querySelector('.voucher-copy-btn');
    const doCopy = () => {
        if (btn) { btn.textContent = '✓ Đã sao chép!'; setTimeout(() => { btn.textContent = 'Sao chép mã'; }, 1400); }
    };

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(code).then(doCopy).catch(() => {});
        return;
    }
    const temp = document.createElement('textarea');
    temp.value = code;
    document.body.appendChild(temp);
    temp.select();
    document.execCommand('copy');
    document.body.removeChild(temp);
    doCopy();
}

function showToast(id, index = 0) {
    const toast = document.getElementById(id);
    if (!toast) return;
    const isMobile = window.innerWidth <= 576;
    toast.style.top = ((isMobile ? 60 : 80) + index * 90) + 'px';
    toast.classList.add('show');
    setTimeout(() => hideToast(id), 8000);
}
function hideToast(id) {
    const toast = document.getElementById(id);
    if (!toast) return;
    toast.classList.remove('show');
}

document.addEventListener('DOMContentLoaded', function () {
    // Toast notifications
    setTimeout(() => showToast('toast-rule', 0), 1200);
    setTimeout(() => showToast('toast-news', 1), 1700);

    // Stats scroll indicator
    const stats = document.querySelector('.ec-stats');
    const indicators = document.querySelectorAll('.ec-stats-indicator span');
    if (stats && indicators.length) {
        stats.addEventListener('scroll', function () {
            const cardWidth = stats.querySelector('.ec-stat-card')?.getBoundingClientRect().width || 1;
            const activeIndex = Math.min(indicators.length - 1, Math.round(stats.scrollLeft / (cardWidth + 10)));
            indicators.forEach((indicator, index) => {
                indicator.classList.toggle('active', index === activeIndex);
            });
        }, { passive: true });
    }

    // Show Voucher first (if exists), then Free Ship after it's closed
    const voucherOverlay = document.getElementById('voucherOverlay');
    const shippingOverlay = document.getElementById('shippingOverlay');

    if (voucherOverlay) {
        // Có Voucher → hiện voucher, Free Ship sẽ hiện sau khi đóng voucher
        setTimeout(() => _openOverlay(voucherOverlay), 600);
    } else if (shippingOverlay) {
        // Không có Voucher → hiện luôn Free Ship
        setTimeout(() => _openOverlay(shippingOverlay), 600);
    }
});
</script>
@endsection
