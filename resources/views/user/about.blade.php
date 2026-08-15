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
        <h1 class="fw-bold display-5">Về chúng tôi</h1>
        <p class="lead opacity-75">
            Sứ mệnh – Tầm nhìn – Giá trị của SportsHub
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
                    Về <span class="text-primary">SportsHub</span>
                </h2>
                <p class="lead">
                    SportsHub là nền tảng đặt sân bóng và dịch vụ đi kèm,
                    kết nối cộng đồng yêu bóng đá với các sân chất lượng.
                </p>
                <p>
                    SportsHub là nền tảng đặt sân bóng và các dịch vụ đi kèm được xây dựng nhằm kết nối cộng đồng yêu bóng đá với những sân bóng chất lượng, uy tín và phù hợp với nhu cầu đa dạng của người chơi. Hệ thống cho phép người dùng dễ dàng tìm kiếm, so sánh và lựa chọn sân bóng theo vị trí, khung giờ, mức giá cũng như các dịch vụ hỗ trợ đi kèm, mang lại trải nghiệm đặt sân thuận tiện và nhanh chóng hơn so với phương thức truyền thống.
                </p>
                <p>
                    Xuất phát từ những khó khăn thực tế của người chơi bóng phong trào như việc tìm sân trống, thiếu thông tin minh bạch về giá cả, chất lượng sân và các dịch vụ liên quan, SportsHub ra đời với mục tiêu giải quyết triệt để những bất cập đó. Thông qua việc ứng dụng công nghệ vào quản lý và vận hành, chúng tôi xây dựng một hệ thống hiện đại, chính xác và minh bạch.
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
        <h2 class="fw-bold mb-3">Lịch sử ra đời</h2>
        <p>
            SportsHub được hình thành từ một dự án nhỏ vào năm 2025, trong bối cảnh nhu cầu đặt sân bóng của người chơi phong trào ngày càng tăng cao nhưng các hình thức đặt sân vẫn chủ yếu mang tính thủ công, thiếu tính đồng bộ và minh bạch. Người chơi thường phải liên hệ trực tiếp với chủ sân, khó nắm bắt lịch trống, giá cả và chất lượng dịch vụ.
        </p>
        <p>
            Nhận thấy những hạn chế đó, nhóm phát triển đã từng bước xây dựng SportsHub như một giải pháp ứng dụng công nghệ vào quản lý và đặt sân bóng. Ban đầu, hệ thống chỉ tập trung vào chức năng đặt sân cơ bản. Qua thời gian, dựa trên nhu cầu thực tế và phản hồi từ người dùng, SportsHub được mở rộng với nhiều tính năng nâng cao.
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
                <h2 class="fw-bold mb-4">Vai trò trong cuộc sống</h2>

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
                            <i class="bi bi-target text-danger"></i> Sứ mệnh
                        </h5>
                        <p>
                            Mang đến một nền tảng đặt sân bóng hiện đại, thân thiện và dễ sử dụng, giúp mọi người tiếp cận thể thao một cách thuận tiện, nhanh chóng và bền vững. SportsHub hướng tới việc đơn giản hóa quá trình tìm kiếm, đặt sân và sử dụng các dịch vụ đi kèm.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 fade-in">
                <div class="card card-hover h-100 border-0 rounded-4">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-eye text-info"></i> Tầm nhìn
                        </h5>
                        <p>
                SportsHub hướng tới trở thành hệ sinh thái thể thao phong trào hàng đầu Việt Nam, nơi kết nối toàn diện giữa người chơi, chủ sân và các dịch vụ liên quan đến thể thao. Chúng tôi mong muốn góp phần thúc đẩy phong trào thể thao phát triển bền vững.
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
