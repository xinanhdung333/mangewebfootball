@extends('layouts.app')

@php
    $shippingMethods = \App\Models\ShippingMethod::query()->where('is_active', true)->orderBy('id')->get();
@endphp
 
@section('content')
<div class="container py-4">

<a href="{{ $type === 'booking' ? route('user.myBookings') : route('user.myServices') }}" class="pay-back-link mb-2">
<i class="bi bi-arrow-left"></i> Quay lại
</a>

<h3 class="fw-bold mb-4"><i class="bi bi-bag-check-fill pay-accent-icon me-2"></i>Thanh toán</h3>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-exclamation-circle me-1"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form method="POST" action="{{ $submitRoute }}">
@csrf

<input type="hidden" id="selectedAddressId" name="selected_address_id" value="">
<input type="hidden" id="hiddenShippingFee" name="shipping_fee" value="0">
<input type="hidden" id="hiddenVoucherCode" name="voucher_code" value="">

<div class="row g-3">

{{-- ═══════════════ LEFT COLUMN ═══════════════ --}}
<div class="col-lg-8">

@if($type === 'order')
{{-- ── Delivery address ── --}}
<div class="pay-card mb-3">
<div class="d-flex justify-content-between align-items-center mb-2">
<div class="pay-card-title"><i class="bi bi-geo-alt-fill pay-accent-icon me-2"></i>Địa chỉ giao hàng</div>
@if(isset($addresses) && $addresses->count() > 1)
<button type="button" class="pay-link-btn" id="toggleAddressList">Đổi địa chỉ</button>
@endif
</div>

@if(isset($addresses) && $addresses->count() > 0)

<div id="addressPreview" class="pay-address-preview">
@foreach($addresses as $address)
<div class="pay-address-preview-row {{ ($address->is_default || $loop->first) ? '' : 'd-none' }}" data-preview-for="{{ $address->id }}">
<span class="fw-semibold">{{ $address->name ?? Auth::user()->name }}</span>
<span class="pay-divider">|</span>
<span class="text-muted">{{ $address->phone }}</span>
<div class="text-muted small mt-1">
{{ $address->street_address }}
@if($address->ward), {{ $address->ward }}@endif
@if($address->district), {{ $address->district }}@endif
, {{ $address->city }}
</div>
</div>
@endforeach
</div>

<div id="addressList" class="d-none mt-3">
<div class="row g-2">
@foreach($addresses as $address)
<div class="col-md-6">
<label class="w-100">
<input
    type="radio"
    name="address_id"
    value="{{ $address->id }}"
    class="d-none address-option"
    data-address-id="{{ $address->id }}"
    data-lat="{{ $address->lat ?? '' }}"
    data-lng="{{ $address->lng ?? '' }}"
    @if($address->is_default || $loop->first) checked @endif
>
<div class="pay-radio-row address-card">
<div class="flex-grow-1">
<div class="fw-semibold small">
{{ $address->name ?? Auth::user()->name }}
@if($address->is_default)<span class="pay-badge-default ms-1">Mặc định</span>@endif
</div>
<div class="text-muted small">
{{ $address->street_address }}@if($address->ward), {{ $address->ward }}@endif
</div>
<div class="mt-1" id="fee-addr-{{ $address->id }}">
@if($address->lat && $address->lng)
<span class="badge bg-light text-secondary border">Đang tính phí ship...</span>
@else
<span class="badge bg-warning text-dark">Chưa có tọa độ - phí cố định</span>
@endif
</div>
</div>
<span class="pay-radio-dot"></span>
</div>
</label>
</div>
@endforeach
</div>
</div>

@else

<div class="alert alert-warning mb-0">
<i class="bi bi-exclamation-triangle"></i>
Bạn chưa có địa chỉ giao hàng.
<a href="{{ route('user.profile') }}" class="alert-link">Thêm địa chỉ ngay</a>
</div>

@endif

</div>
@endif

{{-- ── Ordered items ── --}}
<div class="pay-card mb-3">
<div class="pay-card-title mb-3"><i class="bi bi-basket2-fill pay-accent-icon me-2"></i>{{ $type === 'booking' ? 'Thông tin đặt sân' : 'Sản phẩm đặt mua' }}</div>

