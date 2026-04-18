@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1><i class="bi bi-bag-check"></i> Sân đã đặt</h1>
        </div>
    </div>

    <div class="row mb-3 g-3">
        <div class="col-md-4">
            <input
                type="text"
                id="search-input"
                class="form-control"
                placeholder="Tìm sân..."
                value="{{ request('keyword', '') }}"
            >
        </div>

        <div class="col-md-3">
            <select id="status-filter" class="form-select">
                <option value="">Tất cả trạng thái</option>
                <option value="pending" {{ request('status', $filterStatus ?? '') === 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                <option value="confirmed" {{ request('status', $filterStatus ?? '') === 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                <option value="cancelled" {{ request('status', $filterStatus ?? '') === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
            </select>
        </div>
    </div>

    <div id="booking-table-area" class="py-4"></div>
</div>

<script>
function loadBookings(url = null) {
    const keyword = document.getElementById('search-input').value;
    const status = document.getElementById('status-filter').value;

    if (!url) {
        url = `{{ route('user.search.Booking') }}?keyword=${encodeURIComponent(keyword)}&status=${encodeURIComponent(status)}`;
    }

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.text())
    .then(data => {
        document.getElementById('booking-table-area').innerHTML = data;
    });
}

document.addEventListener('click', function (e) {
    const link = e.target.closest('#booking-table-area .pagination a');

    if (link) {
        e.preventDefault();

        const url = new URL(link.href);
        url.searchParams.set('keyword', document.getElementById('search-input').value);
        url.searchParams.set('status', document.getElementById('status-filter').value);

        loadBookings(url.toString());
    }
});

document.getElementById('search-input').addEventListener('keyup', () => loadBookings());
document.getElementById('status-filter').addEventListener('change', () => loadBookings());

loadBookings();
</script>
@endsection
