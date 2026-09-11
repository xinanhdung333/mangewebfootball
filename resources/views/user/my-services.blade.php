@extends('layouts.app')
@section('content')

{{-- ========== MOBILE-ONLY SHOPEE STYLE ========== --}}
<style>
@media (max-width: 767px) {
    /* Hide desktop elements on mobile */
    .desktop-only { display: none !important; }

    /* Shopee-style sticky header */
    .mobile-order-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        background: #fff;
        border-bottom: 1px solid #f0f0f0;
        position: sticky;
        top: 56px;
        z-index: 100;
    }
    .mobile-order-header h5 {
        font-size: 1rem;
        font-weight: 700;
        margin: 0;
        color: #222;
    }

    /* Status tabs */
    .mobile-order-tabs {
        display: flex;
        background: #fff;
        border-bottom: 2px solid #f0f0f0;
        overflow-x: auto;
        scrollbar-width: none;
        position: sticky;
        top: calc(56px + 48px);
        z-index: 99;
    }
    .mobile-order-tabs::-webkit-scrollbar { display: none; }
    .mobile-order-tab {
        flex-shrink: 0;
        padding: 11px 16px;
        font-size: .82rem;
        font-weight: 500;
        color: #666;
        cursor: pointer;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        white-space: nowrap;
        transition: color .2s, border-color .2s;
    }
    .mobile-order-tab.active {
        color: #ee4d2d;
        border-bottom-color: #ee4d2d;
        font-weight: 700;
    }

    /* Mobile search */
    .mobile-search-bar {
        padding: 10px 14px;
        background: #f5f5f5;
    }
    .mobile-search-bar input {
        width: 100%;
        border: none;
        border-radius: 20px;
        padding: 8px 16px;
        font-size: .85rem;
        background: #fff;
        box-shadow: 0 1px 4px rgba(0,0,0,.07);
        outline: none;
    }

    /* Stats pills */
    .mobile-stats-row {
        display: flex;
        gap: 8px;
        padding: 10px 12px 4px;
    }
    .mobile-stat-pill {
        flex: 1;
        background: #fff;
        border-radius: 10px;
        padding: 10px 6px;
        text-align: center;
        box-shadow: 0 1px 4px rgba(0,0,0,.07);
        font-size: .75rem;
        color: #666;
    }
    .mobile-stat-pill .stat-num {
        font-size: 1rem;
        font-weight: 700;
        color: #ee4d2d;
        display: block;
    }

    /* Order cards - Shopee style */
    #service-table-area .row.g-4 {
        display: flex !important;
        flex-direction: column !important;
        gap: 10px !important;
        padding: 8px 12px !important;
    }
    #service-table-area .row.g-4 > [class*="col-"] {
        width: 100% !important;
        padding: 0 !important;
    }
    #service-table-area .card {
        border-radius: 12px !important;
        border: none !important;
        box-shadow: 0 2px 8px rgba(0,0,0,.07) !important;
        overflow: hidden;
    }
    /* Card image - place on top row */
    #service-table-area .row.g-0 {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        align-items: stretch !important;
    }
    #service-table-area .col-sm-4 {
        width: 80px !important;
        min-width: 80px !important;
        flex: none !important;
    }
    #service-table-area .col-sm-4 .order-image-stack {
        width: 64px !important;
        height: 64px !important;
        margin: 12px auto !important;
    }
    #service-table-area .col-sm-4 .order-stack-image {
        width: 64px !important;
        height: 64px !important;
    }
    #service-table-area .col-sm-8 {
        flex: 1 !important;
        width: auto !important;
    }
    #service-table-area .card-body {
        padding: 12px 12px 10px 8px !important;
    }
    #service-table-area .card-title {
        font-size: .88rem !important;
    }
    #service-table-area .text-muted {
        font-size: .78rem !important;
    }
    #service-table-area .btn-sm {
        font-size: .78rem !important;
        padding: 5px 12px !important;
        border-radius: 20px !important;
    }
    /* Stats row on mobile */
    #service-table-area .row.g-3.mb-4 {
        display: none !important;
    }
    /* Pagination stays */
    #service-table-area .service-pagination {
        margin: 8px 12px 16px !important;
    }

    /* Wrap py-4 container */
    #service-table-area {
        padding-top: 4px !important;
    }
}
@media (min-width: 768px) {
    /* Hide mobile-only elements on desktop */
    .mobile-only { display: none !important; }
}
</style>

