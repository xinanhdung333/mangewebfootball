@extends('layouts.app')
@section('content')

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1><i class="bi bi-bag-check"></i> Dịch vụ đã đặt</h1>
        </div>
    </div>
<div class="row mb-3">

    <div class="col-md-4">
        <input type="text"
               id="search-input"
               class="form-control"
               placeholder="Tìm dịch vụ...">
    </div>

    <div class="col-md-3">
        <select id="status-filter" class="form-select">
            <option value="">Tất cả trạng thái</option>
            <option value="pending">Chờ xử lý</option>
            <option value="confirmed">Đã thanh toán</option>
            <option value="cancelled">Đã huỷ</option>
        </select>
    </div>

</div>


  <div id="service-table-area" class ="py-4">
{{-- @include('user.service-table') --}}
</div>
<script>
function loadServices(url = null) {
    const keyword = document.getElementById('search-input').value;
    const status = document.getElementById('status-filter').value;

    if (!url) {
        url = `{{ route('user.services.search') }}?keyword=${keyword}&status=${status}`;
    }

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.text())
    .then(data => {
        document.getElementById('service-table-area').innerHTML = data;
    });
}


// delegation click pagination (quan trọng)
document.addEventListener('click', function(e) {
    const link = e.target.closest('#service-table-area .pagination a');

    if (link) {
        e.preventDefault();

        const url = new URL(link.href);
        url.searchParams.set('keyword', document.getElementById('search-input').value);
        url.searchParams.set('status', document.getElementById('status-filter').value);

        loadServices(url.toString());
    }
});

document.getElementById('search-input').addEventListener('keyup', () => loadServices());
document.getElementById('status-filter').addEventListener('change', () => loadServices());

// load lần đầu
loadServices();
</script>
@endsection
