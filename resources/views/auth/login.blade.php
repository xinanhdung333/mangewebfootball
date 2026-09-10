@extends('layouts.auth')
@section('content')
<a class="auth-brand" href="{{ route('home') }}"><i class="bi bi-lightning-charge-fill"></i> SPORTSHUB</a>
<h1 class="auth-title">Chào mừng trở lại</h1><p class="auth-subtitle">Đăng nhập để tiếp tục trải nghiệm thể thao cùng SportsHub.</p>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div id="auth-fetch-error" class="alert alert-danger d-none"></div>
<form method="POST" action="{{ route('login.post') }}" id="login-form">@csrf
<div class="mb-3"><label class="form-label" for="email">Email</label><div class="input-group"><span class="input-group-text bg-white border-end-0"><i class="bi bi-envelope"></i></span><input id="email" class="form-control border-start-0" name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus></div></div>
<div class="mb-3"><label class="form-label" for="password">Mật khẩu</label><div class="input-group"><span class="input-group-text bg-white border-end-0"><i class="bi bi-lock"></i></span><input id="password" class="form-control border-start-0 border-end-0" name="password" type="password" placeholder="Nhập mật khẩu" required><button class="btn btn-outline-secondary border-start-0" type="button" id="togglePassword"><i class="bi bi-eye"></i></button></div></div>
<div class="d-flex justify-content-between align-items-center mb-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="remember" id="remember"><label class="form-check-label auth-note" for="remember">Ghi nhớ đăng nhập</label></div><a class="auth-link small" href="{{ route('password.request') }}">Quên mật khẩu?</a></div><button class="btn btn-primary auth-submit w-100">Đăng nhập</button></form>
<p class="text-center auth-note mt-4 mb-0">Chưa có tài khoản? <a class="auth-link" href="{{ route('register') }}">Đăng ký ngay</a></p>
@push('scripts')<script>
document.getElementById('togglePassword').addEventListener('click',function(){const p=document.getElementById('password'),i=this.querySelector('i'),show=p.type==='password';p.type=show?'text':'password';i.className=show?'bi bi-eye-slash':'bi bi-eye';});
document.getElementById('login-form').addEventListener('submit', async function (event) {
    event.preventDefault();
    const box = document.getElementById('auth-fetch-error');
    const button = this.querySelector('button[type="submit"], button:not([type])');
    const originalText = button.innerHTML;
    box.classList.add('d-none');
    button.disabled = true;
    button.innerHTML = 'Dang dang nhap...';

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
            const message = data.errors ? Object.values(data.errors).flat().join('<br>') : (data.message || 'Dang nhap that bai');
            throw new Error(message);
        }
        window.location.href = data.redirect_url || "{{ route('home') }}";
    } catch (error) {
        box.innerHTML = error.message;
        box.classList.remove('d-none');
        button.disabled = false;
        button.innerHTML = originalText;
    }
});
</script>@endpush
@endsection
