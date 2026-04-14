@extends('layouts.app')

@section('content')

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow">
                <div class="card-header text-center">
                    <h4>Đăng ký tài khoản</h4>
                </div>

                <div class="card-body">

                    {{-- show lỗi --}}
                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Tên</label>
                            <input type="text" 
                                   name="name" 
                                   class="form-control"
                                   value="{{ old('name') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" 
                                   name="email" 
                                   class="form-control"
                                   value="{{ old('email') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" 
                                   name="phone" 
                                   class="form-control"
                                   value="{{ old('phone') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mật khẩu</label>
                            <input type="password" 
                                   name="password" 
                                   class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Xác nhận mật khẩu</label>
                            <input type="password" 
                                   name="password_confirmation" 
                                   class="form-control">
                        </div>

                        <button class="btn btn-primary w-100">
                            Đăng ký
                        </button>

                        <div class="text-center mt-3">
                            <a href="{{ route('login') }}">
                                Đã có tài khoản?
                            </a>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {
    let hasError = false;

    // lấy value
    const name = document.querySelector('[name="name"]').value.trim();
    const email = document.querySelector('[name="email"]').value.trim();
    const phone = document.querySelector('[name="phone"]').value.trim();
    const password = document.querySelector('[name="password"]').value;
    const confirm = document.querySelector('[name="password_confirmation"]').value;

    // reset error
    document.querySelectorAll('small').forEach(e => e.innerText = '');

    // NAME
    if (name === '') {
        document.getElementById('nameError').innerText = 'Tên không được để trống';
        hasError = true;
    }

    // EMAIL
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        document.getElementById('emailError').innerText = 'Email không hợp lệ';
        hasError = true;
    }

    // PHONE
    if (phone.length < 9) {
        document.getElementById('phoneError').innerText = 'Số điện thoại không hợp lệ';
        hasError = true;
    }

    // PASSWORD
    if (password.length < 6) {
        document.getElementById('passwordError').innerText = 'Mật khẩu phải >= 6 ký tự';
        hasError = true;
    }

    // CONFIRM
    if (password !== confirm) {
        document.getElementById('confirmError').innerText = 'Mật khẩu không khớp';
        hasError = true;
    }

    if (hasError) {
        e.preventDefault(); // chặn submit
    }
});
</script>
@endsection