@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-grid"></i> Danh sách sân bóng</h1>
<!-- Thanh tìm kiếm + nút lịch đặt -->
        <div class="d-flex align-items-center flex-wrap mt-3 gap-2">
            <input type="text" id="searchField" class="form-control" placeholder="Tìm kiếm sân..." 
                   style="background: rgba(255,255,255,0.8); border:1px solid #ccc; flex: 1 1 200px;">
<select id="priceSort" class="form-select" style="flex: 1 1 150px; min-width:150px;">
    <option value="name">Sắp xếp theo tên</option>
    <option value="priceAsc">Giá thấp đến cao</option>
    <option value="priceDesc">Giá cao đến thấp</option>
    <option value="rating">Đánh giá cao nhất</option>
</select>

<select id="distanceSort" class="form-select" style="flex: 1 1 150px; min-width:150px;">
    <option value="nearest">Gần nhất</option>
    <option value="farthest">Xa nhất</option>
</select>
            <a href="{{  route('user.fieldSchedule') }}" class="btn btn-info w-100-sm" style="flex: 1 1 200px;">
                <i class="bi bi-calendar-check"></i> KHUNG GIỜ ĐÃ ĐƯỢC ĐẶT
            </a>
        </div>
    </div>
</div>

<div class="row" id="fieldList">
    
    @if($fields && count($fields) > 0)
        @foreach($fields as $field)
            <div class="col-6 col-sm-6 col-lg-4 mb-3 px-1 field-item">
                <div class="card field-card-mobile">
                    <img loading="lazy" src="{{ !empty($field->image) ? asset('uploads/fields/' . $field->image) : asset('assets/images/banner.jpg') }}" 
                        class="fields" alt="{{ htmlspecialchars($field->name) }}">
                    <div class="card-body p-2 p-sm-3">
                        <h5 class="card-title mobile-title">{{ htmlspecialchars($field->name) }}</h5>
                        
                        @php
                            $desc = htmlspecialchars($field->description);
                            $shortDesc = strlen($desc) > 120 ? substr($desc, 0, 120) . "..." : $desc;
                        @endphp

                        <p class="card-text description-short" id="desc-short-{{ $field->id }}">
                            {{ $shortDesc }}
                        </p>

                        <p class="card-text d-none" id="desc-full-{{ $field->id }}">
                            {{ nl2br($desc) }}
                        </p>

                        @if(strlen($desc) > 120)
                            <span class="show-more-btn" onclick="toggleDesc({{ $field->id }})">Xem thêm</span>
                        @endif

                        <p class="text-muted mb-2">
                            <i class="bi bi-geo-alt"></i> {{ htmlspecialchars($field->location) }}
                        </p>
                        <p class="text-success fw-bold mb-3">
                            {{ formatCurrency($field->price_per_hour) }}/giờ
                        </p>

                        <!-- Rating -->
                        @php
                            $avg = $field->avg_rating ? round($field->avg_rating, 1) : 0;
                            $total = $field->total_reviews ?? 0;
                        @endphp

                        <div class="mb-2">
                            @for ($i = 1; $i <= 5; $i++)
                                <span style="color: gold; font-size: 18px;">
                                    @if($i <= $avg)★@else☆@endif
                                </span>
                            @endfor

                            <span class="text-muted">({{ $avg }} / 5, {{ $total }} đánh giá)</span>
                        </div>

                        <a href="{{ route('user.bookingcreate', ['field_id' => $field->id]) }}" class="btn btn-primary w-100">
                            <i class="bi bi-calendar-plus"></i> Đặt sân
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="col-md-12">
            <div class="alert alert-info">Hiện tại không có sân nào.</div>
        </div>
    @endif
</div>

<script>
    function toggleDesc(id) {
        const short = document.getElementById("desc-short-" + id);
        const full = document.getElementById("desc-full-" + id);
        const btn = event.target;

        if (short.classList.contains("d-none")) {
            short.classList.remove("d-none");
            full.classList.add("d-none");
            btn.textContent = "Xem thêm";
        } else {
            short.classList.add("d-none");
            full.classList.remove("d-none");
            btn.textContent = "Ẩn";
        }
    }

    // Tìm kiếm sân
    document.getElementById('searchField').addEventListener('keyup', function() {
        const query = this.value.toLowerCase();
        const items = document.querySelectorAll('.field-item');
        items.forEach(item => {
            const name = item.querySelector('.card-title').textContent.toLowerCase();
            const location = item.querySelector('.text-muted').textContent.toLowerCase();
            item.style.display = name.includes(query) || location.includes(query) ? 'block' : 'none';
        });
    });
    