@if($type === 'booking')

<div class="d-flex gap-3">
<img
src="{{ !empty($item->field->image) ? asset('uploads/fields/'.$item->field->image) : asset('assets/images/banner.jpg') }}"
class="pay-item-thumb"
>
<div class="flex-grow-1">
<div class="fw-semibold">{{ $item->field->name }}</div>
<div class="text-muted small mt-1">
<i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($item->booking_date)->format('d/m/Y') }}
<span class="mx-2">·</span>
<i class="bi bi-clock me-1"></i>{{ substr($item->start_time,0,5) }} - {{ substr($item->end_time,0,5) }}
</div>
</div>
</div>

@if(isset($services) && $services->count())
<hr>
<div class="fw-semibold small text-muted mb-2">Dịch vụ đi kèm</div>
@foreach($services as $service)
<div class="d-flex justify-content-between align-items-center py-1">
<div class="d-flex align-items-center gap-2">
<img src="{{ !empty($service->image) ? asset('uploads/services/'.$service->image) : asset('assets/images/banner.jpg') }}" width="36" height="36" style="object-fit:cover;border-radius:6px">
<span class="small">{{ $service->name }} <span class="text-muted">x{{ $service->quantity }}</span></span>
</div>
<span class="small text-muted">{{ number_format($service->price * $service->quantity, 0, ',', '.') }}đ</span>
</div>
@endforeach
@endif

@elseif(isset($services) && $services->count())

@foreach($services as $service)
<div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'pay-item-divider' : '' }}">
<div class="d-flex align-items-center gap-3">
<img src="{{ !empty($service->image) ? asset('uploads/services/'.$service->image) : asset('assets/images/banner.jpg') }}" class="pay-item-thumb-sm">
<div>
<div>{{ $service->name }}</div>
<div class="small text-muted">x {{ $service->quantity }}</div>
</div>
</div>
<span class="text-muted">{{ number_format($service->price * $service->quantity, 0, ',', '.') }}đ</span>
</div>
@endforeach

@endif

</div>

{{-- ── Shipping service + Payment method side-by-side ── --}}
<div class="row g-3 mb-3">

@if($type === 'order')
<div class="col-md-6">
<div class="pay-card h-100">
<div class="pay-card-title mb-3"><i class="bi bi-truck pay-accent-icon me-2"></i>Vận chuyển</div>

@if($shippingMethods->isNotEmpty())
    @foreach($shippingMethods as $shippingMethod)
        <label class="w-100 d-block {{ $loop->last ? 'mb-0' : 'mb-2' }}">
            <input
                type="radio"
                name="shipping_service"
                value="{{ $shippingMethod->code }}"
                data-extra-fee="{{ (float) $shippingMethod->extra_fee }}"
                class="d-none"
                {{ $loop->first ? 'checked' : '' }}
                {{ $shippingMethod->is_active ? '' : 'disabled' }}
            >
            <div class="pay-radio-row {{ $shippingMethod->is_active ? '' : 'pay-radio-row-disabled' }}">
                <div class="flex-grow-1">
                    <div class="fw-semibold small">
                        {{ $shippingMethod->name }}
                        @if(!empty($shippingMethod->description))
                            <span class="text-muted fw-normal">({{ $shippingMethod->description }})</span>
                        @endif
                    </div>
                    <div class="text-muted small" {{ $loop->first ? 'id="shippingServiceFeeLabel"' : '' }}>
                        @if($shippingMethod->extra_fee > 0)
                            +{{ number_format((float) $shippingMethod->extra_fee, 0, ',', '.') }}đ
                        @elseif($shippingMethod->extra_fee == 0)
                            Phí cơ bản
                        @else
                            Giảm {{ number_format(abs((float) $shippingMethod->extra_fee), 0, ',', '.') }}đ
                        @endif
                    </div>
                </div>
                <span class="pay-radio-dot"></span>
            </div>
        </label>
    @endforeach
@else
    <div class="alert alert-warning mb-0 small">Chưa có phương thức vận chuyển được cấu hình.</div>
@endif
    
</div>
</div>
@endif

