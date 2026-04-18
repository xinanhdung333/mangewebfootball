# Football Field Booking & Service Management System

## Giới thiệu

Đây là hệ thống **đặt sân bóng và quản lý dịch vụ đi kèm** được xây dựng bằng **Laravel**. Người dùng có thể đặt sân theo khung giờ, mua dịch vụ bổ sung, thanh toán online qua MoMo hoặc tiền mặt. Hệ thống có khu vực quản trị để quản lý sân, booking, đơn hàng, dịch vụ và người dùng.

Project phù hợp làm:

* Đồ án môn học
* Portfolio backend Laravel
* Demo hệ thống booking thực tế quy mô nhỏ

---

## Tính năng chính

### Người dùng (User)

* Đăng ký / đăng nhập
* Xem danh sách sân bóng
* Đặt sân theo ngày và khung giờ
* Chọn dịch vụ đi kèm khi đặt sân
* Mua dịch vụ riêng (order dịch vụ)
* Thanh toán bằng:

  * MoMo
  * Tiền mặt
* Xem lịch sử booking
* Xem lịch sử đơn hàng
* Gửi feedback

---

### Quản trị viên (Admin)

* Quản lý sân bóng
* Quản lý booking
* Quản lý dịch vụ
* Quản lý đơn hàng dịch vụ
* Quản lý thanh toán
* Quản lý feedback

---

### Boss (Super Admin)

Có toàn quyền như Admin và thêm:

* Thêm người dùng
* Sửa người dùng
* Xóa người dùng
* Quản lý role hệ thống

---

## Các chức năng nâng cao

Project đã triển khai nhiều chức năng nâng cao thường có trong hệ thống booking thực tế:

* Kiểm tra trùng giờ đặt sân realtime bằng AJAX
* Notification khi booking thành công
* Quản lý trạng thái thanh toán (pending / success / failed)
* Quản lý tồn kho dịch vụ (service inventory)
* Dịch vụ đi kèm khi đặt sân
* Order dịch vụ riêng biệt
* Thanh toán MoMo integration
* Phân quyền theo role: guest / user / admin / boss
* Thống kê doanh thu trong admin dashboard
* Export hóa đơn PDF
* Chatbot hỗ trợ người dùng (có khả năng training dữ liệu)
* Đổi mật khẩu qua email (password reset email)

---

## Công nghệ sử dụng

Backend:

* Laravel
* PHP
* MySQL

Frontend:

* Blade Template Engine
* Bootstrap
* JavaScript

Thanh toán:

* MoMo Payment Gateway

---

## Cấu trúc hệ thống dữ liệu chính

Một số bảng quan trọng trong hệ thống:

* users
* roles
* fields
* bookings
* booking_services
* services
* orders
* order_items
* booking_payments
* payments
* feedback

---

## Cài đặt project

### Bước 1: Clone project

```
 git clone https://github.com/xinanhdung333/mangewebfootball.git
```

### Bước 2: Di chuyển vào thư mục project

```
 cd mangewebfootball
```

### Bước 3: Cài đặt thư viện

```
 composer install
```

### Bước 4: Tạo file môi trường

```
 cp .env.example .env
```

### Bước 5: Generate key

```
 php artisan key:generate
```

### Bước 6: Cấu hình database trong file .env

Ví dụ:

```
 DB_DATABASE=football_booking
 DB_USERNAME=root
 DB_PASSWORD=
```

### Bước 7: Chạy migration

```
 php artisan migrate
```

### Bước 8: Chạy server

```
 php artisan serve
```

Truy cập:

```
 http://127.0.0.1:8000
```

---

## Thanh toán MoMo

Project hỗ trợ thanh toán MoMo sandbox.

Cấu hình trong file:

```
 config/momo.php
```

hoặc

```
 .env
```

---

## Tài khoản demo (gợi ý thêm nếu dùng seed)

Boss:

```
 email: boss@example.com
 password: 123456
```

Admin:

```
 email: admin@example.com
 password: 123456
```

User:

```
 email: user@example.com
 password: 123456
```

(Có thể thay đổi trong database nếu chưa seed tự động)

---




---

## Hướng phát triển thêm

Một số hướng nâng cấp trong tương lai:

* Tích hợp AI


---

## Tác giả

Sinh viên thực hiện project Laravel booking sân bóng phục vụ mục đích học tập và portfolio.

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
