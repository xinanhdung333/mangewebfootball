@extends('layouts.visitor')

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
    background: url('../assets/images/about/hero-football.jpg') center/cover no-repeat;
    color: #fff;
}

.about-hero::before {
    content: "";
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.55);
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
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
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
            Sứ mệnh – Tầm nhìn – Giá trị của Football Booking
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
                    Về <span class="text-primary">Football Booking</span>
                </h2>
                <p class="lead">
                    Football Booking là nền tảng đặt sân bóng và dịch vụ đi kèm,
                    kết nối cộng đồng yêu bóng đá với các sân chất lượng.
                </p>
                <p>
                 Football Booking là nền tảng đặt sân bóng và các dịch vụ đi kèm được xây dựng nhằm kết nối cộng đồng yêu bóng đá với những sân bóng chất lượng, uy tín và phù hợp với nhu cầu đa dạng của người chơi. Hệ thống cho phép người dùng dễ dàng tìm kiếm, so sánh và lựa chọn sân bóng theo vị trí, khung giờ, mức giá cũng như các dịch vụ hỗ trợ đi kèm, mang lại trải nghiệm đặt sân thuận tiện và nhanh chóng hơn so với phương thức truyền thống.

Xuất phát từ những khó khăn thực tế của người chơi bóng phong trào như việc tìm sân trống, thiếu thông tin minh bạch về giá cả, chất lượng sân và các dịch vụ liên quan, Football Booking ra đời với mục tiêu giải quyết triệt để những bất cập đó. Thông qua việc ứng dụng công nghệ vào quản lý và vận hành, chúng tôi xây dựng một hệ thống hiện đại, chính xác và minh bạch, giúp người chơi chủ động hơn trong việc sắp xếp thời gian, đồng thời hỗ trợ chủ sân tối ưu hóa quy trình quản lý, nâng cao hiệu quả hoạt động và chất lượng dịch vụ. Football Booking không chỉ là một nền tảng đặt sân, mà còn hướng tới việc xây dựng một cộng đồng bóng đá phong trào năng động, gắn kết và phát triển bền vững.
                </p>
            </div>

            <div class="col-md-6 fade-in">
                <div class="img-hover shadow">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTYWh80sho3ie1ZQDujBYADB4YStf64ZJOaGw&s"
                         class="img-fluid"
                         alt="Football Booking">
                      <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTYWh80sho3ie1ZQDujBYADB4YStf64ZJOaGw&s"
                         class="img-fluid"
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
 Football Booking được hình thành từ một dự án nhỏ vào năm 2025, trong bối cảnh nhu cầu đặt sân bóng của người chơi phong trào ngày càng tăng cao nhưng các hình thức đặt sân vẫn chủ yếu mang tính thủ công, thiếu tính đồng bộ và minh bạch. Người chơi thường phải liên hệ trực tiếp với chủ sân, khó nắm bắt lịch trống, giá cả và chất lượng dịch vụ, dẫn đến nhiều bất tiện trong quá trình tổ chức và tham gia thi đấu.
        </p>
        <p>
            Nhận thấy những hạn chế đó, nhóm phát triển đã từng bước xây dựng Football Booking như một giải pháp ứng dụng công nghệ vào quản lý và đặt sân bóng. Ban đầu, hệ thống chỉ tập trung vào chức năng đặt sân cơ bản. Qua thời gian, dựa trên nhu cầu thực tế và phản hồi từ người dùng, Football Booking được mở rộng với nhiều tính năng nâng cao như quản lý lịch đặt sân thông minh, cung cấp các dịch vụ bổ sung, hỗ trợ thanh toán trực tuyến và cho phép người chơi đánh giá chất lượng sân. Quá trình phát triển này đánh dấu bước chuyển mình từ một công cụ đơn giản sang một nền tảng toàn diện, góp phần nâng cao trải nghiệm cho cả người chơi và chủ sân.
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
                    <img src="https://images2.thanhnien.vn/528068263637045248/2023/2/13/anh-giai-bong-da-sinh-vien-thanh-nien-9-16763053961831969198175.jpg"
                         class="img-fluid"
                         alt="Cộng đồng bóng đá">
                </div>
            </div>

            <div class="col-md-6 fade-in">
                <h2 class="fw-bold mb-4">Vai trò trong cuộc sống</h2>

                <div class="row g-3">
                    <div class="col-12">
                        <div class="card card-hover border-0 rounded-4">
                            <div class="card-body">⚽ Kết nối cộng đồng bóng đá</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card card-hover border-0 rounded-4">
                            <div class="card-body">📅 Quản lý lịch thi đấu hiệu quả</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card card-hover border-0 rounded-4">
                            <div class="card-body">💡 Minh bạch giá & dịch vụ</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card card-hover border-0 rounded-4">
                            <div class="card-body">🤝 Hỗ trợ chủ sân tối ưu vận hành</div>
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
                        <h5 class="fw-bold">Sứ mệnh</h5>
                        <p>
                      Mang đến một nền tảng đặt sân bóng hiện đại, thân thiện và dễ sử dụng, giúp mọi người tiếp cận thể thao một cách thuận tiện, nhanh chóng và bền vững. Football Booking hướng tới việc đơn giản hóa quá trình tìm kiếm, đặt sân và sử dụng các dịch vụ đi kèm, đồng thời đảm bảo sự minh bạch về giá cả và chất lượng. Thông qua việc ứng dụng công nghệ, chúng tôi mong muốn thúc đẩy phong trào thể thao cộng đồng, nâng cao sức khỏe, tinh thần gắn kết và xây dựng một môi trường chơi thể thao lành mạnh cho mọi người
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 fade-in">
                <div class="card card-hover h-100 border-0 rounded-4">
                    <div class="card-body">
                        <h5 class="fw-bold">Tầm nhìn</h5>
                        <p>
