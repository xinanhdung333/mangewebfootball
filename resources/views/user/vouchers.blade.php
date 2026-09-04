@extends('layouts.app')

@section('content')
<div class="container py-4 py-md-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-ticket-perforated text-primary me-2"></i>Ưu đãi của tôi</h1>
            <p class="text-muted mb-0">Sao chép mã và nhập ở bước thanh toán đơn hàng.</p>
        </div>
        <a href="{{ route('cart.index') }}" class="btn btn-outline-primary align-self-start align-self-md-auto"><i class="bi bi-cart3 me-1"></i>Đến giỏ hàng</a>
    </div>

    <div class="row g-4">
        @forelse($vouchers as $voucher)
            <div class="col-md-6 col-lg-4">
                <article class="card voucher-card h-100 border-0 shadow-sm overflow-hidden">
                    <div class="voucher-accent"></div>
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge text-bg-primary rounded-pill px-3 py-2">Mã giảm giá</span>
                            <i class="bi bi-patch-check-fill text-success fs-4" title="Đang hiệu lực"></i>
                        </div>
                        <div class="text-primary fw-bold fs-3 mb-1">-{{ number_format($voucher->discount_amount, 0, ',', '.') }}đ</div>
                        <p class="mb-3 text-muted">Áp dụng cho đơn từ {{ number_format($voucher->min_order_amount, 0, ',', '.') }}đ</p>
                        <div class="voucher-code d-flex align-items-center justify-content-between gap-2 mb-3">
                            <code>{{ $voucher->code }}</code>
                            <button type="button" class="btn btn-sm btn-primary copy-voucher" data-code="{{ $voucher->code }}"><i class="bi bi-copy me-1"></i>Sao chép</button>
                        </div>
                        <div class="mt-auto small text-muted"><i class="bi bi-clock me-1"></i>
                            @if($voucher->expires_at)
                                Hạn dùng: {{ $voucher->expires_at->format('d/m/Y H:i') }}
                            @else
                                Không giới hạn thời gian
                            @endif
                        </div>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12"><div class="text-center bg-light rounded-4 p-5">
                <i class="bi bi-ticket-perforated fs-1 text-muted d-block mb-3"></i>
                <h2 class="h5">Hiện chưa có voucher khả dụng</h2>
                <p class="text-muted mb-0">Các ưu đãi mới sẽ được cập nhật tại đây.</p>
            </div></div>
        @endforelse
    </div>
</div>

<style>
    .voucher-card { border-radius: 1rem; transition: transform .2s ease, box-shadow .2s ease; }
    .voucher-card:hover { transform: translateY(-4px); box-shadow: 0 .75rem 1.5rem rgba(13, 110, 253, .14) !important; }
    .voucher-accent { height: .4rem; background: linear-gradient(90deg, #0d6efd, #6f42c1); }
    .voucher-code { border: 1px dashed #86b7fe; border-radius: .6rem; padding: .55rem .65rem; background: #f5f9ff; }
    .voucher-code code { font-size: 1rem; font-weight: 700; letter-spacing: .05em; color: #084298; overflow-wrap: anywhere; }
</style>
<script>
document.querySelectorAll('.copy-voucher').forEach((button) => {
    button.addEventListener('click', async () => {
        const original = button.innerHTML;
        try { await navigator.clipboard.writeText(button.dataset.code); button.innerHTML = '<i class="bi bi-check2 me-1"></i>Đã chép'; }
        catch (error) { button.innerHTML = 'Không thể sao chép'; }
        setTimeout(() => button.innerHTML = original, 1800);
    });
});
</script>
@endsection
