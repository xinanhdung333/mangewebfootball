@extends('layouts.app')

@section('content')

<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-calendar"></i> Đặt sân của tôi</h1>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">

        <div class="btn-group">

            <button onclick="filterStatus('')" class="btn btn-primary">
                Tất cả
            </button>

            <button onclick="filterStatus('pending')" class="btn btn-warning">
                Chờ xác nhận
            </button>

            <button onclick="filterStatus('confirmed')" class="btn btn-success">
                Xác nhận
            </button>

            <button onclick="filterStatus('cancelled')" class="btn btn-danger">
                Hủy
            </button>

        </div>

    </div>
</div>

<div id="booking-table">
    @include('user.booking-table')
</div>
<script>
window.filterStatus = function(status)
{
    console.log("clicked:", status);

    let url = "/user/my-bookings-fetch";

    if(status)
    {
        url += "?status=" + status;
    }

    fetch(url)
    .then(res => res.text())
    .then(html =>
    {
        document.getElementById("booking-table").innerHTML = html;
    });
}
</script>
@endsection