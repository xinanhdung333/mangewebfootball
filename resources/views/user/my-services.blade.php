@extends('layouts.app')
@section('content')

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1><i class="bi bi-bag-check"></i> Dịch vụ của bạn</h1>
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
            <option value="paid">Đã thanh toán</option>
            <option value="cancelled">Đã huỷ</option>
        </select>
    </div>

</div>


  <div id="service-table-area">

<!-- @include('user.service-table') -->

</div>
<script>
function loadServices(url = null) {
    const keyword = document.getElementById('search-input').value;
    const status = document.getElementById('status-filter').value;

    if (!url) {
        url = `{{ route('user.services.search') }}?keyword=${keyword}&status=${status}`;
    }

    fetch(url)
        .then(res => res.text())
        .then(data => {
            document.getElementById('service-table-area').innerHTML = data;

            // gán lại click cho pagination links
            document.querySelectorAll('#service-table-area .pagination a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    loadServices(this.href);
                });
            });
        });
}

document.getElementById('search-input').addEventListener('keyup', () => loadServices());
document.getElementById('status-filter').addEventListener('change', () => loadServices());

// gọi lần đầu nếu muốn
loadServices();
</script>
@endsection
