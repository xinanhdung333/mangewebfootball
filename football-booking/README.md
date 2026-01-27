# Football Booking - Hệ thống quản lý đặt sân bóng

Một ứng dụng web PHP thuần hoàn toàn để quản lý đặt sân bóng với giao diện hiện đại.

## ✨ Tính năng

### Cho người dùng
- ✅ Đăng ký tài khoản mới
- ✅ Đăng nhập/Đăng xuất
- ✅ Xem danh sách sân bóng
- ✅ Đặt sân bóng
- ✅ Xem lịch sử đặt sân
- ✅ Quản lý hồ sơ cá nhân
- ✅ Hủy đặt sân

### Cho quản lý viên
- ✅ Quản lý sân bóng (thêm, sửa, xóa)
- ✅ Quản lý đặt sân (xác nhận, hủy)
- ✅ Quản lý người dùng
- ✅ Thống kê doanh thu
- ✅ Xem báo cáo theo tháng

## 🛠️ Công nghệ sử dụng

- **Backend**: PHP (thuần, không framework)
- **Database**: MySQL
- **Frontend**: HTML5, Bootstrap 5, CSS3
- **Security**: Password hashing (bcrypt), Prepared Statements

## 📋 Yêu cầu

- PHP >= 7.4
- MySQL >= 5.7
- Web Server (Apache, Nginx, IIS)

## 🚀 Hướng dẫn cài đặt

### Bước 1: Chuẩn bị Database
```sql
-- Mở MySQL command line hoặc phpMyAdmin
-- Chạy file database.sql
mysql -u root -p < database.sql
```

### Bước 2: Cấu hình
1. Chỉnh sửa file `includes/config.php`
2. Thay đổi các thông số kết nối database:
   - `DB_HOST`: localhost (hoặc địa chỉ server)
   - `DB_USER`: root (hoặc username của bạn)
   - `DB_PASS`: (password MySQL)
   - `DB_NAME`: football_booking

### Bước 3: Đặt thư mục vào Web Server
- Copy toàn bộ thư mục `football-booking` vào `htdocs` (Apache) hoặc thư mục công khai của server

### Bước 4: Truy cập ứng dụng
```
http://localhost/football-booking
```

## 👤 Tài khoản mẫu

### Admin
- Email: `admin@football.com`
- Password: `admin123` (thay đổi sau khi đăng nhập)

## 📁 Cấu trúc thư mục

```
football-booking/
├── index.php                  # Trang chủ
├── includes/
│   ├── config.php            # Cấu hình database
│   ├── functions.php         # Các hàm tiện ích
│   ├── header.php            # Header
│   └── footer.php            # Footer
├── pages/
│   ├── login.php             # Đăng nhập
│   ├── register.php          # Đăng ký
│   ├── logout.php            # Đăng xuất
│   ├── dashboard.php         # Dashboard người dùng
│   ├── fields.php            # Danh sách sân
│   ├── booking.php           # Đặt sân
│   ├── booking-detail.php    # Chi tiết đặt sân
│   ├── my-bookings.php       # Lịch sử đặt sân
│   ├── profile.php           # Hồ sơ cá nhân
│   └── admin/                # Các trang quản lý
│       ├── manage-fields.php     # Quản lý sân
│       ├── manage-bookings.php   # Quản lý đặt sân
│       ├── manage-users.php      # Quản lý người dùng
│       └── statistics.php        # Thống kê
├── assets/
│   ├── css/
│   │   └── style.css         # Stylesheet chính
│   ├── js/
│   │   └── main.js           # JavaScript chính
│   └── images/               # Thư mục hình ảnh
├── database.sql              # File SQL tạo database
└── README.md                 # File này
```

## 🔒 Bảo mật

- ✅ Mật khẩu được mã hóa bằng bcrypt
- ✅ SQL Injection được ngăn chặn bằng Prepared Statements
- ✅ Session management an toàn
- ✅ CSRF protection (có thể thêm tokens)
- ✅ Input validation trên server

## 💡 Mở rộng trong tương lai

- [ ] Thanh toán online (VNPay, Momo)
- [ ] Email confirmation
- [ ] OTP verification
- [ ] Reviews & ratings
- [ ] Notification system
- [ ] Mobile app
- [ ] API RESTful

## 📞 Liên hệ

Email: admin@football-booking.com

## 📜 License

This project is open source and available under the MIT License.

---

**Tạo bởi**: Football Booking Team  
**Phiên bản**: 1.0.0  
**Cập nhật lần cuối**: 12/11/2025
