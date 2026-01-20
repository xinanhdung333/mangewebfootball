@extends('layouts.visitor')

@section('content')
@php
    $stats_total = $stats_total ?? 0;
    $stats_confirmed = $stats_confirmed ?? 0;
    $stats_revenue = $stats_revenue ?? 0;
    $bookings = $bookings ?? [];
    $news = $news ?? [];
@endphp

<section class="hero-modern">
    <div class="hero-content-modern">
        <h1 class="hero-title">Chào mừng  — Đặt sân nhanh, chơi đã</h1>
        <p class="hero-subtitle">Nhanh chóng chọn sân, giờ, và dịch vụ. Trải nghiệm đặt sân mượt mà trên mọi thiết bị.</p>
        <div class="hero-ctas">
            <a href="{{ url('/pages/fields.php') }}" class="hero-btn primary"><i class="bi bi-geo-alt-fill"></i> Tìm sân gần bạn</a>
            <a href="{{ url('/pages/services.php') }}" class="hero-btn ghost"><i class="bi bi-bag"></i> Dịch vụ & đồ ăn</a>
        </div>
    </div>
</section>

<div class="container">
    <div class="row stats-row">
        <div class="stat-card stat-1"><div class="stat-icon"><i class="bi bi-calendar-check"></i></div><h5>Tổng đặt sân</h5><p class="value">{{ $stats_total }}</p></div>
        <div class="stat-card stat-2"><div class="stat-icon"><i class="bi bi-patch-check"></i></div><h5>Sân đã xác nhận</h5><p class="value">{{ $stats_confirmed }}</p></div>
        <div class="stat-card stat-3"><div class="stat-icon"><i class="bi bi-cash-stack"></i></div><h5>Tổng chi phí</h5><p class="value">{{ formatCurrency($stats_revenue) }}</p></div>
    </div>

    <div class="row g-4 mt-2">
        <div class="col-lg-8">
            <div class="section-block">
            <h3><i class="bi bi-clock-history"></i> Đặt sân gần đây</h3>
            <div class="table-wrap">
                @if(count($bookings))
                <table class="table table-hover table-custom align-middle mb-0">
                    <thead><tr><th>Sân</th><th>Ngày</th><th>Giờ</th><th>Giá</th><th>Trạng thái</th><th></th></tr></thead>
                    <tbody>
                    @foreach(array_slice($bookings,0,6) as $b)
                        <tr>
                        <td>{{ $b['field_name'] }}</td>
                        <td>{{ date('d/m/Y', strtotime($b['booking_date'])) }}</td>
                        <td>{{ $b['start_time'].' - '.$b['end_time'] }}</td>
                        <td>{{ formatCurrency($b['total_price']) }}</td>
                        <td><span class="badge bg-{{ $b['status']=='confirmed'?'success':($b['status']=='pending'?'warning':'danger') }}">{{ $b['status']=='confirmed'?'Xác nhận':($b['status']=='pending'?'Chờ':'Hủy') }}</span></td>
                        <td><a href="{{ url('pages/booking-detail.php') }}?id={{ $b['id'] }}" class="btn btn-sm btn-outline-primary">Xem</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @else
                <div class="p-4">Chưa có đặt sân nào. <a href="{{ url('/pages/fields.php') }}">Đặt ngay</a></div>
                @endif
            </div>
            <a href="{{ url('../my-bookings.php') }}" class="btn btn-outline-primary mt-2">Xem tất cả</a>
            </div>

            <div class="section-block mt-4">
                <h3><i class="bi bi-gift"></i> Khuyến mãi hôm nay</h3>
                <div class="intro-box">
                    <p>Giảm 20% giá sân cho các khung giờ từ 14:00 - 17:00. Nhanh tay đặt ngay!</p>
                    <a href="#" class="btn btn-sm btn-warning">Xem khuyến mãi</a>
                </div>
            </div>

        </div>
        <div class="col-lg-4">
            <div class="section-block">
                <h3><i class="bi bi-info-circle"></i> Giới thiệu</h3>
                <div class="intro-box">
                    <p>Hệ thống sân bóng hiện đại, đạt chuẩn thi đấu – chiếu sáng LED, cỏ nhân tạo cao cấp, khu dịch vụ tiện nghi. Trải nghiệm tốt nhất cho người chơi bóng phong trào.</p>
                    <a href="{{ url('/pages/about.php') }}" class="btn btn-sm btn-primary">Tìm hiểu thêm</a>
                </div>
            </div>

            <div class="section-block mt-4">
                <h3><i class="bi bi-info-circle"></i> Về chúng tôi</h3>
                <div class="intro-box">
                    <p>Hệ thống sân bóng hiện đại, đạt chuẩn thi đấu – chiếu sáng LED, cỏ nhân tạo cao cấp, khu dịch vụ tiện nghi. Trải nghiệm tốt nhất cho người chơi bóng phong trào.</p>
                    <a href="{{ url('/pages/about.php') }}" class="btn btn-sm btn-primary">Tìm hiểu thêm</a>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <div class="section-block">
                <h3><i class="bi bi-newspaper"></i> Tin mới</h3>
                <div class="news-grid">
                    @foreach(array_slice($news,0,3) as $a)
                        <div class="news-card">
                            <img src="{{ $a['urlToImage'] }}" alt="">
                            <div class="body">
                                <h6>{{ $a['title'] }}</h6>
                                <small>{{ date('d/m/Y H:i', strtotime($a['publishedAt'])) }}</small>
                                <p>{{ $a['description'] }}</p>
                                <a href="{{ $a['url'] }}" target="_blank">Xem thêm</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
    <script>
    const hero = document.querySelector('.hero-modern');
    window.addEventListener('scroll', () => { if (hero) hero.style.backgroundPositionY = `${window.scrollY*0.2}px`; }, { passive:true });
    </script>
</div>

@endsection

