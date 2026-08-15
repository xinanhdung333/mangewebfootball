@extends('layouts.auth')
@section('content')
<a class="auth-brand" href="{{ route('home') }}"><i class="bi bi-lightning-charge-fill"></i> SPORTSHUB</a>
<h1 class="auth-title">Đặt lại mật khẩu</h1><p class="auth-subtitle">Tạo mật khẩu mới để bảo vệ tài khoản của bạn.</p>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('password.store') }}">@csrf<input type="hidden" name="token" value="{{ $request->route('token') }}"><div class="mb-3"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus></div><div class="mb-3"><label class="form-label">Mật khẩu mới</label><input class="form-control" type="password" name="password" required></div><div class="mb-4"><label class="form-label">Xác nhận mật khẩu</label><input class="form-control" type="password" name="password_confirmation" required></div><button class="btn btn-primary auth-submit w-100">Cập nhật mật khẩu</button></form>
@endsection
