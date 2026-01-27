@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page title -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h1><i class="bi bi-house-fill"></i> Bảng điều khiển Quản trị</h1>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Người dùng</h5>
                    <h2>{{ $stats_users }}</h2>
                    <small>Tổng số người dùng</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Sân bóng</h5>
                    <h2>{{ $stats_fields }}</h2>
                    <small>Tổng số sân</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Đặt sân</h5>
                    <h2>{{ $stats_bookings }}</h2>
                    <small>Đặt sân xác nhận</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Doanh thu</h5>
                    <h2>{{ number_format($stats_revenue, 0, ',', '.') }}₫</h2>
                    <small>Tổng doanh thu</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Statistics -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Thống kê dịch vụ</h5>
                </div>
                <div class="card-body">
                    <p><strong>Tổng dịch vụ:</strong> {{ $stats_services }}</p>
                    <p><strong>Dịch vụ đã sử dụng:</strong> {{ $stats_services_used }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Liên kết nhanh</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="{{ route('admin.manage.bookings') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-calendar-event"></i> Quản lý đặt sân
                        </a>
                        <a href="{{ route('admin.manage.fields') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-grid"></i> Quản lý sân
                        </a>
                        <a href="{{ route('admin.manage.services') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-list"></i> Quản lý dịch vụ
                        </a>
                        <a href="{{ route('admin.manage.orders') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-cash-stack"></i> Quản lý doanh thu
                        </a>
                        <a href="{{ route('admin.statistics') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-graph-up"></i> Thống kê
                        </a>
                        <a href="{{ route('admin.manage.feedback') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-chat-left-text"></i> Feedback
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
