<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;


class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function redirectToProvider(string $provider)
    {
        abort_unless(in_array($provider, ['google', 'facebook'], true), 404);
        $this->ensureSocialProviderConfigured($provider);

        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback(string $provider, Request $request)
    {
        abort_unless(in_array($provider, ['google', 'facebook'], true), 404);

        $socialUser = Socialite::driver($provider)->user();
        if (!$socialUser->getEmail()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Không thể lấy email từ tài khoản mạng xã hội. Vui lòng cấp quyền email rồi thử lại.',
            ]);
        }

        $providerColumn = $provider . '_id';
        $user = User::where($providerColumn, $socialUser->getId())->first();

        if (!$user && $socialUser->getEmail()) {
            $user = User::where('email', $socialUser->getEmail())->first();
        }

        if ($user) {
            $user->update([
                $providerColumn => $socialUser->getId(),
                'avt' => $socialUser->getAvatar() ?: $user->avt,
            ]);
        } else {
            $user = User::create([
                'id' => DB::table('users')->max('id') + 1,
                'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: 'SportsHub User',
                'email' => $socialUser->getEmail(),
                'phone' => null,
                'password' => Hash::make(Str::random(40)),
                'role' => 'user',
                'avt' => $socialUser->getAvatar(),
                $providerColumn => $socialUser->getId(),
            ]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect($this->redirectUrlFor($user));
    }

    private function redirectUrlFor(User $user): string
    {
        return match ($user->role) {
            'admin' => url('/admin/statistics'),
            'boss' => url('/boss/statistics'),
            default => route('dashboard'),
        };
    }

    private function ensureSocialProviderConfigured(string $provider): void
    {
        $config = config("services.{$provider}");

        abort_if(
            blank($config['client_id'] ?? null) || blank($config['client_secret'] ?? null),
            503,
            "Đăng nhập bằng " . ucfirst($provider) . " chưa được cấu hình. Vui lòng bổ sung Client ID và Client Secret trong file .env."
        );
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required']
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->role === 'admin') {
                $redirectUrl = url('/admin/statistics');
            } elseif ($user->role === 'boss') {
                $redirectUrl = url('/boss/statistics');
            } elseif ($user->role === 'user') {
                $redirectUrl = route('dashboard');
            } else {
                $redirectUrl = route('dashboard');
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Login successful',
                    'redirect_url' => $redirectUrl,
                ]);
            }

            return redirect($redirectUrl);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Email hoac mat khau khong chinh xac',
            ], 422);
        }

        return back()->withErrors([
            'email' => 'Email hoặc mật khẩu không chính xác'
        ])->withInput();
    }

    public function showRegister()
    {
        return view('auth.register');
    }

public function register(Request $request)
{
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'phone' => 'nullable|string|max:20|unique:users,phone',
        'password' => 'required|string|min:6|confirmed',
    ]);

    $id = DB::table('users')->max('id') + 1;

    $user = User::create([
        'id' => $id,
        'name' => $data['name'],
        'email' => $data['email'],
        'phone' => $data['phone'] ?? '',
        'password' => Hash::make($data['password']),
        'role' => 'user',
    ]);

    Auth::login($user);
    $request->session()->regenerate();

    if ($request->expectsJson()) {
        return response()->json([
            'message' => 'Register successful',
            'redirect_url' => route('dashboard'),
        ], 201);
    }

    return redirect()->route('dashboard')
        ->with('success','Đăng ký thành công');
}
 
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Logged out',
                'redirect_url' => route('home'),
            ]);
        }

        return redirect()->route('home');
    }
}
