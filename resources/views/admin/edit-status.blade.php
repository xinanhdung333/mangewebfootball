@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-pencil-square"></i> Chỉnh Sửa Trạng Thái</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Cập Nhật Trạng Thái Đặt Sân</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Vui lòng sử dụng trang <a href="{{ route('boss.manage.bookings') }}">Quản lý đặt sân</a> để cập nhật trạng thái các đơn đặt sân.</p>
            </div>
        </div>
    </div>
</div>
