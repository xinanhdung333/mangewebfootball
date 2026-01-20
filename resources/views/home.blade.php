@extends('layouts.visitor')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="jumbotron bg-light p-5 rounded text-white" style="background-color: rgba(0,0,0,0.35);">
                    <h1 class="display-4">Chào mừng tới Football Booking</h1>
                    <p class="lead">Đặt sân bóng nhanh chóng, dễ dàng và an toàn</p>
                    <hr>

                    @guest
                        <a href="{{ url('/pages/login') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
                        </a>
                        <a href="{{ url('/pages/register') }}" class="btn btn-outline-primary btn-lg ms-2">
                            <i class="bi bi-person-plus"></i> Đăng ký
                        </a>
                    @endguest

                    @auth
                        @if(optional($user)->role === 'admin' || optional($user)->role === 'boss')
                            <a href="{{ url('/pages/admin/statistics') }}" class="btn btn-danger btn-lg">
                                <i class="bi bi-speedometer2"></i> Quản lý hệ thống
                            </a>
                        @endif

                        @if(optional($user)->role === 'user')
                            <a href="{{ url('/pages/fields') }}" class="btn btn-primary btn-lg">
                                <i class="bi bi-calendar-plus"></i> Đặt sân ngay
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>

        <div class="container mt-5">
            <div class="row">
                <div class="col-md-6">
                    <div class="feature-box">
                        <div class="feature-icon bg-blue">
                            <i class="bi bi-cloud"></i>
                        </div>
                        <h3 class="feature-title">Sed feugiat</h3>
                        <p class="feature-text">Integer volutpat ante et accumsan commophasellus sed aliquam feugiat lorem aliquet ut enim rutrum phasellus iaculis accumsan dolore magna aliquam veroeros.</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="feature-box">
                        <div class="feature-icon bg-yellow">
                            <i class="bi bi-lock"></i>
                        </div>
                        <h3 class="feature-title">Enim phasellus</h3>
                        <p class="feature-text">Integer volutpat ante et accumsan commophasellus sed aliquam feugiat lorem aliquet ut enim rutrum phasellus iaculis accumsan dolore magna aliquam veroeros.</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="feature-box">
                        <div class="feature-icon bg-orange">
                            <i class="bi bi-lightning"></i>
                        </div>
                        <h3 class="feature-title">Sed lorem adipiscing</h3>
                        <p class="feature-text">Integer volutpat ante et accumsan commophasellus sed aliquam feugiat lorem aliquet ut enim rutrum phasellus iaculis accumsan dolore magna aliquam veroeros.</p>
                        <button class="btn btn-outline-secondary mt-3">Learn More</button>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="feature-box">
                        <div class="feature-icon bg-green">
                            <i class="bi bi-bar-chart"></i>
                        </div>
                        <h3 class="feature-title">Accumsan integer</h3>
                        <p class="feature-text">Integer volutpat ante et accumsan commophasellus sed aliquam feugiat lorem aliquet ut enim rutrum phasellus iaculis accumsan dolore magna aliquam veroeros.</p>
                        <button class="btn btn-outline-secondary mt-3">Learn More</button>
                    </div>
                </div>
            </div>
        </div>

        <br><br><br>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endpush
