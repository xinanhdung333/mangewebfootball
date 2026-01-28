@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-speedometer2"></i> Bảng điều khiển (Boss)</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Người dùng</h5>
                <p class="card-text">{{ \App\Models\User::count() }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Sân</h5>
                <p class="card-text">{{ \App\Models\Field::count() }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title">Đặt sân</h5>
                <p class="card-text">{{ \App\Models\Booking::count() }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <h5 class="card-title">Dịch vụ</h5>
                <p class="card-text">{{ \App\Models\Service::count() }}</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Links</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="{{ route('boss.manage.bookings') }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-calendar"></i> Quản lý đặt sân
                    </a>
                    <a href="{{ route('boss.manage.services') }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-grid"></i> Quản lý dịch vụ
                    </a>
                    <a href="{{ route('boss.manage.fields') }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-pin-map"></i> Quản lý sân
                    </a>
                    <a href="{{ route('boss.manage.users') }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-people"></i> Quản lý người dùng
                    </a>
                    <a href="{{ route('boss.statistics') }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-bar-chart"></i> Thống kê
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
