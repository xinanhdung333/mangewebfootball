@php $url = request()->getRequestUri(); @endphp

</div>
</main>

@if(strpos($url, 'login') !== false || strpos($url, 'register') !== false || strpos($url, 'admin') !== false)

    <!-- FOOTER ĐƠN GIẢN -->
    <div class="footer-bottom mt-4 pt-3 border-top border-secondary">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-0 small">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="{{ url('/pages/terms.php') }}" class="small text-light me-3">Điều khoản</a>
                <a href="{{ url('/pages/privacy.php') }}" class="small text-light">Chính sách bảo mật</a>
            </div>
        </div>
    </div>

@else

    <footer class="site-footer bg-dark text-light pt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="footer-brand">{{ config('app.name') }}</h5>
                    <p class="small">Đặt sân bóng nhanh chóng, dễ dàng và an toàn. Hỗ trợ đặt sân, thanh toán và quản lý lịch đặt cho người dùng.</p>
                    <div class="social-links mt-3">
                        <a href="#" class="text-light me-3"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-light"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <h6 class="mb-3">Các chức năng chính</h6>
                    <ul class="list-unstyled footer-links">
                        <div class="t">
                            <li><a href="{{ route('visitor.fields') }}">Sân bóng</a></li>
                            <li><a href="{{ route('services.index') }}">Dịch vụ</a></li>
                            <li><a href="{{ route('booking.create') }}">Đặt sân</a></li>
                            <li><a href="{{ route('cart.index') }}">Giỏ hàng</a></li>
                            <li><a href="{{ route('bookings.my') }}">Lịch đặt của tôi</a></li>
                            <li><a href="{{ route('profile') }}">Hồ sơ</a></li>
                        </div>
                    </ul>
                </div>

                <div class="col-md-3 mb-4">
                    <h6 class="mb-3">Nhân viên</h6>
                    <ul class="list-unstyled team-list">
                        <li>Phạm Ngọc Tiến — Giám Đốc</li>
                        <li>Lê Quang Hải — Thư Ký</li>
                        <li>Ngô Tiến Duy - Designer</li>
                        <li>Nguyễn Cao Phong - Bảo Vệ</li>
                        <li>Mai Thế Anh - Thư Ký</li>
                    </ul>
                </div>

                <div class="col-md-2 mb-4">
                    <h6 class="mb-3">Nhà tài trợ</h6>
                    <div class="d-flex flex-column footer-sponsors">
                        <a href="#" class="mb-2">
                            <img src="{{ url('/') }}/assets/images/anh10.jpg" 
                                 alt="Sponsor 1" class="img-fluid rounded sponsor-logo">
                        </a>
                    </div>
                </div>
            </div>

            <div class="footer-bottom mt-4 pt-3 border-top border-secondary">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-0 small">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <a href="{{ url('/pages/terms.php') }}" class="small text-light me-3">Điều khoản</a>
                        <a href="{{ url('/pages/privacy.php') }}" class="small text-light">Chính sách bảo mật</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

@endif

</body>

<style>
.footer-links a {
    color: #fff;
    text-decoration: none;
    position: relative;
    padding-bottom: 3px;
}

.footer-links a::after {
    content: "";
    position: absolute;
    left: 0;
    bottom: 0;
    width: 0%;
    height: 2px;
    background: #00ffcc;
    transition: 0.3s;
}

.footer-links a:hover::after {
    width: 100%;
}
</style>

</html>

