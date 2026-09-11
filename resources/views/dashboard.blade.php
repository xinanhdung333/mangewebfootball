@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
@endpush

@section('content')
    <div class="dashboard-page">
        <div class="dashboard-container">
            <header class="dashboard-heading">
                <div>
                    <p class="dashboard-kicker">SportsHub</p>
                    <h1 class="dashboard-title">{{ __('Dashboard') }}</h1>
                </div>
                <time class="dashboard-date" datetime="{{ now()->toDateString() }}">{{ now()->format('d/m/Y') }}</time>
            </header>

            <section class="dashboard-hero" aria-labelledby="dashboard-welcome">
                <div class="dashboard-hero-content">
                    <p class="dashboard-hero-greeting">Xin chào, {{ Auth::user()->name }}!</p>
                    <h2 id="dashboard-welcome">Chào mừng bạn quay lại</h2>
                    <p>Theo dõi hoạt động sân bóng và quản lý các đặt sân của bạn ở một nơi.</p>
                </div>
            </section>

            <section class="dashboard-stats" aria-label="Tổng quan">
                @foreach ([
                    ['label' => 'Lịch đặt sắp tới', 'value' => '0', 'class' => 'dashboard-stat-value--green'],
                    ['label' => 'Đặt sân hoàn tất', 'value' => '0', 'class' => 'dashboard-stat-value--blue'],
                    ['label' => 'Dịch vụ đã dùng', 'value' => '0', 'class' => 'dashboard-stat-value--amber'],
                    ['label' => 'Điểm tích lũy', 'value' => '0', 'class' => 'dashboard-stat-value--violet'],
                ] as $stat)
                    <article class="dashboard-stat">
                        <p>{{ $stat['label'] }}</p>
                        <strong class="{{ $stat['class'] }}">{{ $stat['value'] }}</strong>
                    </article>
                @endforeach
            </section>

            <section class="dashboard-columns">
                <article class="dashboard-card dashboard-activity">
                    <div>
                        <h2>Hoạt động gần đây</h2>
                        <p class="dashboard-card-description">Các đặt sân mới nhất của bạn.</p>
                    </div>
                    <div class="dashboard-empty-state">
                        <p>Bạn chưa có hoạt động nào.</p>
                        <a href="{{ route('visitor.fields') }}" class="dashboard-button">Khám phá sân bóng</a>
                    </div>
                </article>

                <article class="dashboard-card">
                    <h2>Truy cập nhanh</h2>
                    <nav class="dashboard-quick-links" aria-label="Truy cập nhanh">
                        <a href="{{ route('profile.edit') }}">Cập nhật hồ sơ</a>
                        <a href="{{ route('bookings.my') }}">Xem lịch đặt sân</a>
                    </nav>
                </article>
            </section>
        </div>
    </div>
@endsection
