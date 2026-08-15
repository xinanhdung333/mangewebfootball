@extends('layouts.auth')
@section('content')
<a class="auth-brand" href="{{ route('home') }}"><i class="bi bi-lightning-charge-fill"></i> SPORTSHUB</a>
<h1 class="auth-title">Xác thực email</h1><p class="auth-subtitle">Chúng tôi đã gửi liên kết xác thực đến email của bạn. Hãy mở email và nhấp vào liên kết để hoàn tất.</p>
@if(session('status') === 'verification-link-sent')<div class="alert alert-success">Liên kết xác thực mới đã được gửi.</div>@endif
<form method="POST" action="{{ route('verification.send') }}">@csrf<button class="btn btn-primary auth-submit w-100">Gửi lại email xác thực</button></form><form class="text-center mt-3" method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-link auth-link">Đăng xuất</button></form>
@endsection
