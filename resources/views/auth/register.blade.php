@extends('layouts.app')

@section('content')

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

```
        <div class="card shadow">
            <div class="card-header text-center">
                <h4>Đăng ký tài khoản</h4>
            </div>

            <div class="card-body">

                {{-- show lỗi server --}}
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form id="registerForm" method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Tên</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               value="{{ old('name') }}">
                        <small id="nameError" class="text-danger"></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email"
                               name="email"
                               class="form-control"
                               value="{{ old('email') }}">
                        <small id="emailError" class="text-danger"></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text"
                               name="phone"
                               class="form-control"
                               value="{{ old('phone') }}">
                        <small id="phoneError" class="text-danger"></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mật khẩu</label>
                        <input type="password"
                               name="password"
                               class="form-control">
                        <small id="passwordError" class="text-danger"></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Xác nhận mật khẩu</label>
                        <input type="password"
                               name="password_confirmation"
                               class="form-control">
                        <small id="confirmError" class="text-danger"></small>
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
```

</div>
<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {

    let hasError = false;

    const name = document.querySelector('[name="name"]').value.trim();
    const email = document.querySelector('[name="email"]').value.trim();
    const phone = document.querySelector('[name="phone"]').value.trim();
    const password = document.querySelector('[name="password"]').value;
    const confirm = document.querySelector('[name="password_confirmation"]').value;

    // reset error
    document.querySelectorAll('small').forEach(el => el.innerText = '');

    // NAME
    if (name === '') {
        document.getElementById('nameError').innerText = 'Tên không được để trống';
        hasError = true;

    } else if (name.length < 2) {
        document.getElementById('nameError').innerText = 'Tên phải có ít nhất 2 ký tự';
        hasError = true;

    } else if (name.length > 20) {
        document.getElementById('nameError').innerText = 'Tên không được vượt quá 20 ký tự';
        hasError = true;
    }

    // EMAIL
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailRegex.test(email)) {
        document.getElementById('emailError').innerText = 'Email không hợp lệ';
        hasError = true;
    }

    // PHONE
    const phoneRegex = /^(0|\+84)[3|5|7|8|9][0-9]{8}$/;

    if (phone.length < 9 || !phoneRegex.test(phone)) {
        document.getElementById('phoneError').innerText = 'Số điện thoại không hợp lệ';
        hasError = true;
    }

    // PASSWORD
  const passwordRegex =
/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

if (!passwordRegex.test(password)) {
    document.getElementById('passwordError').innerText =
    'Mật khẩu phải có ít nhất 8 ký tự, gồm chữ hoa, chữ thường, số và ký tự đặc biệt';
    hasError = true;
}

    // CONFIRM PASSWORD
    if (password !== confirm) {
        document.getElementById('confirmError').innerText = 'Mật khẩu không khớp';
        hasError = true;
    }

    if (hasError) {
        e.preventDefault();
    }

});
</script>

@endsection
