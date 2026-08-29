<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use RefreshDatabase; // Tự động xóa dữ liệu test sau mỗi lần chạy

    // ✅ TEST 1: Đăng nhập thành công
    public function test_user_can_login_with_correct_credentials()
    {
        // Tạo user ảo trong DB test
        $user = User::factory()->create([
            'email' => 'sinhvien@example.com',
            'password' => bcrypt('12345678'),
        ]);

        // Gửi request POST đến route /login
        $response = $this->post('/login', [
            'email' => 'sinhvien@example.com',
            'password' => '12345678',
        ]);

        // Kiểm tra: Chuyển hướng về trang chủ
        $response->assertRedirect('/home');
        
        // Kiểm tra: Đã đăng nhập thành công
        $this->assertAuthenticatedAs($user);
    }

    // ❌ TEST 2: Sai mật khẩu
    public function test_user_cannot_login_with_wrong_password()
    {
        User::factory()->create([
            'email' => 'sinhvien@example.com',
            'password' => bcrypt('12345678'),
        ]);

        $response = $this->post('/login', [
            'email' => 'sinhvien@example.com',
            'password' => 'sai_mat_khau',
        ]);

        // Kiểm tra: Có lỗi validation cho field 'email'
        $response->assertSessionHasErrors('email');
        
        // Kiểm tra: Vẫn chưa đăng nhập
        $this->assertGuest();
    }

    // ❌ TEST 3: Email không tồn tại
    public function test_user_cannot_login_with_nonexistent_email()
    {
        $response = $this->post('/login', [
            'email' => 'khongton tai@example.com',
            'password' => '12345678',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // ❌ TEST 4: Bỏ trống email
    public function test_login_fails_when_email_is_empty()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => '12345678',
        ]);

        // Kiểm tra lỗi validation
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // ❌ TEST 5: Bỏ trống mật khẩu
    public function test_login_fails_when_password_is_empty()
    {
        User::factory()->create([
            'email' => 'sinhvien@example.com',
            'password' => bcrypt('12345678'),
        ]);

        $response = $this->post('/login', [
            'email' => 'sinhvien@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }
}