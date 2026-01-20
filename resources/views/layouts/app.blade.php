<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Football Booking')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>body{background:#f8f9fa;} .description-short{overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;} .show-more-btn{color:#0d6efd;cursor:pointer;font-size:14px;}</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="{{ route('home') }}">Football Booking</a>
    <div>
      @guest
        <a href="{{ route('login') }}" class="btn btn-outline-primary me-2">Đăng nhập</a>
        <a href="{{ route('register') }}" class="btn btn-primary">Đăng ký</a>
      @else
        <span class="me-3">Xin chào, {{ Auth::user()->name }}</span>
      @endguest
    </div>
  </div>
</nav>

<main class="py-4">
    <div class="container">
        @yield('content')
    </div>
</main>

@include('partials.footer')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
