@extends('layouts.app')

@section('content')


    <!-- TITLE -->
    <div class="row mb-3">
        <div class="col-md-12">
            <h3>📅 Sân đã đặt</h3>
        </div>
    </div>

    <!-- SEARCH + FILTER -->
    <div class="row mb-3">

        <div class="col-md-4">
            <input type="text"
                   id="search-input"
                   class="form-control"
                   placeholder="Tìm theo tên sân...">
        </div>

        <div class="col-md-3">
            <select id="status-filter" class="form-select">
                <option value="">Tất cả trạng thái</option>
                <option value="pending">Chờ xử lý</option>
                <option value="paid">Đã thanh toán</option>
                <option value="cancelled">Đã huỷ</option>
            </select>
        </div>

    </div>

    <!-- CONTENT LOAD AJAX -->
 

            <div id="booking-table-area">
                ///data
            </div>


       
<script>
function loadBookings(url = null) {
    const keyword = document.getElementById('search-input').value;
    const status = document.getElementById('status-filter').value;

    if (!url) {
        url = `{{ route('user.search.Booking') }}?keyword=${keyword}&status=${status}`;
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


// delegation click pagination (quan trọng)
document.addEventListener('click', function(e) {
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

// load lần đầu
loadBookings();
</script>

@endsection