<div class="{{ $type === 'order' ? 'col-md-6' : 'col-12' }}">
<div class="pay-card h-100">
<div class="pay-card-title mb-3"><i class="bi bi-credit-card-fill pay-accent-icon me-2"></i>Phương thức thanh toán</div>

<label class="w-100 d-block mb-2">
<input type="radio" name="payment_method" value="momo" class="d-none payment-option" checked>
<div class="pay-radio-row">
<img src="{{ asset('assets/momo.webp') }}" width="26" class="me-1">
<div class="flex-grow-1">
<div class="fw-semibold small">MoMo</div>
<div class="text-muted small">Thanh toán online</div>
</div>
<span class="pay-radio-dot"></span>
</div>
</label>

<label class="w-100 d-block mb-2">
<input type="radio" name="payment_method" value="cash" class="d-none payment-option">
<div class="pay-radio-row">
<span class="pay-method-icon"><i class="bi bi-cash"></i></span>
<div class="flex-grow-1">
<div class="fw-semibold small">Thanh toán khi nhận hàng</div>
<div class="text-muted small">Không cần ví điện tử</div>
</div>
<span class="pay-radio-dot"></span>
</div>
</label>

<label class="w-100 d-block mb-0">
<input type="radio" name="payment_method" value="bank_transfer" class="d-none payment-option" @disabled(empty($bankTransfer['account_no'] ?? null))>
<div class="pay-radio-row {{ empty($bankTransfer['account_no'] ?? null) ? 'pay-radio-row-disabled' : '' }}">
<span class="pay-method-icon"><i class="bi bi-qr-code-scan"></i></span>
<div class="flex-grow-1">
<div class="fw-semibold small">Chuyển khoản MBBank</div>
<div class="text-muted small">
@if(empty($bankTransfer['account_no'] ?? null))
Chưa cấu hình
@else
Quét mã VietQR
@endif
</div>
</div>
<span class="pay-radio-dot"></span>
</div>
</label>

</div>
</div>

</div>

<div id="bankTransferDetails" class="pay-card mb-3 d-none">
<div class="row g-3 align-items-center">
<div class="col-md-4 text-center">
@if(!empty($bankTransfer['qr_url'] ?? null))
<img id="bankTransferQrImage" src="{{ $bankTransfer['qr_url'] }}" alt="MBBank VietQR" class="img-fluid rounded bg-white p-2" style="max-height:200px">
@else
<div class="alert alert-warning mb-0 small">Chưa có QR vì thiếu số tài khoản MBBank trong .env</div>
@endif
</div>
<div class="col-md-8">
<div class="row g-2 small">
<div class="col-6"><div class="text-muted">Ngân hàng</div><div class="fw-semibold">{{ $bankTransfer['bank_name'] ?? 'MBBank' }}</div></div>
<div class="col-6"><div class="text-muted">Số tài khoản</div><div class="fw-semibold">{{ $bankTransfer['account_no'] ?? 'Chưa cấu hình' }}</div></div>
<div class="col-6"><div class="text-muted">Chủ tài khoản</div><div class="fw-semibold">{{ $bankTransfer['account_name'] ?? 'Chưa cấu hình' }}</div></div>
<div class="col-6"><div class="text-muted">Số tiền</div><div id="bankTransferAmount" class="fw-semibold pay-accent-text">{{ number_format($amount,0,',','.') }}đ</div></div>
<div class="col-12"><div class="text-muted">Nội dung chuyển khoản</div><div class="fw-bold text-danger">{{ $bankTransfer['transfer_code'] ?? '' }}</div></div>
</div>
</div>
</div>
</div>

{{-- ── Order notes ── --}}
<div class="pay-card mb-3">
<div class="pay-card-title mb-2"><i class="bi bi-pencil-square pay-accent-icon me-2"></i>Ghi chú đơn hàng</div>
<textarea class="form-control pay-textarea" name="note" rows="2" placeholder="Lưu ý cho shop hoặc shipper (không bắt buộc)"></textarea>
</div>

</div>

{{-- ═══════════════ RIGHT COLUMN ═══════════════ --}}
<div class="col-lg-4">

