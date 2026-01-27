@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-info-circle"></i> Giới thiệu</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Về Hệ Thống Quản Lý Sân Bóng</h5>
                <p class="card-text">
                    Đây là hệ thống quản lý đặt sân bóng toàn diện, giúp boss quản lý các sân, dịch vụ, 
                    người dùng và các đơn đặt sân một cách hiệu quả.
                </p>
                <hr>
                <h6>Tính Năng Chính:</h6>
                <ul>
                    <li>Quản lý sân bóng</li>
                    <li>Quản lý dịch vụ bổ sung</li>
                    <li>Quản lý người dùng</li>
                    <li>Theo dõi đơn đặt sân</li>
                    <li>Thống kê doanh thu</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Thông Tin Liên Hệ</h5>
            </div>
            <div class="card-body">
                <p><strong>Email:</strong> support@footballbooking.local</p>
                <p><strong>Điện thoại:</strong> 0123 456 789</p>
                <p><strong>Địa chỉ:</strong> 123 Đường ABC, TP HCM</p>
            </div>
        </div>
    </div>
</div>
