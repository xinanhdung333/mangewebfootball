@extends('layouts.auth')
@section('content')
<a class="auth-brand" href="{{ route('home') }}"><i class="bi bi-lightning-charge-fill"></i> SPORTSHUB</a>
<h1 class="auth-title">Tạo tài khoản</h1><p class="auth-subtitle">Tham gia SportsHub để quản lý sân và mua sắm dễ dàng.</p>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('register') }}">@csrf
<div class="mb-3"><label class="form-label">Họ và tên</label><input class="form-control" name="name" value="{{ old('name') }}" required></div><div class="mb-3"><label class="form-label">Email</label><input class="form-control" name="email" type="email" value="{{ old('email') }}" required></div><div class="mb-3"><label class="form-label">Số điện thoại</label><input class="form-control" name="phone" value="{{ old('phone') }}" required></div><div class="mb-3"><label class="form-label">Mật khẩu</label><input class="form-control" name="password" type="password" required></div><div class="mb-4"><label class="form-label">Xác nhận mật khẩu</label><input class="form-control" name="password_confirmation" type="password" required></div><button class="btn btn-primary auth-submit w-100">Đăng ký</button></form>
<p class="text-center auth-note mt-4 mb-0">Đã có tài khoản? <a class="auth-link" href="{{ route('login') }}">Đăng nhập</a></p>
@endsection
