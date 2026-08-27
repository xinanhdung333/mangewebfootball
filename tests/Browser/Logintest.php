<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** Test đăng nhập thành công */
    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email'    => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->type('email', 'test@example.com')
                ->type('password', 'password123')
                ->press('Login')
                ->waitForLocation('/dashboard') // chờ redirect sau login
                ->assertPathIs('/dashboard')
                ->assertSee('Dashboard'); // hoặc text bất kỳ chỉ hiện sau khi login
        });
    }

    /** Test đăng nhập sai mật khẩu */
    public function test_user_cannot_login_with_invalid_password()
    {
        $user = User::factory()->create([
            'email'    => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'test@example.com')
                ->type('password', 'wrong-password')
                ->press('Login')
                ->waitForText('These credentials do not match our records.')
                ->assertPathIs('/login'); // vẫn ở lại trang login
        });
    }

    /** Test validate field trống */
    public function test_login_requires_email_and_password()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->press('Login')
                ->waitForText('The email field is required.')
                ->assertSee('The password field is required.');
        });
    }

    /** Test đăng xuất */
    public function test_user_can_logout()
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user) // login thẳng không qua form, dùng cho test khác không phải test login
                ->visit('/dashboard')
                ->press('Logout')
                ->assertPathIs('/login');
        });
    }
}