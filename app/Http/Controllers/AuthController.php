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

            if ($user->role === 'admin') return redirect('/admin/statistics');
            if ($user->role === 'boss') return redirect('/boss/statistics');
            if ($user->role === 'user') return redirect('user/dashboard');

            return redirect('/dashboard');
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
        'email' => 'required|email:rfc,dns|unique:users,email',
        'phone' => 'required|string|max:20',
        'password' => 'required|string|min:6|confirmed',
    ]);

    $id = DB::table('users')->max('id') + 1;

    User::create([
        'id' => $id,
        'name' => $data['name'],
        'email' => $data['email'],
        'phone' => $data['phone'],
        'password' => Hash::make($data['password']),
        'role' => 'user',
    ]);

    return redirect()->route('login')
        ->with('success','Đăng ký thành công');
}
 
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}