{{-- ── Voucher ── --}}
<div class="pay-card mb-3">
<div class="pay-card-title mb-2"><i class="bi bi-ticket-perforated-fill pay-accent-icon me-2"></i>Mã giảm giá</div>
<div class="d-flex gap-2">
<input type="text" class="form-control pay-voucher-input" id="voucherInput" placeholder="Nhập mã giảm giá">
<button type="button" class="btn pay-btn-outline" id="voucherApplyBtn">Áp dụng</button>
</div>
@if($type === 'order')
<button type="button" class="btn btn-link px-0 pt-2 text-decoration-none" data-bs-toggle="modal" data-bs-target="#voucherPickerModal">
    <i class="bi bi-ticket-detailed me-1"></i>Chọn voucher
</button>
@endif
<div id="voucherMsg" class="small mt-2 text-muted"></div>
</div>

{{-- ── Summary ── --}}
<div class="pay-card pay-summary-card">

<div class="pay-card-title mb-3">Tóm tắt thanh toán</div>

<div class="d-flex justify-content-between small mb-2">
<span class="text-muted">Tạm tính</span>
<span>{{ number_format($item->total_amount ?? $amount, 0, ',', '.') }}đ</span>
</div>

@if($type === 'order')
<div class="d-flex justify-content-between small mb-2">
<span class="text-muted">Phí vận chuyển</span>
<span id="summaryShip" class="text-info">Đang tính...</span>
</div>
<div class="d-flex justify-content-between small mb-2 d-none" id="summaryVoucherRow">
<span class="text-muted">Voucher giảm giá</span>
<span id="summaryVoucherAmount" class="text-success">-0đ</span>
</div>
@endif

<hr>

<div class="d-flex justify-content-between align-items-center mb-3">
<span class="fw-semibold">Tổng thanh toán</span>
<span id="summaryTotal" class="fs-4 fw-bold pay-accent-text">{{ number_format($item->total_amount ?? $amount, 0, ',', '.') }}đ</span>
</div>

<button type="submit" class="btn pay-btn-primary w-100 py-2">
Đặt hàng <i class="bi bi-arrow-right"></i>
</button>

</div>

</div>

</div>

</form>

</div>


<style>
/* ══════════════════════════════════════════════
   CHECKOUT — flat two-column, Shopee-style accent
══════════════════════════════════════════════ */
:root {
    --pay-accent:       #ee4d2d;
    --pay-accent-soft:  #fef1ee;
    --pay-text:         #1f2937;
    --pay-text-muted:   #6b7280;
    --pay-border:       #e5e7eb;
    --pay-border-hover: #d1d5db;
    --pay-surface:      #ffffff;
    --pay-surface-soft: #f9fafb;
}

.pay-back-link {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    font-size: .85rem;
    color: var(--pay-text-muted);
    text-decoration: none;
}
.pay-back-link:hover { color: var(--pay-accent); }

.pay-accent-icon { color: var(--pay-accent); }
.pay-accent-text { color: var(--pay-accent); }

.pay-card {
    background: var(--pay-surface);
    border: 1px solid var(--pay-border);
    border-radius: 12px;
    padding: 1.1rem 1.25rem;
}

.pay-card-title {
    font-weight: 600;
    font-size: .95rem;
    color: var(--pay-text);
    display: flex;
    align-items: center;
}

.pay-link-btn {
    background: none;
    border: none;
    color: var(--pay-accent);
    font-size: .82rem;
    font-weight: 600;
    padding: 0;
    cursor: pointer;
}
.pay-link-btn:hover { text-decoration: underline; }

.pay-address-preview-row { font-size: .88rem; }
.pay-divider { color: var(--pay-border-hover); margin: 0 .5rem; }

.pay-badge-default {
    display: inline-block;
    font-size: .68rem;
    font-weight: 600;
    color: var(--pay-accent);
    background: var(--pay-accent-soft);
    padding: .1rem .4rem;
    border-radius: 12px;
}

.pay-item-thumb {
    width: 80px; height: 80px;
    object-fit: cover;
    border-radius: 10px;
    flex-shrink: 0;
}
.pay-item-thumb-sm {
    width: 52px; height: 52px;
    object-fit: cover;
    border-radius: 8px;
    flex-shrink: 0;
}
.pay-item-divider { border-bottom: 1px solid var(--pay-border); }

