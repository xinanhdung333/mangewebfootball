@extends('layouts.app')

@section('title', 'Trang chủ')

@section('content')
<style>
.feature-box {
    background: #ffffff;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    text-align: left;
}
.feature-box:hover {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    transform: translateY(-8px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.12);
}
.feature-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    color: #fff;
    font-size: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
}
.bg-blue   { background: #3f8efc; }
.bg-yellow { background: #f3c63f; }
.bg-orange { background: #ff8a3d; }
.bg-green  { background: #3ecf8e; }

.feature-title {
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 10px;
    color: #333;
}
.feature-text {
    color: #666;
    font-size: 15px;
    line-height: 1.6;
}
.feature-box button {
    border-radius: 30px;
    padding: 6px 20px;
}
.jumbotron {
    background-image: url('{{ asset('assets/images/2340596.jpg') }}');
    background-size: cover;
    background-position: center;
    color: white;
}
</style>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="jumbotron p-5 rounded">
                <h1 class="display-4">Chào mừng tới Football Booking</h1>
                <p class="lead">Đặt sân bóng nhanh chóng, dễ dàng và an toàn</p>
                <hr>

                @guest
                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg ms-2">
                        <i class="bi bi-person-plus"></i> Đăng ký
                    </a>
                @endguest

                @auth
                  @if(auth()->user()->role === 'admin')
    <a href="{{ route('admin.statistics') }}" class="btn btn-danger btn-lg">
        <i class="bi bi-speedometer2"></i> Quản lý hệ thống
    </a>
@endif
                  @if(auth()->user()->role === 'boss')
    <a href="{{ route('boss.statistics') }}" class="btn btn-danger btn-lg">
        <i class="bi bi-speedometer2"></i> Quản lý hệ thống
    </a>
@endif
                    @elseif(auth()->user()->role === 'user')
                        <a href="{{ route('fields.index') }}" class="btn btn-success btn-lg">
                            <i class="bi bi-calendar-plus"></i> Đặt sân ngay
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    <!-- Feature section -->
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6">
                <div class="feature-box">
                    <div class="feature-icon bg-blue">
                        <i class="bi bi-cloud"></i>
                    </div>
                    <h3 class="feature-title">Sed feugiat</h3>
                    <p class="feature-text">
                        Integer volutpat ante et accumsan commophasellus sed aliquam feugiat lorem aliquet ut enim rutrum phasellus iaculis accumsan dolore magna aliquam veroeros.
                    </p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="feature-box">
                    <div class="feature-icon bg-yellow">
                        <i class="bi bi-lock"></i>
                    </div>
                    <h3 class="feature-title">Enim phasellus</h3>
                    <p class="feature-text">
                        Integer volutpat ante et accumsan commophasellus sed aliquam feugiat lorem aliquet ut enim rutrum phasellus iaculis accumsan dolore magna aliquam veroeros.
                    </p>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-6">
                <div class="feature-box">
                    <div class="feature-icon bg-orange">
                        <i class="bi bi-lightning"></i>
                    </div>
                    <h3 class="feature-title">Sed lorem adipiscing</h3>
                    <p class="feature-text">
                        Integer volutpat ante et accumsan commophasellus sed aliquam feugiat lorem aliquet ut enim rutrum phasellus iaculis accumsan dolore magna aliquam veroeros.
                    </p>
                    <button class="btn btn-outline-secondary mt-3">Learn More</button>
                </div>
            </div>

            <div class="col-md-6">
                <div class="feature-box">
                    <div class="feature-icon bg-green">
                        <i class="bi bi-bar-chart"></i>
                    </div>
                    <h3 class="feature-title">Accumsan integer</h3>
                    <p class="feature-text">
                        Integer volutpat ante et accumsan commophasellus sed aliquam feugiat lorem aliquet ut enim rutrum phasellus iaculis accumsan dolore magna aliquam veroeros.
                    </p>
                    <button class="btn btn-outline-secondary mt-3">Learn More</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
