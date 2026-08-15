@extends('layouts.auth')
@section('content')
<a class="auth-brand" href="{{ route('home') }}"><i class="bi bi-lightning-charge-fill"></i> SPORTSHUB</a>
<h1 class="auth-title">Quên mật khẩu?</h1><p class="auth-subtitle">Nhập email để nhận liên kết đặt lại mật khẩu.</p>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif @error('email')<div class="alert alert-danger">{{ $message }}</div>@enderror
<form method="POST" action="{{ route('password.email') }}">@csrf<div class="mb-4"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="{{ old('email') }}" required autofocus></div><button class="btn btn-primary auth-submit w-100">Gửi liên kết</button></form><p class="text-center auth-note mt-4 mb-0"><a class="auth-link" href="{{ route('login') }}"><i class="bi bi-arrow-left"></i> Quay lại đăng nhập</a></p>
@endsection
