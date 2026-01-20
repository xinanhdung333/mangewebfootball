@extends('layouts.visitor')

@section('content')
@php $fields = $fields ?? []; @endphp

<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-grid"></i> Danh sách sân bóng</h1>

        <div class="d-flex align-items-center mt-3">
            <input type="text" id="searchField" class="form-control me-2" placeholder="Tìm kiếm sân..." 
                   style="background: rgba(255,255,255,0.8); border:1px solid #ccc;">

            <a href="{{ url('/pages/field-schedule.php') }}" class="btn btn-info">
                <i class="bi bi-calendar-check"></i> KHUNG GIỜ ĐÃ ĐƯỢC ĐẶT
            </a>
        </div>
    </div>
</div>

<div class="row" id="fieldList">
    @if(count($fields) > 0)
        @foreach($fields as $field)
            <div class="col-md-4 mb-4 field-item">
                <div class="card h-100">
                    <img src="{{ !empty($field['image']) ? url('/uploads/fields/'.$field['image']) : url('/assets/images/banner.jpg') }}" 
                        class="card-img-top" alt="{{ $field['name'] ?? '' }}" 
                        style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title">{{ $field['name'] ?? '' }}</h5>
                        @php
                            $desc = $field['description'] ?? '';
                            $shortDesc = mb_strlen($desc) > 120 ? mb_substr($desc, 0, 120) . "..." : $desc;
                        @endphp

                        <p class="card-text description-short" id="desc-short-{{ $field['id'] }}">{{ $shortDesc }}</p>

                        <p class="card-text d-none" id="desc-full-{{ $field['id'] }}">{!! nl2br(e($desc)) !!}</p>

                        @if(mb_strlen($desc) > 120)
                            <span class="show-more-btn" onclick="toggleDesc({{ $field['id'] }})">Xem thêm</span>
                        @endif

                        <p class="text-muted mb-2"><i class="bi bi-geo-alt"></i> {{ $field['location'] ?? '' }}</p>
                        <p class="text-success fw-bold mb-3">{{ formatCurrency($field['price_per_hour'] ?? 0) }}/giờ</p>

                        @php $avg = isset($field['avg_rating']) ? round($field['avg_rating'],1) : 0; $total = $field['total_reviews'] ?? 0; @endphp
                        <div class="mb-2">
                            @for ($i=1;$i<=5;$i++)
                                <span style="color: gold; font-size: 18px;">{!! ($i <= $avg) ? '★' : '☆' !!}</span>
                            @endfor
                            <span class="text-muted">({{ $avg }} / 5, {{ $total }} đánh giá)</span>
                        </div>

                        <a href="{{ url('/pages/booking.php') }}?field_id={{ $field['id'] }}" class="btn btn-primary w-100"><i class="bi bi-calendar-plus"></i> Đặt sân</a>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="col-md-12"><div class="alert alert-info">Hiện tại không có sân nào.</div></div>
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
        btn.innerText = "Xem thêm";
    } else {
        short.classList.add("d-none");
        full.classList.remove("d-none");
        btn.innerText = "Thu gọn";
    }
}
document.getElementById('searchField').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    document.querySelectorAll('#fieldList .field-item').forEach(item => {
        const name = item.querySelector('.card-title').innerText.toLowerCase();
        const desc = item.querySelector('.card-text').innerText.toLowerCase();
        if (name.includes(filter) || desc.includes(filter)) item.style.display = ''; else item.style.display = 'none';
    });
});
</script>

<style>
.description-short { overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
.show-more-btn { color: #0d6efd; cursor: pointer; font-size: 14px; }
</style>

@endsection