document.getElementById('priceSort').addEventListener('change', function () {

    let type = this.value;

    let container = document.getElementById('fieldList');

    let items = Array.from(document.querySelectorAll('.field-item'));

    items.sort(function (a, b) {

        let nameA = a.querySelector('.card-title').innerText.toLowerCase();
        let nameB = b.querySelector('.card-title').innerText.toLowerCase();

        let priceA = parseInt(a.querySelector('.text-success').innerText.replace(/\D/g, ""));
        let priceB = parseInt(b.querySelector('.text-success').innerText.replace(/\D/g, ""));
let ratingA = parseFloat(
    a.querySelector('.mb-2 .text-muted').innerText.match(/\((.*?)\s\/\s5/)[1]
);

let ratingB = parseFloat(
    b.querySelector('.mb-2 .text-muted').innerText.match(/\((.*?)\s\/\s5/)[1]
);

        if (type === "name") return nameA.localeCompare(nameB);

        if (type === "priceAsc") return priceA - priceB;

        if (type === "priceDesc") return priceB - priceA;

        if (type === "rating") return ratingB - ratingA;

    });

    items.forEach(item => container.appendChild(item));

});
document.getElementById('distanceSort').addEventListener('change', function () {

    let type = this.value;

    let container = document.getElementById('fieldList');

    let items = Array.from(document.querySelectorAll('.field-item'));

    items.sort(function (a, b) {

        let locationA = a.querySelector('.text-muted').innerText;
        let locationB = b.querySelector('.text-muted').innerText;

        if (type === "nearest") return locationA.localeCompare(locationB);

        if (type === "farthest") return locationB.localeCompare(locationA);

    });

    items.forEach(item => container.appendChild(item));

});

const urlParams = new URLSearchParams(window.location.search);
const sort = urlParams.get('sort');

if(sort){
    document.getElementById('sort').value = sort;
}
</script>

<style>
@media (max-width: 576px) {
    /* Masonry layout (staggered) */
    .row#fieldList {
        display: block;
        column-count: 2;
        column-gap: 10px;
        margin-left: 0;
        margin-right: 0;
    }
    .field-item {
        width: 100% !important;
        max-width: 100%;
        padding-left: 0;
        padding-right: 0;
        margin-bottom: 10px !important;
        break-inside: avoid;
        page-break-inside: avoid;
        display: inline-block;
    }
    .field-card-mobile {
        margin-bottom: 0 !important;
        border-radius: 12px; /* bo tròn nhẹ */
        border: none;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .field-card-mobile .fields {
        height: auto !important;
        aspect-ratio: auto; /* Để ảnh cao thấp tự nhiên */
        object-fit: cover;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }
    .mobile-title {
        font-size: 13px !important;
        font-weight: normal;
        margin-bottom: 4px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: normal;
        line-height: 1.3;
    }
    .description-short, .show-more-btn {
        display: none !important; /* Hide descriptions to save space */
    }
    .field-item .text-muted.mb-2 {
        font-size: 11px;
        margin-bottom: 4px !important;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .field-item .text-success {
        font-size: 14px;
        color: #ee4d2d !important; /* Shopee orange for price */
        margin-bottom: 4px !important;
    }
    .field-item .mb-2 span {
        font-size: 10px !important; /* rating text */
    }
    .field-item .mb-2 span[style*="font-size"] {
        font-size: 12px !important; /* rating stars */
    }
    .field-item .btn-primary {
        font-size: 12px;
        padding: 6px;
        width: 100%;
        margin-top: 5px;
        border-radius: 6px;
    }
}
</style>
 <a href ="{{route('user.fields')}}">
    <div id="toast-rule" class="toast-noti">
    <i class="bi bi-megaphone-fill"></i>
    <div class="toast-content">
        <strong>Thông báo</strong>
        <p>🔥 Giờ cao điểm {{optional($rule)->start_time}} - {{ optional($rule)->end_time }} (giá x{{ optional($rule)->multiplier }})</p>
    </div>
    <span class="toast-close" onclick="hideToast('toast-rule')">×</span>
</div>
</a>
<a href ="{{route('user.services')}}">
    <div id="toast-news" class="toast-noti">
    <i class="bi bi-info-circle-fill"></i>
    <div class="toast-content">
        <strong>Thông báo mới</strong>
        <p>🔥 {{optional($ruleService)->note}}</p>
    </div>
    <span class="toast-close" onclick="hideToast('toast-news')">×</span>
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
        top: -100px; /* mặc định ẩn */

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
function showToast(id, index = 0) {
    const toast = document.getElementById(id);
    if (!toast) return;

    toast.style.top = (80 + index * 90) + "px"; // 👈 xếp tầng
    toast.classList.add('show');

    setTimeout(() => {
        hideToast(id);
    }, 10000);
}

function hideToast(id) {
    const toast = document.getElementById(id);
    if (!toast) return;

    toast.classList.remove('show');
    toast.style.top = "-100px";
}

document.addEventListener('DOMContentLoaded', function () {
    setTimeout(() => showToast('toast-rule', 0), 800);
    setTimeout(() => showToast('toast-news', 1), 1200);
});
</script>
@endsection
