@extends('layouts.app')
@section('content')

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-1">
                <i class="bi bi-heart"></i> Danh sách yêu thích
            </h1>
            <p class="text-muted small">Quản lý các sản phẩm bạn đã yêu thích</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="wishlist-icon-box bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-heart-fill fs-4"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-muted small">Tổng sản phẩm yêu thích</h6>
                        <div class="fs-4 fw-bold" id="wishlistTotalCount">0</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="wishlist-icon-box bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-cart-check fs-4"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-muted small">Hành động nhanh</h6>
                        <button class="btn btn-sm btn-outline-danger mt-1" id="clearAllWishlist">
                            <i class="bi bi-trash"></i> Xóa tất cả
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Wishlist Items -->
    <div id="wishlistContainer">
        <!-- Empty state -->
        <div id="wishlistEmpty" class="card shadow-sm border-0" style="display:none;">
            <div class="card-body text-center py-5">
                <div class="wishlist-empty-icon mb-3">
                    <i class="bi bi-heart text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
                <h5 class="mb-2">Chưa có sản phẩm yêu thích</h5>
                <p class="text-muted mb-3">Hãy thêm sản phẩm vào danh sách yêu thích để theo dõi!</p>
                <a href="{{ route('user.services') }}" class="btn btn-primary">
                    <i class="bi bi-bag me-1"></i> Khám phá sản phẩm
                </a>
            </div>
        </div>

        <!-- Items grid -->
        <div id="wishlistGrid" class="row g-4" style="display:none;"></div>
    </div>
</div>

<style>
.wishlist-icon-box {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.wishlist-card {
    border: none;
    border-radius: 16px;
    overflow: hidden;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.wishlist-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(0,0,0,0.15) !important;
}

.wishlist-card .card-img-top {
    height: 220px;
    object-fit: cover;
    transition: transform 0.3s ease;
}
.wishlist-card:hover .card-img-top {
    transform: scale(1.03);
}

.wishlist-card .card-body {
    padding: 16px;
}

.wishlist-card .wishlist-title {
    font-weight: 700;
    font-size: 1rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    min-height: 48px;
}

.wishlist-card .wishlist-price {
    font-size: 1.15rem;
    font-weight: 700;
    color: #ee4d2d;
}

.wishlist-card .wishlist-original-price {
    font-size: 0.85rem;
    color: #999;
    text-decoration: line-through;
}

.wishlist-card .wishlist-date {
    font-size: 0.78rem;
    color: #999;
}

.wishlist-remove-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(255,255,255,0.9);
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: #dc3545;
    cursor: pointer;
    transition: all 0.2s ease;
    backdrop-filter: blur(4px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    z-index: 2;
}
.wishlist-remove-btn:hover {
    background: #dc3545;
    color: #fff;
    transform: scale(1.1);
}

.wishlist-card .wishlist-img-wrapper {
    position: relative;
    overflow: hidden;
}

.wishlist-discount-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    z-index: 2;
}

@media (max-width: 575.98px) {
    .wishlist-card .card-img-top {
        height: 160px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const CACHE_KEY = 'sportsHubWishlist';
    const gridEl = document.getElementById('wishlistGrid');
    const emptyEl = document.getElementById('wishlistEmpty');
    const totalCountEl = document.getElementById('wishlistTotalCount');
    const clearAllBtn = document.getElementById('clearAllWishlist');

    function readWishlist() {
        try {
            const cached = JSON.parse(localStorage.getItem(CACHE_KEY) || '[]');
            return Array.isArray(cached) ? cached : [];
        } catch (e) {
            return [];
        }
    }

    function writeWishlist(items) {
        localStorage.setItem(CACHE_KEY, JSON.stringify(items));
        updateNavBadge(items.length);
    }

    function updateNavBadge(count) {
        const badge = document.getElementById('navWishlistBadge');
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? '' : 'none';
        }
    }

    function formatPrice(price) {
        return new Intl.NumberFormat('vi-VN').format(price);
    }

    function formatDate(isoString) {
        if (!isoString) return '';
        const d = new Date(isoString);
        return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function renderWishlist() {
        const items = readWishlist();
        totalCountEl.textContent = items.length;

        if (items.length === 0) {
            gridEl.style.display = 'none';
            emptyEl.style.display = '';
            return;
        }

        emptyEl.style.display = 'none';
        gridEl.style.display = '';
        gridEl.innerHTML = '';

        items.forEach(function(item, index) {
            const hasDiscount = item.original_price && item.price < item.original_price;
            const discountPercent = hasDiscount
                ? Math.round((1 - item.price / item.original_price) * 100)
                : 0;

            const col = document.createElement('div');
            col.className = 'col-sm-6 col-md-4 col-lg-3';
            col.innerHTML = `
                <div class="card wishlist-card shadow-sm h-100">
                    <div class="wishlist-img-wrapper">
                        <img src="${item.image || '/images/default.png'}"
                             class="card-img-top"
                             alt="${item.name || 'Sản phẩm'}"
                             onerror="this.src='/images/default.png'">
                        <button class="wishlist-remove-btn" data-index="${index}" title="Xóa khỏi yêu thích">
                            <i class="bi bi-x-lg"></i>
                        </button>
                        ${hasDiscount ? `<span class="badge bg-danger wishlist-discount-badge">-${discountPercent}%</span>` : ''}
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="wishlist-title mb-2">${item.name || 'Sản phẩm'}</div>
                        <div class="mb-2">
                            <span class="wishlist-price">${formatPrice(item.price)} VNĐ</span>
                            ${hasDiscount ? `<br><span class="wishlist-original-price">${formatPrice(item.original_price)} VNĐ</span>` : ''}
                        </div>
                        <div class="wishlist-date mb-3">
                            <i class="bi bi-clock"></i> Đã thêm: ${formatDate(item.added_at)}
                        </div>
                        <div class="mt-auto d-grid gap-2">
                            <a href="${item.url || '#'}" class="btn btn-primary btn-sm">
                                <i class="bi bi-eye me-1"></i> Xem chi tiết
                            </a>
                            <button class="btn btn-outline-danger btn-sm wishlist-remove-btn-bottom" data-index="${index}">
                                <i class="bi bi-heart-fill me-1"></i> Bỏ yêu thích
                            </button>
                        </div>
                    </div>
                </div>
            `;
            gridEl.appendChild(col);
        });

        // Bind remove buttons
        document.querySelectorAll('.wishlist-remove-btn, .wishlist-remove-btn-bottom').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const idx = parseInt(this.dataset.index);
                const current = readWishlist();
                current.splice(idx, 1);
                writeWishlist(current);
                renderWishlist();
            });
        });
    }

    // Clear all
    clearAllBtn.addEventListener('click', function() {
        if (confirm('Bạn có chắc muốn xóa tất cả sản phẩm yêu thích?')) {
            writeWishlist([]);
            renderWishlist();
        }
    });

    renderWishlist();
});
</script>

@endsection
