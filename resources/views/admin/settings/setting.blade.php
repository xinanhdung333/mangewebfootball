@extends('layouts.app')

@section('content')

<style>
body {
    background: #f4f6f9;
}

/* Wrapper */
.settings-wrapper {
    display: flex;
    max-width: 1200px;
    margin:    0px auto;
    gap: 20px;
}

/* Sidebar */
.sidebar {
    width: 260px;
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    height: fit-content;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.sidebar h2 {
    font-size: 18px;
    margin-bottom: 20px;
}

.sidebar a {
    display: block;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 6px;
    color: #333;
    text-decoration: none;
    transition: 0.2s;
}

.sidebar a:hover {
    background: #f1f2f6;
}

.sidebar a.active {
    background: #ee4d2d;
    color: #fff;
}

/* Content */
.content {
    flex: 1;
}

/* Section card */
.section {
    background: #fff;
    border-radius: 14px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}

.section h3 {
    font-size: 20px;
    margin-bottom: 15px;
}

/* Item */
.item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    border-bottom: 1px solid #eee;
    border-radius: 8px;
    transition: 0.2s;
}

.item:hover {
    background: #f9f9f9;
}

.item:last-child {
    border-bottom: none;
}

.item-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.item i {
    color: #ee4d2d;
    font-size: 18px;
}

/* Toggle */
.switch {
    position: relative;
    width: 42px;
    height: 22px;
}

.switch input {
    display: none;
}

.slider {
    position: absolute;
    background: #ccc;
    border-radius: 20px;
    inset: 0;
    cursor: pointer;
    transition: 0.3s;
}

.slider:before {
    content: "";
    position: absolute;
    width: 18px;
    height: 18px;
    left: 2px;
    top: 2px;
    background: #fff;
    border-radius: 50%;
    transition: 0.3s;
}

input:checked + .slider {
    background: #ee4d2d;
}

input:checked + .slider:before {
    transform: translateX(20px);
}
.settings-page .container {
    max-width: 100% !important;
    padding: 0 !important;
}
</style>
<div class="settings-page">

<div class="settings-wrapper">

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Cài đặt</h2>
        <a href="#" class="active">Bảo mật</a>
        <a href="#">Giao diện</a>
        <a href="#">Thông báo</a>
    </div>

    <!-- Content -->
    <div class="content">

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <!-- Giao hàng -->
        <div class="section">
            <h3>Giao hàng & Vận chuyển</h3>
            <form action="{{ route('admin.settings.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-bold">Địa chỉ cửa hàng (Shop Address)</label>
                    <div class="input-group">
                        <input type="text" id="shop_address" name="shop_address" class="form-control" placeholder="Ví dụ: 1 Đại Cồ Việt, Hai Bà Trưng, Hà Nội" value="{{ old('shop_address', $settings['shop_address'] ?? '') }}" required>
                        <button type="button" class="btn btn-outline-secondary" id="btn-geocode" style="border-radius: 0 4px 4px 0;">
                            <i class="bi bi-search"></i> Tìm toạ độ
                        </button>
                    </div>
                    <small class="text-muted" id="geocode-msg">Bấm "Tìm toạ độ" để tự động điền Vĩ độ & Kinh độ bên dưới.</small>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Vĩ độ (Latitude)</label>
                        <input type="text" id="shop_lat" name="shop_lat" class="form-control" value="{{ old('shop_lat', $settings['shop_lat'] ?? '21.0285') }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Kinh độ (Longitude)</label>
                        <input type="text" id="shop_lng" name="shop_lng" class="form-control" value="{{ old('shop_lng', $settings['shop_lng'] ?? '105.8542') }}" required>
                    </div>
                    <div class="col-12 mb-3 mt-n2">
                        <small class="text-warning"><i class="bi bi-info-circle"></i> Chỉ chỉnh sửa toạ độ nếu hệ thống tìm địa chỉ không chính xác. <a href="https://www.latlong.net/" target="_blank">Lấy toạ độ thủ công tại đây</a>.</small>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Phí giao hàng mỗi km (VNĐ)</label>
                    <input type="number" name="shipping_fee_per_km" class="form-control" value="{{ old('shipping_fee_per_km', $settings['shipping_fee_per_km'] ?? '15000') }}" required min="0">
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-primary" style="background:#ee4d2d; border-color:#ee4d2d;">Lưu cấu hình</button>
                    <a href="{{ route('admin.shipping-methods.index') }}" class="btn btn-outline-secondary">Quản lý đơn vị vận chuyển</a>
                </div>
            </form>
        </div>

        <!-- Trang Giới thiệu -->
        <div class="section">
            <h3>Trang Giới thiệu</h3>
            <a href="{{ route('admin.settings.about') }}" style="text-decoration:none; color:inherit;">
                <div class="item">
                    <div class="item-left">
                        <i class="bi bi-pencil-square"></i>
                        Chỉnh sửa nội dung trang giới thiệu
                    </div>
                    <i class="bi bi-chevron-right"></i>
                </div>
            </a>
        </div>

        <!-- Khuyến mãi -->
        <div class="section">
            <h3>Khuyến mãi</h3>
            <a href="{{ route('admin.vouchers.index') }}" style="text-decoration:none; color:inherit;">
                <div class="item">
                    <div class="item-left">
                        <i class="bi bi-ticket-perforated"></i>
                        Quản lý mã giảm giá (Voucher)
                    </div>
                    <i class="bi bi-chevron-right"></i>
                </div>
            </a>
        </div>

        <!-- Bảo mật -->
        <div class="section">
            <h3>Bảo mật</h3>
                    <a href ="{{route('admin.settings.pricing')}}">

            <div class="item">
                <div class="item-left">
                    <i class="bi bi-phone"></i>
                    Chỉnh sửa tăng giảm giá , ưu đãi
                </div>
                <i class="bi bi-chevron-right"></i>

            </div>
