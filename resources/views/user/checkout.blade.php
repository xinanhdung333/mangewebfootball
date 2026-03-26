@extends('layouts.app')
@section('content')

<div class="container-fluid py-5">
    <div style="max-width: 600px; margin: 50px auto; text-align: center; padding: 40px; border-radius: 15px; background: #e6ffed; border: 2px solid #b3f0c4; box-shadow: 0 4px 15px rgba(46, 125, 50, 0.15);">
        <div class="mb-4">
            <i class="bi bi-check-circle-fill" style="font-size: 4rem; color: #2e7d32;"></i>
        </div>
        
        <h3 class="text-success fw-bold mb-3">Thanh toán thành công!</h3>

        @php $createdOrders = $createdOrders ?? []; @endphp
        <p class="fs-5 text-dark mb-4">
            Bạn đã tạo <strong>{{ count($createdOrders) }}</strong> đơn hàng riêng:
        </p>

        @if(count($createdOrders) > 0)
            <div class="alert alert-light border" style="max-height: 300px; overflow-y: auto;">
                <ul class="list-unstyled text-start">
                    @foreach($createdOrders as $order)
                        <li class="mb-2">
                            <strong>Đơn #{{ $order['order_id'] }}</strong> — 
                            <span class="text-muted">{{ $order['name'] }}</span> — 
                            <span class="text-success fw-bold">{{ number_format($order['total'], 0, ',', '.') }} VNĐ</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @else
            <div class="alert alert-info">Chưa có đơn hàng. Vui lòng thực hiện thanh toán từ giỏ hàng.</div>
        @endif

        <div class="mt-4">
            <p class="text-muted mb-3">Nhấn vào nút bên dưới để quay lại giỏ hàng.</p>
            <a href="{{ route('user.cart') }}" class="btn btn-primary btn-lg px-5">
                <i class="bi bi-arrow-left"></i> Quay lại giỏ hàng
            </a>
        </div>

        <div class="mt-3">
            <a href="{{ route('user.services') }}" class="btn btn-outline-secondary">
                <i class="bi bi-shop"></i> Tiếp tục mua sắm
            </a>
        </div>
    </div>
</div>

<script>
// Chuyển hướng sau 5 giây
setTimeout(() => {
    window.location.href = '{{ route("user.myServices") }}';
}, 5000);
</script>

<style>
@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

div[style*="max-width: 600px"] {
    animation: slideInDown 0.6s ease-out;
}
</style>

@endsection
