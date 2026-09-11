<!doctype html>
<html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"><meta name="csrf-token" content="{{ csrf_token() }}"><title>{{ config('app.name', 'SportsHub') }}</title><link rel="icon" href="{{ asset('assets/images/logo.jpg') }}"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css"><link rel="stylesheet" href="{{ asset('assets/css/sports-theme.css?v='.time()) }}">
<style>
@media (max-width: 768px) {
    .auth-card { padding: 15px !important; }
    .auth-title { font-size: 1.25rem !important; margin-bottom: 0.25rem !important; margin-top: 0.25rem !important; }
    .auth-subtitle { font-size: 0.8rem !important; margin-bottom: 0.75rem !important; line-height: 1.3 !important; }
    .auth-card .mb-3, .auth-card .mb-4 { margin-bottom: 0.5rem !important; }
    .auth-card .form-control { min-height: 38px !important; height: 38px !important; padding: 0.25rem 0.75rem !important; font-size: 0.85rem !important; border-radius: 8px !important; }
    .auth-card .form-label { margin-bottom: 0.2rem !important; font-size: 0.8rem !important; }
    .auth-card .input-group-text { padding: 0.25rem 0.5rem !important; }
    .auth-submit { min-height: 40px !important; height: 40px !important; font-size: 0.9rem !important; border-radius: 8px !important; padding: 0 !important; }
    .auth-note { margin-top: 0.75rem !important; font-size: 0.8rem !important; }
    .auth-brand { font-size: 1.1rem !important; margin-bottom: 0.25rem !important; }
    .auth-panel { padding: 10px !important; }
}
</style>
</head>
<body><div class="auth-page"><aside class="auth-visual"><div class="auth-visual-copy"><div class="auth-kicker"><i class="bi bi-lightning-charge-fill"></i> SportsHub</div><h1>Sẵn sàng cho trận đấu tiếp theo.</h1><p class="mb-0 fs-5 text-white-50">Đặt sân, mua sắm và kết nối với cộng đồng thể thao của bạn.</p></div></aside><main class="auth-panel"><div class="auth-card">@yield('content')</div></main></div>@stack('scripts')</body></html>