Football Booking hướng tới trở thành hệ sinh thái thể thao phong trào hàng đầu Việt Nam, nơi kết nối toàn diện giữa người chơi, chủ sân và các dịch vụ liên quan đến thể thao. Chúng tôi không chỉ dừng lại ở việc cung cấp nền tảng đặt sân, mà còn phát triển một hệ sinh thái số thông minh, hỗ trợ quản lý, vận hành và nâng cao trải nghiệm cho toàn bộ cộng đồng thể thao. Trong tương lai, Football Booking mong muốn góp phần thúc đẩy phong trào thể thao phát triển bền vững, ứng dụng công nghệ vào đời sống hàng ngày, tạo ra một môi trường thể thao hiện đại, tiện lợi và gắn kết trên phạm vi toàn quốc.           
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- =======================
        BÁO CHÍ
======================= -->
<div class="section bg-light">
    <div class="container">
        <h2 class="fw-bold mb-4 fade-in">Báo chí & Tham khảo</h2>

        <div class="row g-4">

            <!-- VNEXPRESS -->
            <div class="col-md-4 fade-in">
                <div class="card press-card card-hover border-0 rounded-4 h-100">
                    <img src="https://i1-kinhdoanh.vnecdn.net/2022/12/02/Ong-Le-Hoang-Chau-Chu-tich-HoR-6155-3910-1669950038.jpg?w=1020&h=0&q=100&dpr=1&fit=crop&s=9Z_Q-LmI3AofIYXjQtUrTA"
                         class="card-img-top press-img"
                         alt="VnExpress">
                    <div class="card-body">
                        <h6 class="fw-bold">VnExpress</h6>
                        <p class="small text-muted">
                            Bóng đá phong trào và xu hướng số hóa tại Việt Nam
                        </p>
                        <a href="https://vnexpress.net/bong-da-phong-trao-4543211.html"
                           target="_blank"
                           class="stretched-link"></a>
                    </div>
                </div>
            </div>

            <!-- THANH NIÊN -->
            <div class="col-md-4 fade-in">
                <div class="card press-card card-hover border-0 rounded-4 h-100">
                    <img src="https://images2.thanhnien.vn/thumb_w/640/528068263637045248/2025/12/12/z6841428241258cf62c597e9b85f394a1c164e8e81e381-17655464416661515099017.jpg"
                         class="card-img-top press-img"
                         alt="Thanh Niên">
                    <div class="card-body">
                        <h6 class="fw-bold">Thanh Niên</h6>
                        <p class="small text-muted">
                            Ứng dụng công nghệ trong quản lý sân bóng
                        </p>
                        <a href="https://thanhnien.vn/bong-da-phong-trao-ung-dung-cong-nghe-185231201.htm"
                           target="_blank"
                           class="stretched-link"></a>
                    </div>
                </div>
            </div>

            <!-- TUỔI TRẺ -->
            <div class="col-md-4 fade-in">
                <div class="card press-card card-hover border-0 rounded-4 h-100">
                    <img src="https://cdn2.tuoitre.vn/thumb_w/730/471584752817336320/2025/12/16/base64-17658784022591266149878.jpeg"
                         class="card-img-top press-img"
                         alt="Tuổi Trẻ">
                    <div class="card-body">
                        <h6 class="fw-bold">Tuổi Trẻ</h6>
                        <p class="small text-muted">
                            Bóng đá cộng đồng và lợi ích xã hội
                        </p>
                        <a href="https://tuoitre.vn/bong-da-cong-dong-va-loi-ich-xa-hoi-2023.htm"
                           target="_blank"
                           class="stretched-link"></a>
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
        <a href="../pages/fields.php" class="btn btn-light btn-lg px-5">
            Đặt sân ngay
        </a>
    </div>
</div>

<!-- =======================
        JS FADE IN
======================= -->
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
<br>
<br>
<br>
<br>
<br>
`
@endsection