</a>


            <div class="item">
                <div class="item-left">
                    <i class="bi bi-shield-lock"></i>
                    Thiết lập Digital OTP
                </div>
                <i class="bi bi-chevron-right"></i>
            </div>

            <div class="item">
                <div class="item-left">
                    <i class="bi bi-person-bounding-box"></i>
                    Xác thực khuôn mặt
                </div>
                <i class="bi bi-chevron-right"></i>
            </div>

            <div class="item">
                <div class="item-left">
                    <i class="bi bi-fingerprint"></i>
                    Đăng nhập Face ID
                </div>

                <label class="switch">
                    <input type="checkbox" checked>
                    <span class="slider"></span>
                </label>
            </div>

        </div>

        <!-- Giao diện -->
        <div class="section">
            <h3>Giao diện</h3>

            <div class="item">
                <div class="item-left">
                    <i class="bi bi-palette"></i>
                    Đổi theme
                </div>
                <i class="bi bi-chevron-right"></i>
            </div>

            <div class="item">
                <div class="item-left">
                    <i class="bi bi-image"></i>
                    Ảnh nền cá nhân
                </div>
                <i class="bi bi-chevron-right"></i>
            </div>

        </div>

    </div>

</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnGeocode = document.getElementById('btn-geocode');
    const inputAddress = document.getElementById('shop_address');
    const inputLat = document.getElementById('shop_lat');
    const inputLng = document.getElementById('shop_lng');
    const msg = document.getElementById('geocode-msg');

    btnGeocode.addEventListener('click', function() {
        const address = inputAddress.value.trim();
        if(!address) {
            msg.innerHTML = '<span class="text-danger">Vui lòng nhập địa chỉ trước.</span>';
            return;
        }

        btnGeocode.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        btnGeocode.disabled = true;
        msg.innerHTML = '<span class="text-info">Đang tìm kiếm...</span>';

        fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(address))
            .then(res => res.json())
            .then(data => {
                if (data && data.length > 0) {
                    inputLat.value = data[0].lat;
                    inputLng.value = data[0].lon;
                    msg.innerHTML = '<span class="text-success fw-bold"><i class="bi bi-check-circle"></i> Đã tìm thấy toạ độ!</span>';
                } else {
                    msg.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> Không tìm thấy địa chỉ này trên bản đồ. Bạn hãy thử nhập địa chỉ ngắn gọn hơn (vd: Quận/Thành phố) hoặc nhập toạ độ thủ công.</span>';
                }
            })
            .catch(err => {
                msg.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> Lỗi kết nối đến bản đồ.</span>';
            })
            .finally(() => {
                btnGeocode.innerHTML = '<i class="bi bi-search"></i> Tìm toạ độ';
                btnGeocode.disabled = false;
            });
    });
});
</script>

@endsection