@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height:80vh;">
        <div class="col-md-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-5">
                    <h2 class="card-title text-center mb-4 fw-bold text-primary">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Đăng nhập
                    </h2>

                    @if($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form method="POST" action="{{ route('login.post') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control form-control-lg" name="email" value="{{ old('email') }}" placeholder="Nhập email của bạn" required>
                        </div>

                        <div class="mb-3 position-relative">
                            <label class="form-label fw-semibold">Mật khẩu</label>
                            <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="Nhập mật khẩu" required>
                            <span 
                                class="position-absolute top-50 end-0 translate-middle-y me-3"
                                onclick="togglePassword()" 
                                style="cursor: pointer;">
                                <i id="eyeIcon" class="bi bi-eye-slash fs-5 text-secondary"></i>
                            </span>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold">Đăng nhập</button>
                    </form>

                    <hr class="my-4">

                    <p class="text-center mb-0">
                        Chưa có tài khoản? <a href="{{ route('register') }}" class="text-primary fw-semibold">Đăng ký ngay</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function togglePassword() {
    const password = document.getElementById("password");
    const icon = document.getElementById("eyeIcon");

    if (password.type === "password") {
        password.type = "text";
        icon.classList.replace("bi-eye-slash", "bi-eye");
    } else {
        password.type = "password";
        icon.classList.replace("bi-eye", "bi-eye-slash");
    }
}
</script>
@endpush

@endsection
