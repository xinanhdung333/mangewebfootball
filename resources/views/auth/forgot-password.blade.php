@extends('layouts.app')
@section('content')
<title>Quên mật khẩu</title>
<script src="https://cdn.tailwindcss.com"></script>

<div class="min-h-screen flex items-center justify-end px-20 relative">

    <!-- Form -->
<div class="w-full max-w-xl bg-white dark:bg-gray-800 shadow-2xl rounded-2xl p-10 form-box">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-2">
            Quên mật khẩu
        </h2>

        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
            Nhập email của bạn để nhận link đặt lại mật khẩu.
        </p>

        @if (session('status'))
            <div class="mb-4 text-green-600 text-sm">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5" id="forgotForm">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 outline-none"
                >

                @error('email')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                id="submitBtn"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 rounded-lg transition">
                Gửi link đặt lại mật khẩu
            </button>
        </form>

    </div>
</div>

<script>
const form = document.getElementById('forgotForm');
const btn = document.getElementById('submitBtn');

form.addEventListener('submit', function () {
    btn.disabled = true;
    btn.innerText = 'Đang gửi...';
    btn.classList.add('opacity-70', 'cursor-not-allowed');
});
</script>

<style>

body{
    background-image: url("{{ asset('assets/atony.jpg') }}") !important;
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
}

/* Form chỉnh đúng vị trí tay cầu thủ */
.form-box{
    margin-right: 180px;
    backdrop-filter: blur(10px);
        min-height: 320px;

}

/* Responsive */
@media(max-width:768px){
    .form-box{
        margin-right:0;
    }
}

</style>

@endsection