{{-- MOBILE-ONLY: header + tabs + search --}}
<div class="mobile-only">
    <div class="mobile-order-header">
        <a href="{{ url()->previous() }}" class="text-dark" style="font-size:1.1rem; line-height:1;"><i class="bi bi-arrow-left"></i></a>
        <h5><i class="bi bi-bag-check me-1"></i> Đơn hàng đã mua</h5>
    </div>
    <div class="mobile-order-tabs">
        <div class="mobile-order-tab active" data-status="">Tất cả</div>
        <div class="mobile-order-tab" data-status="pending">Chờ xác nhận</div>
        <div class="mobile-order-tab" data-status="confirmed">Đã xác nhận</div>
        <div class="mobile-order-tab" data-status="completed">Hoàn thành</div>
        <div class="mobile-order-tab" data-status="cancelled">Đã hủy</div>
    </div>
    <div class="mobile-search-bar">
        <input type="text" id="mobile-search" placeholder="🔍 Tìm kiếm đơn hàng..." value="{{ $keyword ?? '' }}">
    </div>
</div>

{{-- DESKTOP layout (hidden on mobile) --}}
<div class="container-fluid py-4">
    <div class="row mb-4 desktop-only">
        <div class="col-md-12">
            <h1><i class="bi bi-bag-check"></i> Dịch vụ đã đặt</h1>
        </div>
    </div>
    <div class="row mb-3 desktop-only">

        <div class="col-md-4">
            <input type="text"
                   id="search-input"
                   class="form-control"
                   placeholder="Tìm dịch vụ..."
                   value="{{ $keyword ?? request('keyword', '') }}">
        </div>

        <div class="col-md-3">
            <select id="status-filter" class="form-select">
                <option value="">Tất cả trạng thái</option>
                <option value="pending" {{ request('status', $filterStatus ?? '') === 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                <option value="confirmed" {{ request('status', $filterStatus ?? '') === 'confirmed' ? 'selected' : '' }}>Đã thanh toán</option>
                <option value="cancelled" {{ request('status', $filterStatus ?? '') === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
            </select>
        </div>

    </div>

    <div id="service-table-area" class="py-4">
        @isset($myServices)
            @include('user.service-table')
        @endisset
    </div>
</div>

<script>
// ===== Mobile tabs =====
document.querySelectorAll('.mobile-order-tab').forEach(tab => {
    tab.addEventListener('click', function () {
        document.querySelectorAll('.mobile-order-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('status-filter').value = this.dataset.status;
        loadServices();
    });
});

// ===== Sync mobile search to desktop input =====
const mobileSearch = document.getElementById('mobile-search');
if (mobileSearch) {
    mobileSearch.addEventListener('keyup', () => {
        document.getElementById('search-input').value = mobileSearch.value;
        loadServices();
    });
}

function loadServices(url = null) {
    const keyword = document.getElementById('search-input').value;
    const status = document.getElementById('status-filter').value;

    if (!url) {
        url = `{{ route('user.services.search') }}?keyword=${encodeURIComponent(keyword)}&status=${encodeURIComponent(status)}&partial=1`;
    }

    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.text())
    .then(data => {
        if (data.includes('<html') || data.includes('<!DOCTYPE html')) {
            const doc = new DOMParser().parseFromString(data, 'text/html');
            const partial = doc.querySelector('#service-table-area');
            if (partial) {
                document.getElementById('service-table-area').innerHTML = partial.innerHTML;
                return;
            }
        }
        document.getElementById('service-table-area').innerHTML = data;
    });
}

document.addEventListener('click', function(e) {
    const link = e.target.closest('#service-table-area .service-pagination a');
    if (link) {
        e.preventDefault();
        const url = new URL(link.href);
        url.searchParams.set('keyword', document.getElementById('search-input').value);
        url.searchParams.set('status', document.getElementById('status-filter').value);
        url.searchParams.set('partial', '1');
        loadServices(url.toString());
    }
});

document.getElementById('search-input').addEventListener('keyup', () => loadServices());
document.getElementById('status-filter').addEventListener('change', () => loadServices());
</script>
@endsection
