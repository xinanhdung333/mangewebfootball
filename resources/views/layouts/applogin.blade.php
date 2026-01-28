<!DOCTYPE html>
<html lang="vi">
<head>
    <link rel="icon" type="image/png" sizes="256x256" href="{{ asset('assets/images/logo.jpg') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($page_title) ? $page_title . ' - ' . config('app.name') : config('app.name') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    <style>
        #mascot {
            position: fixed;
            top: 80%;
            left: 90%;
            width: 80px;
            height: 80px;
            cursor: grab;
            z-index: 9999;
        }
        .admin-nav {
            display: flex;
            gap: 10px;
            margin: 0;
            padding: 0;
            list-style: none;
            align-items: center;
        }
        .admin-nav li a {
            color: #fff;
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            transition: 0.2s;
            display: block;
        }
        .admin-nav li a:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="{{ route('home') }}">
    <i class="bi bi-dribbble"></i> Football Booking
</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                @auth
                    @if(auth()->user()->role === 'user')
                        <li class="nav-item"><a class="nav-link" href="{{ route('home') }}"><i class="bi bi-house"></i> Trang chủ</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('fields.index') }}"><i class="bi bi-grid"></i> Sân</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('bookings.my') }}"><i class="bi bi-calendar"></i> Đặt sân của tôi</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('services.index') }}"><i class="bi bi-bag"></i> Dịch vụ</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('myServices') }}"><i class="bi bi-bag-check"></i> Dịch vụ của tôi</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('cart.index') }}"><i class="bi bi-cart-fill"></i> Giỏ hàng</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('feedback') }}"><i class="bi bi-chat-dots"></i> Feedback</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('about') }}"><i class="bi bi-chat-dots"></i> About</a></li>
                    @endif

                    @if(auth()->user()->role === 'admin')
                        <ul class="admin-nav">
                            <li><a href="{{ route('admin.manage.fields') }}"><i class="bi bi-gear"></i> Quản lý sân</a></li>
                            <li><a href="{{ route('admin.manage.bookings') }}"><i class="bi bi-clipboard-check"></i> Quản lý đặt sân</a></li>
                            <li><a href="{{ route('admin.manage.services') }}"><i class="bi bi-grid"></i> Quản lý dịch vụ</a></li>
                            <li><a href="{{ route('admin.user.service.history') }}"><i class="bi bi-bag-check"></i> Chi tiết mua hàng</a></li>
                            <li><a href="{{ route('admin.manage.feedback') }}"><i class="bi bi-chat-dots"></i> Quản lý Feedback</a></li>
                            <li><a href="{{ route('admin.invoices') }}"><i class="bi bi-file-earmark-pdf"></i> Quản lý hóa đơn</a></li>
                            <li><a href="{{ route('admin.statistics') }}"><i class="bi bi-bar-chart"></i> Thống kê</a></li>
                        </ul>
                    @endif
                    @if(auth()->user()->role === 'boss')
                        <ul class="admin-nav">
                            <li><a href="{{ route('boss.manage.fields') }}"><i class="bi bi-gear"></i> Quản lý sân</a></li>
                            <li><a href="{{ route('boss.manage.bookings') }}"><i class="bi bi-clipboard-check"></i> Quản lý đặt sân</a></li>
                            <li><a href="{{ route('boss.manage.services') }}"><i class="bi bi-grid"></i> Quản lý dịch vụ</a></li>
                            <li><a href="{{ route('boss.user.service.history') }}"><i class="bi bi-bag-check"></i> Chi tiết mua hàng</a></li>
                            <li><a href="{{ route('boss.manage.feedback') }}"><i class="bi bi-chat-dots"></i> Quản lý Feedback</a></li>
                            <li><a href="{{ route('boss.invoices') }}"><i class="bi bi-file-earmark-pdf"></i> Quản lý hóa đơn</a></li>
                            <li><a href="{{ route('boss.manage.users') }}"><i class="bi bi-people"></i> Quản lý người dùng</a></li>
                            <li><a href="{{ route('boss.statistics') }}"><i class="bi bi-bar-chart"></i> Thống kê</a></li>
                        </ul>
                    @endif

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person"></i> {{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile') }}">Hồ sơ</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('logout') }}">Đăng xuất</a></li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right"></i> Đăng nhập</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('register') }}"><i class="bi bi-person-plus"></i> Đăng ký</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<a href="{{ route('home') }}">
    <img src="{{ asset('assets/images/mascot.png') }}" id="mascot" alt="Mascot">
</a>

<main class="container mt-4">
    @yield('content')
</main>

@include('partials.footer')

<script>
const mascot = document.getElementById('mascot');
let isDragging = false;
let offsetX = 0, offsetY = 0;

mascot.addEventListener('mousedown',  (e) => {
    isDragging = true;
    offsetX = e.clientX - mascot.offsetLeft;
    offsetY = e.clientY - mascot.offsetTop;
    mascot.style.cursor = 'grabbing';
});

document.addEventListener('mouseup', () => {
    isDragging = false;
    mascot.style.cursor = 'grab';
});

document.addEventListener('mousemove', (e) => {
    if (!isDragging) return;

    mascot.style.left = (e.clientX - offsetX) + 'px';
    mascot.style.top  = (e.clientY - offsetY) + 'px';
});
</script>

@stack('scripts')
</body>
</html>