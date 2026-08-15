@extends('layouts.auth')
@section('content')
<a class="auth-brand" href="{{ route('home') }}"><i class="bi bi-lightning-charge-fill"></i> SPORTSHUB</a>
<h1 class="auth-title">Xác nhận mật khẩu</h1><p class="auth-subtitle">Vui lòng nhập mật khẩu để tiếp tục thao tác an toàn.</p>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('password.confirm') }}">@csrf<div class="mb-4"><label class="form-label">Mật khẩu</label><input class="form-control" type="password" name="password" required autofocus></div><button class="btn btn-primary auth-submit w-100">Xác nhận</button></form>
@endsection
