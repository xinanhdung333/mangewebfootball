@extends('layouts.app')

@section('content')
<div class="container py-4">
    <a href="{{ $backRoute }}" class="qr-back-link mb-3">
        <i class="bi bi-arrow-left"></i> Quay lại phương thức thanh toán
    </a>

    <div class="qr-page">
        <div class="qr-panel">
            <div class="qr-header">
                <div>
                    <div class="qr-kicker">Thanh toán chuyển khoản</div>
                    <h3 class="fw-bold mb-1">Quét mã VietQR</h3>
                    <div class="text-muted">{{ $description }}</div>
                </div>
                <span class="qr-status">Đang chờ thanh toán</span>
            </div>

            <div class="row g-4 align-items-center">
                <div class="col-lg-5 text-center">
                    @if(!empty($bankTransfer['qr_url'] ?? null))
                        <div class="qr-image-wrap">
                            <img src="{{ $bankTransfer['qr_url'] }}" alt="MBBank VietQR" class="qr-image">
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            Chưa có QR vì thiếu số tài khoản MBBank trong .env
                        </div>
                    @endif
                </div>

                <div class="col-lg-7">
                    <div class="qr-info-grid">
                        <div class="qr-info-item">
                            <span>Ngân hàng</span>
                            <strong>{{ $bankTransfer['bank_name'] ?? 'MBBank' }}</strong>
                        </div>
                        <div class="qr-info-item">
                            <span>Số tài khoản</span>
                            <strong>{{ $bankTransfer['account_no'] ?? 'Chưa cấu hình' }}</strong>
                        </div>
                        <div class="qr-info-item">
                            <span>Chủ tài khoản</span>
                            <strong>{{ $bankTransfer['account_name'] ?? 'Chưa cấu hình' }}</strong>
                        </div>
                        <div class="qr-info-item">
                            <span>Số tiền</span>
                            <strong class="qr-amount">{{ number_format($amount, 0, ',', '.') }}đ</strong>
                        </div>
                        <div class="qr-info-item qr-info-wide">
                            <span>Nội dung chuyển khoản</span>
                            <strong class="qr-code">{{ $bankTransfer['transfer_code'] ?? '' }}</strong>
                        </div>
                    </div>

                    <div class="qr-note">
                        Sau khi chuyển khoản, hệ thống sẽ tự xác nhận khi nhận được tiền. Vui lòng nhập đúng nội dung chuyển khoản để đơn được xử lý nhanh.
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <a href="{{ $doneRoute }}" class="btn qr-primary-btn">
                            Tôi đã chuyển khoản <i class="bi bi-check2-circle"></i>
                        </a>
                        <a href="{{ $backRoute }}" class="btn qr-outline-btn">
                            Đổi phương thức
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.qr-back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #ef4427;
    font-weight: 600;
    text-decoration: none;
}
.qr-page {
    max-width: 980px;
    margin: 0 auto;
}
.qr-panel {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 28px;
    box-shadow: 0 12px 32px rgba(15, 23, 42, .08);
}
.qr-header {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    align-items: flex-start;
    margin-bottom: 24px;
}
.qr-kicker {
    color: #ef4427;
    font-size: .85rem;
    font-weight: 700;
    text-transform: uppercase;
}
.qr-status {
    border: 1px solid #fed7aa;
    background: #fff7ed;
    color: #c2410c;
    border-radius: 999px;
    padding: 7px 12px;
    font-size: .82rem;
    font-weight: 700;
    white-space: nowrap;
}
.qr-image-wrap {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 18px;
}
.qr-image {
    width: min(100%, 320px);
    height: auto;
}
.qr-info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}
.qr-info-item {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 12px 14px;
    background: #fff;
}
.qr-info-wide {
    grid-column: 1 / -1;
}
.qr-info-item span {
    display: block;
    color: #64748b;
    font-size: .84rem;
    margin-bottom: 4px;
}
.qr-info-item strong {
    color: #111827;
    overflow-wrap: anywhere;
}
.qr-amount,
.qr-code {
    color: #ef4427 !important;
}
.qr-note {
    margin-top: 14px;
    padding: 12px 14px;
    border-radius: 8px;
    background: #f8fafc;
    color: #475569;
    font-size: .92rem;
}
.qr-primary-btn {
    background: #ef4427;
    color: #fff;
    border: 0;
    font-weight: 700;
}
.qr-primary-btn:hover {
    background: #d9361d;
    color: #fff;
}
.qr-outline-btn {
    border: 1px solid #cbd5e1;
    color: #334155;
    font-weight: 700;
}
@media (max-width: 768px) {
    .qr-panel {
        padding: 18px;
    }
    .qr-header,
    .qr-info-grid {
        display: block;
    }
    .qr-status,
    .qr-info-item {
        display: block;
        margin-top: 10px;
    }
}
</style>
@endsection
