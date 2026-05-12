@extends('layouts.app')

@section('content')

<style>
body {
    background: #f4f6f9;
}

/* Wrapper */
.settings-wrapper {
    display: flex;
    max-width: 1200px;
    margin:    0px auto;
    gap: 20px;
}

/* Sidebar */
.sidebar {
    width: 260px;
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    height: fit-content;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.sidebar h2 {
    font-size: 18px;
    margin-bottom: 20px;
}

.sidebar a {
    display: block;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 6px;
    color: #333;
    text-decoration: none;
    transition: 0.2s;
}

.sidebar a:hover {
    background: #f1f2f6;
}

.sidebar a.active {
    background: #ee4d2d;
    color: #fff;
}

/* Content */
.content {
    flex: 1;
}

/* Section card */
.section {
    background: #fff;
    border-radius: 14px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}

.section h3 {
    font-size: 20px;
    margin-bottom: 15px;
}

/* Item */
.item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    border-bottom: 1px solid #eee;
    border-radius: 8px;
    transition: 0.2s;
}

.item:hover {
    background: #f9f9f9;
}

.item:last-child {
    border-bottom: none;
}

.item-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.item i {
    color: #ee4d2d;
    font-size: 18px;
}

/* Toggle */
.switch {
    position: relative;
    width: 42px;
    height: 22px;
}

.switch input {
    display: none;
}

.slider {
    position: absolute;
    background: #ccc;
    border-radius: 20px;
    inset: 0;
    cursor: pointer;
    transition: 0.3s;
}

.slider:before {
    content: "";
    position: absolute;
    width: 18px;
    height: 18px;
    left: 2px;
    top: 2px;
    background: #fff;
    border-radius: 50%;
    transition: 0.3s;
}

input:checked + .slider {
    background: #ee4d2d;
}

input:checked + .slider:before {
    transform: translateX(20px);
}
.settings-page .container {
    max-width: 100% !important;
    padding: 0 !important;
}
</style>
<div class="settings-page">

<div class="settings-wrapper">

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Cài đặt</h2>
        <a href="#" class="active">Bảo mật</a>
        <a href="#">Giao diện</a>
        <a href="#">Thông báo</a>
    </div>

    <!-- Content -->
    <div class="content">

        <!-- Bảo mật -->
        <div class="section">
            <h3>Bảo mật</h3>
                    <a href ="{{route('admin.settings.pricing')}}">

            <div class="item">
                <div class="item-left">
                    <i class="bi bi-phone"></i>
                    Chỉnh sửa tăng giảm giá , ưu đãi
                </div>
                <i class="bi bi-chevron-right"></i>

            </div>
</a>


            <div class="item">
                <div class="item-left">
                    <i class="bi bi-shield-lock"></i>
                    Thiết lập Digital OTP
                </div>
                <i class="bi bi-chevron-right"></i>
            </div>

            <div class="item">
                <div class="item-left">
                    <i class="bi bi-person-bounding-box"></i>
                    Xác thực khuôn mặt
                </div>
                <i class="bi bi-chevron-right"></i>
            </div>

            <div class="item">
                <div class="item-left">
                    <i class="bi bi-fingerprint"></i>
                    Đăng nhập Face ID
                </div>

                <label class="switch">
                    <input type="checkbox" checked>
                    <span class="slider"></span>
                </label>
            </div>

        </div>

        <!-- Giao diện -->
        <div class="section">
            <h3>Giao diện</h3>

            <div class="item">
                <div class="item-left">
                    <i class="bi bi-palette"></i>
                    Đổi theme
                </div>
                <i class="bi bi-chevron-right"></i>
            </div>

            <div class="item">
                <div class="item-left">
                    <i class="bi bi-image"></i>
                    Ảnh nền cá nhân
                </div>
                <i class="bi bi-chevron-right"></i>
            </div>

        </div>

    </div>

</div>
</div>

@endsection