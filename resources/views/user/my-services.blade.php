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
function loadServices(url = null) {
    const keyword = document.getElementById('search-input').value;
    const status = document.getElementById('status-filter').value;

    if (!url) {
        url = `{{ route('user.services.search') }}?keyword=${encodeURIComponent(keyword)}&status=${encodeURIComponent(status)}&partial=1`;
    }

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
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
