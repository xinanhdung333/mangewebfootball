<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;


class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
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
