@extends('layouts.app')
@section('content')

<style>
/* =======================
   GLOBAL
======================= */
body {
    background-color: #f8f9fa;
}

.section {
    padding: 70px 0;
}

/* =======================
   HERO
======================= */
.about-hero {
    position: relative;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    background-size: cover;
    background-position: center;
    color: #fff;
}

.about-hero::before {
    content: "";
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
}

.about-hero .container {
    position: relative;
    z-index: 1;
}

/* =======================
   CARD HOVER
======================= */
.card-hover {
    transition: all 0.35s ease;
}

.card-hover:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

/* =======================
   IMAGE ZOOM
======================= */
.img-hover {
    overflow: hidden;
    border-radius: 1rem;
}

.img-hover img {
    transition: transform 0.4s ease;
}

.img-hover:hover img {
    transform: scale(1.08);
}

/* =======================
   FADE IN ON SCROLL
======================= */
.fade-in {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.8s ease;
}

.fade-in.show {
    opacity: 1;
    transform: translateY(0);
}

/* =======================
   PRESS IMAGE
======================= */
.press-img {
    height: 200px;
    object-fit: cover;
}

.press-card {
    overflow: hidden;
}

.press-card img {
    transition: transform 0.4s ease;
}

.press-card:hover img {
    transform: scale(1.1);
}
</style>

<!-- =======================
        HERO
======================= -->
<div class="about-hero py-5">
    <div class="container text-center fade-in">
        <h1 class="fw-bold display-5">{{ $about['hero_title'] }}</h1>
        <p class="lead opacity-75">
            {{ $about['hero_subtitle'] }}
        </p>
    </div>
</div>

<!-- =======================
        GIỚI THIỆU
======================= -->
<div class="section bg-light">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-md-6 fade-in">
                <h2 class="fw-bold mb-3">
                    {{ $about['intro_title'] }}
                </h2>
                <p class="lead">
                    {{ $about['intro_lead'] }}
                </p>
                <p>
                    {{ $about['intro_paragraph_1'] }}
                </p>
                <p>
                    {{ $about['intro_paragraph_2'] }}
                </p>
            </div>

            <div class="col-md-6 fade-in">
                <div class="img-hover shadow">
                    <img src="https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=500&h=500&fit=crop" 
                         class="img-fluid rounded" 
                         alt="Football Booking">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- =======================
        LỊCH SỬ
======================= -->
<div class="section bg-light">
    <div class="container fade-in">
        <h2 class="fw-bold mb-3">{{ $about['history_title'] }}</h2>
        <p>
            {{ $about['history_paragraph_1'] }}
        </p>
        <p>
            {{ $about['history_paragraph_2'] }}
        </p>
    </div>
</div>

<!-- =======================
        VAI TRÒ
======================= -->
<div class="section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-md-6 fade-in">
                <div class="img-hover shadow">
                    <img src="https://images.unsplash.com/photo-1479994309496-46d86e3404fa?w=500&h=500&fit=crop" 
                         class="img-fluid rounded" 
                         alt="Cộng đồng bóng đá">
                </div>
            </div>

            <div class="col-md-6 fade-in">
                <h2 class="fw-bold mb-4">{{ $about['role_title'] }}</h2>

                <div class="row g-3">
                    <div class="col-12">
                        <div class="card card-hover border-0 rounded-4 p-3">
                            <div class="card-body p-0">
                                <strong>⚽ Kết nối cộng đồng bóng đá</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card card-hover border-0 rounded-4 p-3">
                            <div class="card-body p-0">
                                <strong>📅 Quản lý lịch thi đấu hiệu quả</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card card-hover border-0 rounded-4 p-3">
                            <div class="card-body p-0">
                                <strong>💡 Minh bạch giá & dịch vụ</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card card-hover border-0 rounded-4 p-3">
                            <div class="card-body p-0">
                                <strong>🤝 Hỗ trợ chủ sân tối ưu vận hành</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- =======================
        SỨ MỆNH & TẦM NHÌN
======================= -->
<div class="section bg-light">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6 fade-in">
                <div class="card card-hover h-100 border-0 rounded-4">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-target text-danger"></i> {{ $about['mission_title'] }}
                        </h5>
                        <p>
                            {{ $about['mission_text'] }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 fade-in">
                <div class="card card-hover h-100 border-0 rounded-4">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-eye text-info"></i> {{ $about['vision_title'] }}
                        </h5>
                        <p>
                            {{ $about['vision_text'] }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- =======================
        CTA
======================= -->
<div class="section bg-primary text-white text-center">
    <div class="container fade-in">
        <h3 class="fw-bold mb-3">Tham gia cùng chúng tôi</h3>
        <p class="opacity-75 mb-4">
            Không chỉ là đặt sân – mà là kết nối đam mê bóng đá
        </p>
        <a href="{{ route('user.fields') }}" class="btn btn-light btn-lg px-5">
            <i class="bi bi-calendar-plus"></i> Đặt sân ngay
        </a>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const items = document.querySelectorAll(".fade-in");

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add("show");
            }
        });
    }, { threshold: 0.15 });

    items.forEach(item => observer.observe(item));
});
</script>

@endsection