/* ── Radio rows (address / shipping / payment) ── */
.pay-radio-row {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .7rem .85rem;
    border: 1px solid var(--pay-border);
    border-radius: 10px;
    cursor: pointer;
    transition: border-color .15s ease, background .15s ease;
}
.pay-radio-row:hover { border-color: var(--pay-border-hover); }
.pay-radio-row-disabled { opacity: .55; cursor: not-allowed; }

.pay-radio-dot {
    width: 18px; height: 18px;
    border-radius: 50%;
    border: 2px solid var(--pay-border-hover);
    flex-shrink: 0;
    position: relative;
}

input[type="radio"]:checked + .pay-radio-row {
    border-color: var(--pay-accent);
    background: var(--pay-accent-soft);
}
input[type="radio"]:checked + .pay-radio-row .pay-radio-dot {
    border-color: var(--pay-accent);
}
input[type="radio"]:checked + .pay-radio-row .pay-radio-dot::after {
    content: '';
    position: absolute;
    inset: 3px;
    background: var(--pay-accent);
    border-radius: 50%;
}
input[type="radio"]:disabled + .pay-radio-row { opacity: .55; cursor: not-allowed; }

.pay-method-icon {
    width: 28px; height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--pay-text-muted);
    font-size: 1.05rem;
    flex-shrink: 0;
}

.pay-textarea {
    border-color: var(--pay-border);
    border-radius: 8px;
    resize: none;
}
.pay-textarea:focus,
.pay-voucher-input:focus {
    border-color: var(--pay-accent);
    box-shadow: 0 0 0 .2rem rgba(238,77,45,.15);
}

.pay-voucher-input {
    border-color: var(--pay-border);
    border-radius: 8px;
    font-size: .88rem;
}

