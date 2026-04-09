@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-grid"></i> Danh sách sân bóng</h1>
<!-- Thanh tìm kiếm + nút lịch đặt -->
        <div class="d-flex align-items-center mt-3">
            <input type="text" id="searchField" class="form-control me-2" placeholder="Tìm kiếm sân..." 
                   style="background: rgba(255,255,255,0.8); border:1px solid #ccc;">
<select id="priceSort" class="form-select ms-2" style="max-width:220px;">
    <option value="name">Sắp xếp theo tên</option>
    <option value="priceAsc">Giá thấp đến cao</option>
    <option value="priceDesc">Giá cao đến thấp</option>
    <option value="rating">Đánh giá cao nhất</option>
</select>

<select id="distanceSort" class="form-select ms-2" style="max-width:220px;">
    <option value="nearest">Gần nhất</option>
    <option value="farthest">Xa nhất</option>
</select>
            <a href="{{  route('user.fieldSchedule') }}" class="btn btn-info">
                <i class="bi bi-calendar-check"></i> KHUNG GIỜ ĐÃ ĐƯỢC ĐẶT
            </a>
        </div>
    </div>
</div>
<div class="row" id="fieldList">
    @if($fields && count($fields) > 0)
        @foreach($fields as $field)
            <div class="col-md-4 mb-4 field-item">
                <div class="card h-100">
                    <img src="{{ !empty($field->image) ? asset('uploads/fields/' . $field->image) : asset('assets/images/banner.jpg') }}" 
                        class="fields" alt="{{ htmlspecialchars($field->name) }}">
                    <div class="card-body">
                        <h5 class="card-title">{{ htmlspecialchars($field->name) }}</h5>
                        
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
@endsection
