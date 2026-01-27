# Football Booking - Hướng dẫn cài đặt nhanh

## 📝 Bước 1: Import Database

1. Mở **phpMyAdmin** hoặc **MySQL Workbench**
2. Tạo database mới (hoặc để trống để script tự tạo)
3. Import file `database.sql`:
   - Click tab **Import**
   - Chọn file `database.sql`
   - Click **Import**

## ⚙️ Bước 2: Cấu hình

1. Mở file `includes/config.php`
2. Chỉnh sửa các thông số:
```php
define('DB_HOST', 'localhost');     // Địa chỉ MySQL server
define('DB_USER', 'root');          // Username MySQL
define('DB_PASS', '');              // Password MySQL
define('DB_NAME', 'football_booking'); // Tên database
```

## 🌐 Bước 3: Cấu hình Web Server

### Cho Apache:
```
DocumentRoot: C:\Users\Admin\football-booking\
```

### Cho XAMPP:
1. Copy thư mục vào `C:\xampp\htdocs\football-booking`
2. Truy cập: `http://localhost/football-booking`

### Cho Laragon:
1. Copy thư mục vào `C:\laragon\www\football-booking`
2. Truy cập: `http://football-booking.local`

## 🔐 Bước 4: Đăng nhập

### Admin:
- Email: `admin@football.com`
- Password: `admin123`

### Tạo user mới:
- Click "Đăng ký"
- Điền thông tin
- Click "Đăng ký"

## 🎯 Chức năng chính

### Người dùng thường:
- Đăng ký/Đăng nhập
- Xem danh sách sân
- Đặt sân bóng
- Xem lịch sử đặt sân
- Quản lý hồ sơ

### Admin:
- Quản lý sân (thêm/sửa/xóa)
- Quản lý đặt sân (xác nhận/hủy)
- Quản lý người dùng
- Xem thống kê

## ✅ Kiểm tra cài đặt

1. Mở trình duyệt, truy cập: `http://localhost/football-booking`
2. Thử đăng ký tài khoản mới
3. Đăng nhập và đặt sân
4. Đăng nhập admin để xác nhận đơn

## 🐛 Khắc phục lỗi

### Lỗi "Lỗi kết nối database"
- Kiểm tra MySQL đang chạy
- Kiểm tra thông số DB trong `config.php`

### Lỗi 404 (trang không tìm thấy)
- Kiểm tra đường dẫn file
- Kiểm tra cấu hình Web Server

### Lỗi 500 (Internal Server Error)
- Kiểm tra error log PHP
- Kiểm tra quyền truy cập thư mục

## 📞 Hỗ trợ

Nếu có vấn đề, vui lòng kiểm tra:
1. PHP version >= 7.4
2. MySQL đang chạy
3. File config.php có đúng không
4. Database đã import thành công

Chúc bạn sử dụng vui vẻ! 🚀
