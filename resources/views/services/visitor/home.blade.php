@extends('layouts.app')

@section('content')
    
<style>
    .feature-box:hover {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

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

/* Màu icon */
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
    background-image: url('../assets/images/2340596.jpg') !important;
    background-size: cover;
    background-position: center;
}

.hero-title {
    font-size: clamp(24px, 5vw, 48px);
    font-weight: 700;
}
.hero-btn {
    padding: 10px 24px;
    font-size: 16px;
    border-radius: 8px;
}

@media (max-width: 576px) {
    .hero-btn {
        padding: 8px 16px;
        font-size: 14px;
    }

    .feature-box {
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 12px;
    }
    .feature-icon {
        width: 50px;
        height: 50px;
        font-size: 22px;
        margin-bottom: 10px;
    }
    .feature-title {
        font-size: 18px;
        margin-bottom: 5px;
    }
    .feature-text {
        font-size: 13px;
        line-height: 1.4;
    }
}

/* Sản phẩm CSS (Desktop grid, Mobile masonry) */
.home-product-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
}
.home-product-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: transform 0.2s;
}
.home-product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.12);
}
.home-product-card img {
    width: 100%;
    height: 160px;          /* desktop: chiều cao cố định */
    object-fit: cover;
    border-bottom: 1px solid #eee;
    display: block;
}
.home-product-info {
    padding: 10px;
}
.home-product-title {
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 6px;
    line-height: 1.4;
    color: #333;
}
.home-product-price {
    color: #ee4d2d;
    font-weight: bold;
    font-size: 15px;
}

@media (max-width: 768px) {
    .home-product-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 576px) {
    .home-product-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }
    .home-product-card img {
        height: auto;         /* bỏ height cứng */
        aspect-ratio: 1 / 1;  /* ảnh vuông đều nhau */
    }
    .home-product-title {
        font-size: 12px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .home-product-price {
        font-size: 13px;
    }
    .home-product-info {
        padding: 6px 8px;
    }
}
</style>

 <div class="container mt-4">
        <div class="row">
        <div class="col-md-12">
                <div class="jumbotron bg-light rounded text-white p-4 p-md-5" style="background-color: rgba(0,0,0,0.35);">
                    <h1 class="hero-title">Chào mừng tới SportsHub</h1>
                    <p class="lead mb-3" style="font-size:clamp(15px,3.5vw,20px);">Đặt sân bóng nhanh chóng, dễ dàng và an toàn</p>
                    <hr class="my-3">
                    <div class="d-flex flex-wrap gap-3">
                        <a href="{{ url('login') }}" class="btn btn-primary hero-btn">
                            <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-outline-primary hero-btn bg-white text-primary">
                            <i class="bi bi-person-plus"></i> Đăng ký
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sản phẩm nổi bật -->
    <div class="container mt-5 mb-5">
        <h2 class="mb-4 text-center">Sản phẩm & Dịch vụ nổi bật</h2>
        <div class="home-product-grid">
            <!-- Sản phẩm 1 -->
            <div class="home-product-card">
                <img src="{{ asset('assets/images/banner.jpg') }}" alt="Product">
                <div class="home-product-info">
                    <div class="home-product-title">Giày đá bóng chính hãng siêu nhẹ đinh TF cao cấp (Mẫu mới)</div>
                    <div class="home-product-price">1.500.000đ</div>
                </div>
            </div>
            
            <!-- Sản phẩm 2 (Ngắn hơn) -->
            <div class="home-product-card">
                <img src="{{ asset('assets/images/2340596.jpg') }}" alt="Product">
                <div class="home-product-info">
                    <div class="home-product-title">Tất chống trơn</div>
                    <div class="home-product-price">50.000đ</div>
                </div>
            </div>

            <!-- Sản phẩm 3 -->
            <div class="home-product-card">
                <img src="{{ asset('assets/images/banner.jpg') }}" alt="Product">
                <div class="home-product-info">
                    <div class="home-product-title">Áo bóng đá câu lạc bộ Real Madrid mùa giải mới chất vải thun lạnh co giãn 4 chiều</div>
                    <div class="home-product-price">250.000đ</div>
                </div>
            </div>

            <!-- Sản phẩm 4 -->
            <div class="home-product-card">
                <img src="{{ asset('assets/images/2340596.jpg') }}" alt="Product">
                <div class="home-product-info">
                    <div class="home-product-title">Băng quấn cổ chân bảo vệ</div>
                    <div class="home-product-price">120.000đ</div>
                </div>
            </div>

            <!-- Sản phẩm 5 -->
            <div class="home-product-card">
                <img src="{{ asset('assets/images/banner.jpg') }}" alt="Product">
                <div class="home-product-info">
                    <div class="home-product-title">Găng tay thủ môn có xương chống lật ngón cực xịn</div>
                    <div class="home-product-price">450.000đ</div>
                </div>
            </div>

            <!-- Sản phẩm 6 -->
            <div class="home-product-card">
                <img src="{{ asset('assets/images/2340596.jpg') }}" alt="Product">
                <div class="home-product-info">
                    <div class="home-product-title">Quả bóng đá số 5 tiêu chuẩn FIFA</div>
                    <div class="home-product-price">350.000đ</div>
                </div>
            </div>
            
            <!-- Sản phẩm 7 -->
            <div class="home-product-card">
                <img src="{{ asset('assets/images/banner.jpg') }}" alt="Product">
                <div class="home-product-info">
                    <div class="home-product-title">Bình nước thể thao 1L nhựa Tritan</div>
                    <div class="home-product-price">80.000đ</div>
                </div>
            </div>

            <!-- Sản phẩm 8 -->
            <div class="home-product-card">
                <img src="{{ asset('assets/images/2340596.jpg') }}" alt="Product">
                <div class="home-product-info">
                    <div class="home-product-title">Túi đựng giày 2 ngăn chống nước, tiện lợi mang theo đồ đi tập</div>
                    <div class="home-product-price">180.000đ</div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endpush
@endsection