.pay-btn-outline {
    border: 1px solid var(--pay-text);
    background: var(--pay-text);
    color: #fff;
    border-radius: 8px;
    font-size: .85rem;
    font-weight: 600;
    padding: .375rem 1rem;
    white-space: nowrap;
}
.pay-btn-outline:hover { background: #111827; }

/* ── Summary (sticky right column) ── */
.pay-summary-card {
    position: sticky;
    top: 1rem;
}

.pay-btn-primary {
    background: var(--pay-accent);
    border-color: var(--pay-accent);
    color: #fff;
    font-weight: 600;
    border-radius: 8px;
}
.pay-btn-primary:hover { background: #d8431f; border-color: #d8431f; color: #fff; }

@keyframes spin { to { transform: rotate(360deg); } }
.spin { display: inline-block; animation: spin 1s linear infinite; }
</style>

@if($type === 'order')
<div class="modal fade" id="voucherPickerModal" tabindex="-1" aria-labelledby="voucherPickerTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="voucherPickerTitle"><i class="bi bi-ticket-perforated text-primary me-2"></i>Chọn voucher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body p-3">
                @forelse($availableVouchers as $voucher)
                    @php($isEligible = $item->total_amount >= $voucher->min_order_amount)
                    <div class="border rounded-3 p-3 mb-3 {{ $isEligible ? '' : 'opacity-50 bg-light' }}">
                        <div class="d-flex justify-content-between gap-3">
                            <div>
                                <div class="fw-bold text-primary">Giảm {{ number_format($voucher->discount_amount, 0, ',', '.') }}đ</div>
                                <div class="small text-muted mt-1">Đơn tối thiểu {{ number_format($voucher->min_order_amount, 0, ',', '.') }}đ</div>
                                <div class="small mt-2"><code>{{ $voucher->code }}</code>
                                    @if($voucher->expires_at)
                                        <span class="text-muted ms-1">· HSD {{ $voucher->expires_at->format('d/m/Y') }}</span>
                                    @endif
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm {{ $isEligible ? 'btn-primary select-voucher' : 'btn-secondary' }} align-self-center"
                                @disabled(!$isEligible) data-code="{{ $voucher->code }}">
                                {{ $isEligible ? 'Chọn' : 'Chưa đủ điều kiện' }}
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-muted mb-0 py-4">Hiện chưa có voucher khả dụng.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Toggle address list ── */
    const toggleBtn = document.getElementById('toggleAddressList');
    const addressList = document.getElementById('addressList');
    const addressPreview = document.getElementById('addressPreview');

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            const showing = !addressList.classList.contains('d-none');
            addressList.classList.toggle('d-none');
            toggleBtn.textContent = showing ? 'Đổi địa chỉ' : 'Đóng';
        });
    }

    function updateAddressPreview(addrId) {
        if (!addressPreview) return;
        addressPreview.querySelectorAll('.pay-address-preview-row').forEach(row => {
            row.classList.toggle('d-none', row.dataset.previewFor !== String(addrId));
        });
    }

    /* ── 1. Chọn địa chỉ ── */
    const addressOptions   = document.querySelectorAll('.address-option');
    const selectedAddressInput = document.getElementById('selectedAddressId');

    const checkedAddr = document.querySelector('.address-option:checked');
    if (checkedAddr) selectedAddressInput.value = checkedAddr.value;

    addressOptions.forEach(opt => {
        opt.addEventListener('change', function () {
            if (this.checked) {
                selectedAddressInput.value = this.value;
                updateAddressPreview(this.value);
                calcShippingFeeFor(this);
                if (addressList && toggleBtn) {
                    addressList.classList.add('d-none');
                    toggleBtn.textContent = 'Đổi địa chỉ';
                }
            }
        });
    });

    /* ── 2. Tính phí ship khi chọn địa chỉ ── */
    const csrfToken   = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const baseAmount  = {{ $type === 'order' ? (int)($item->total_amount ?? 0) : 0 }};
    const isOrder     = {{ $type === 'order' ? 'true' : 'false' }};
    const hiddenShipFee = document.getElementById('hiddenShippingFee');
    const summaryShip   = document.getElementById('summaryShip');
    const summaryTotal  = document.getElementById('summaryTotal');
    const shippingServiceFeeLabel = document.getElementById('shippingServiceFeeLabel');
    let selectedShippingExtraFee = Number(document.querySelector('input[name="shipping_service"]:checked')?.dataset.extraFee || 0);
    let currentCalculatedShippingFee = 0;
    let currentShippingFreeFlag = false;
    let lastDistanceKm = null;

    document.querySelectorAll('input[name="shipping_service"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            selectedShippingExtraFee = Number(this.dataset.extraFee || 0);
            updateSummary(currentCalculatedShippingFee, null, lastDistanceKm, currentShippingFreeFlag);
            updateTotal();
        });
    });

    if (isOrder) {
        addressOptions.forEach(opt => {
            if (opt.dataset.lat && opt.dataset.lng) {
                fetchShippingFee(opt.dataset.addressId, parseFloat(opt.dataset.lat), parseFloat(opt.dataset.lng), opt.checked);
            } else if (opt.checked) {
                updateSummary(null, 'Chưa có toạ độ');
            }
        });
    }

    function calcShippingFeeFor(radioEl) {
        if (!isOrder) return;
        const addrId = radioEl.dataset.addressId;
        const lat    = parseFloat(radioEl.dataset.lat);
        const lng    = parseFloat(radioEl.dataset.lng);

        if (!lat || !lng) {
            showFeeOnCard(addrId, null, null, 'Chưa có tọa độ GPS');
            updateSummary(null, 'Chưa có toạ độ GPS');
            return;
        }

        fetchShippingFee(addrId, lat, lng, true);
    }

    function fetchShippingFee(addrId, lat, lng, isSelected = false) {
        const badge = document.getElementById('fee-addr-' + addrId);
        if (!badge) return;

        badge.innerHTML = '<span class="badge bg-light text-secondary border"><i class="bi bi-arrow-repeat spin"></i> Đang tính...</span>';
        if (isSelected && summaryShip) summaryShip.textContent = 'Đang tính...';
        if (isSelected && shippingServiceFeeLabel) shippingServiceFeeLabel.textContent = 'Đang tính phí...';

        fetch('/api/shipping-fee', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ address_id: addrId, order_total: baseAmount }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                showFeeOnCard(addrId, null, null, data.error);
                if (isSelected) updateSummary(null, data.error);
                return;
            }
            showFeeOnCard(addrId, data.fee, data.distance_km, data.reason, data.is_free);
            if (isSelected) updateSummary(data.is_free ? 0 : data.fee, null, data.distance_km, data.is_free);
        })
        .catch(() => {
            showFeeOnCard(addrId, null, null, 'Không tính được phí ship');
            if (isSelected) updateSummary(null, 'Lỗi tính phí');
        });
    }

    function updateSummary(fee, errorMsg, distKm, isFree) {
        if (!summaryTotal || !hiddenShipFee) return;

        if (fee !== null && fee !== undefined) {
            currentCalculatedShippingFee = Number(fee) || 0;
            currentShippingFreeFlag = !!isFree;
            lastDistanceKm = distKm ?? null;

            const effectiveFee = currentShippingFreeFlag ? 0 : currentCalculatedShippingFee + selectedShippingExtraFee;
            const feeDisp = currentShippingFreeFlag ? 'Miễn phí' : effectiveFee.toLocaleString('vi-VN') + 'đ';
            const distDisp = distKm ? ' (~' + parseFloat(distKm).toFixed(1) + ' km)' : '';

            if (summaryShip) summaryShip.innerHTML = '<span class="text-' + (currentShippingFreeFlag ? 'success' : 'info') + '">' + feeDisp + distDisp + '</span>';
            if (shippingServiceFeeLabel) shippingServiceFeeLabel.textContent = feeDisp + distDisp;
            hiddenShipFee.value = effectiveFee;
            const total = baseAmount + effectiveFee - currentVoucherDiscount;
            summaryTotal.textContent = total.toLocaleString('vi-VN') + 'đ';
        } else {
            currentCalculatedShippingFee = 0;
            currentShippingFreeFlag = false;
            lastDistanceKm = null;
            if (summaryShip) summaryShip.innerHTML = '<span class="text-warning">' + (errorMsg ?? 'Không xác định') + '</span>';
            if (shippingServiceFeeLabel) shippingServiceFeeLabel.textContent = errorMsg ?? 'Không xác định';
            hiddenShipFee.value = 0;
            summaryTotal.textContent = (baseAmount - currentVoucherDiscount).toLocaleString('vi-VN') + 'đ';
        }
    }

    function showFeeOnCard(addrId, fee, distKm, reason, isFree = false) {
        const el = document.getElementById('fee-addr-' + addrId);
        if (!el) return;

        if (isFree) {
            el.innerHTML = '<span class="badge bg-success">Miễn phí vận chuyển</span>';
        } else if (fee !== null) {
            const feeStr  = fee.toLocaleString('vi-VN') + 'đ';
            const distStr = distKm ? ' (~' + parseFloat(distKm).toFixed(1) + ' km)' : '';
            el.innerHTML  = '<span class="badge bg-info text-dark">Phí ship: <strong>' + feeStr + '</strong>' + distStr + '</span>';
        } else {
            el.innerHTML = '<span class="badge bg-warning text-dark">' + (reason ?? 'Không xác định') + '</span>';
        }
    }

    /* ── 3. Hiện/ẩn QR chuyển khoản ── */
    const paymentOptions = document.querySelectorAll('.payment-option');
    const bankTransferDetails = document.getElementById('bankTransferDetails');

    function toggleBankTransferDetails() {
        const checkedPayment = document.querySelector('.payment-option:checked');
        if (!bankTransferDetails || !checkedPayment) return;
        bankTransferDetails.classList.toggle('d-none', checkedPayment.value !== 'bank_transfer');
    }

    paymentOptions.forEach(opt => opt.addEventListener('change', toggleBankTransferDetails));
    toggleBankTransferDetails();

    /* ── 4. Voucher ── */
    const voucherBtn = document.getElementById('voucherApplyBtn');
    const voucherInput = document.getElementById('voucherInput');
    const voucherMsg = document.getElementById('voucherMsg');
    const hiddenVoucherCode = document.getElementById('hiddenVoucherCode');
    const summaryVoucherRow = document.getElementById('summaryVoucherRow');
    const summaryVoucherAmount = document.getElementById('summaryVoucherAmount');
    let currentVoucherDiscount = 0;

    if (voucherBtn && '{{ $type }}' === 'order') {
        voucherBtn.addEventListener('click', async function () {
            const code = voucherInput.value.trim();
            if (!code) {
                voucherMsg.innerHTML = '<span class="text-warning">Vui lòng nhập mã giảm giá</span>';
                return;
            }

            voucherBtn.disabled = true;
            voucherBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            voucherMsg.innerHTML = '';

            try {
                const response = await fetch(`{{ route('user.payment.order.apply-voucher', $item->id ?? 0) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ voucher_code: code })
                });

                const data = await response.json();
                
                if (data.success) {
                    voucherMsg.innerHTML = `<span class="text-success"><i class="bi bi-check-circle"></i> ${data.message}</span>`;
                    hiddenVoucherCode.value = data.voucher_code;
                    currentVoucherDiscount = parseFloat(data.discount_amount);
                    
                    if (summaryVoucherRow && summaryVoucherAmount) {
                        summaryVoucherRow.classList.remove('d-none');
                        summaryVoucherAmount.textContent = '-' + currentVoucherDiscount.toLocaleString('vi-VN') + 'đ';
                    }
                    
                    updateTotal();
                } else {
                    voucherMsg.innerHTML = `<span class="text-danger"><i class="bi bi-x-circle"></i> ${data.message}</span>`;
                    hiddenVoucherCode.value = '';
                    currentVoucherDiscount = 0;
                    if (summaryVoucherRow) summaryVoucherRow.classList.add('d-none');
                    updateTotal();
                }
            } catch (error) {
                voucherMsg.innerHTML = '<span class="text-danger">Đã có lỗi xảy ra. Vui lòng thử lại.</span>';
            } finally {
                voucherBtn.disabled = false;
                voucherBtn.textContent = 'Áp dụng';
            }
        });
    }

    document.querySelectorAll('.select-voucher').forEach((button) => {
        button.addEventListener('click', () => {
            voucherInput.value = button.dataset.code;
            const modalElement = document.getElementById('voucherPickerModal');
            bootstrap.Modal.getInstance(modalElement)?.hide();
            voucherBtn.click();
        });
    });

    /* ── Update total with voucher ── */
    const bankTransferQrBaseUrl = {!! json_encode($bankTransfer['qr_url'] ?? '') !!};
    const bankTransferAmountEl = document.getElementById('bankTransferAmount');
    const bankTransferQrImageEl = document.getElementById('bankTransferQrImage');

    function updateBankTransferSummary() {
        const ship = parseFloat(hiddenShippingFee ? hiddenShippingFee.value : 0) || 0;
        const total = Math.max(0, baseAmount + ship - currentVoucherDiscount);

        if (bankTransferAmountEl) {
            bankTransferAmountEl.textContent = total.toLocaleString('vi-VN') + 'đ';
        }

        if (bankTransferQrBaseUrl && bankTransferQrImageEl) {
            let updatedQrUrl = bankTransferQrBaseUrl;
            if (updatedQrUrl.includes('amount=')) {
                updatedQrUrl = updatedQrUrl.replace(/([?&])amount=\d+/, '$1amount=' + Math.round(total));
            } else {
                updatedQrUrl += (updatedQrUrl.includes('?') ? '&' : '?') + 'amount=' + Math.round(total);
            }
            bankTransferQrImageEl.src = updatedQrUrl;
        }
    }

    function updateTotal() {
        if (!summaryTotal || '{{ $type }}' !== 'order') return;
        const ship = parseFloat(hiddenShippingFee ? hiddenShippingFee.value : 0) || 0;
        const total = Math.max(0, baseAmount + ship - currentVoucherDiscount);
        summaryTotal.textContent = total.toLocaleString('vi-VN') + 'đ';
        updateBankTransferSummary();
    }

    const originalShowFeeOnCard = showFeeOnCard;
    showFeeOnCard = function(addrId, fee, distKm, reason, isFree = false) {
        originalShowFeeOnCard(addrId, fee, distKm, reason, isFree);
        updateTotal();
    };

    const originalUpdateSummary = updateSummary;
    updateSummary = function(fee, errorMsg, distKm, isFree) {
        originalUpdateSummary(fee, errorMsg, distKm, isFree);
        updateTotal();
    };
});
</script>

@endsection
