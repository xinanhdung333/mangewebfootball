@extends('layouts.auth')
@section('content')
<a class="auth-brand" href="{{ route('home') }}"><i class="bi bi-lightning-charge-fill"></i> SPORTSHUB</a>
<h1 class="auth-title">Tạo tài khoản</h1><p class="auth-subtitle">Tham gia SportsHub để quản lý sân và mua sắm dễ dàng.</p>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div id="auth-fetch-error" class="alert alert-danger d-none"></div>
<form method="POST" action="{{ route('register') }}" id="register-form">@csrf
<div class="mb-3"><label class="form-label">Họ và tên</label><input class="form-control" name="name" value="{{ old('name') }}" required></div><div class="mb-3"><label class="form-label">Email</label><input class="form-control" name="email" type="email" value="{{ old('email') }}" required></div><div class="mb-3"><label class="form-label">Số điện thoại</label><input class="form-control" name="phone" value="{{ old('phone') }}" required></div><div class="mb-3"><label class="form-label">Mật khẩu</label><input class="form-control" name="password" type="password" required></div><div class="mb-4"><label class="form-label">Xác nhận mật khẩu</label><input class="form-control" name="password_confirmation" type="password" required></div><button class="btn btn-primary auth-submit w-100">Đăng ký</button></form>
<p class="text-center auth-note mt-4 mb-0">Đã có tài khoản? <a class="auth-link" href="{{ route('login') }}">Đăng nhập</a></p>
@push('scripts')<script>
document.getElementById('register-form').addEventListener('submit', async function (event) {
    event.preventDefault();
    const box = document.getElementById('auth-fetch-error');
    const button = this.querySelector('button[type="submit"], button:not([type])');
    const originalText = button.innerHTML;
    box.classList.add('d-none');
    button.disabled = true;
    button.innerHTML = 'Dang dang ky...';

    try {
        const response = await fetch(this.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: new FormData(this),
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            const message = data.errors ? Object.values(data.errors).flat().join('<br>') : (data.message || 'Dang ky that bai');
            throw new Error(message);
        }
        window.location.href = data.redirect_url || "{{ route('login') }}";
    } catch (error) {
        box.innerHTML = error.message;
        box.classList.remove('d-none');
        button.disabled = false;
        button.innerHTML = originalText;
    }
});
</script>@endpush
@endsection
