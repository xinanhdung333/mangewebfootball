@extends('layouts.app')

@php
    $voucherTypes = [
        'fixed' => 'Giảm tiền cố định',
        'percentage' => 'Giảm theo phần trăm',
        'free_shipping' => 'Miễn phí vận chuyển',
    ];
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="bi bi-ticket-perforated text-primary me-2"></i>Quản lý Voucher</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVoucherModal">
            <i class="bi bi-plus-lg me-1"></i>Thêm mới
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i>Có lỗi xảy ra:
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Mã giảm giá</th>
                            <th>Loại ưu đãi</th>
                            <th>Mức giảm</th>
                            <th>Đơn tối thiểu</th>
                            <th>Hạn sử dụng</th>
                            <th>Trạng thái</th>
                            <th class="text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vouchers as $voucher)
                            @php($type = $voucher->discount_type ?? 'fixed')
                            <tr>
                                <td class="ps-4">#{{ $voucher->id }}</td>
                                <td><span class="badge bg-secondary font-monospace fs-6">{{ $voucher->code }}</span></td>
                                <td>{{ $voucherTypes[$type] ?? 'Giảm tiền cố định' }}</td>
                                <td class="text-danger fw-bold">
                                    @if($type === 'percentage')
                                        {{ rtrim(rtrim(number_format($voucher->discount_amount, 2, ',', '.'), '0'), ',') }}%
                                        @if($voucher->max_discount_amount)
                                            <div class="small text-muted">Tối đa {{ number_format($voucher->max_discount_amount, 0, ',', '.') }}đ</div>
                                        @endif
                                    @elseif($type === 'free_shipping')
                                        Miễn phí vận chuyển
                                    @else
                                        {{ number_format($voucher->discount_amount, 0, ',', '.') }}đ
                                    @endif
                                </td>
                                <td>{{ number_format($voucher->min_order_amount, 0, ',', '.') }}đ</td>
                                <td>
                                    @if($voucher->expires_at)
                                        {{ \Carbon\Carbon::parse($voucher->expires_at)->format('d/m/Y H:i') }}
                                        @if(\Carbon\Carbon::parse($voucher->expires_at)->isPast())
                                            <span class="badge bg-danger ms-1">Hết hạn</span>
                                        @endif
                                    @else
                                        <span class="text-muted">Không giới hạn</span>
                                    @endif
                                </td>
                                <td>
                                    @if($voucher->is_active)
                                        <span class="badge bg-success">Hoạt động</span>
                                    @else
                                        <span class="badge bg-secondary">Tạm khoá</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-primary me-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editVoucherModal-{{ $voucher->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('admin.vouchers.destroy', $voucher->id) }}" method="POST" class="d-inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc muốn xoá mã {{ $voucher->code }}?');">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Chưa có mã giảm giá nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@foreach($vouchers as $voucher)
    @include('admin.vouchers.partials.form-modal', [
        'modalId' => 'editVoucherModal-' . $voucher->id,
        'title' => 'Cập nhật Voucher: ' . $voucher->code,
        'action' => route('admin.vouchers.update', $voucher->id),
        'method' => 'PUT',
        'voucher' => $voucher,
        'voucherTypes' => $voucherTypes,
        'submitText' => 'Lưu thay đổi',
    ])
@endforeach

@include('admin.vouchers.partials.form-modal', [
    'modalId' => 'addVoucherModal',
    'title' => 'Thêm Voucher Mới',
    'action' => route('admin.vouchers.store'),
    'method' => 'POST',
    'voucher' => null,
    'voucherTypes' => $voucherTypes,
    'submitText' => 'Thêm mới',
])
@endsection

@push('scripts')
<script>
function syncVoucherForm(select) {
    const form = select.closest('form');
    const type = select.value;
    const amountGroup = form.querySelector('.voucher-amount-group');
    const maxGroup = form.querySelector('.voucher-max-group');
    const amountInput = form.querySelector('[name="discount_amount"]');
    const maxInput = form.querySelector('[name="max_discount_amount"]');
    const amountLabel = form.querySelector('.voucher-amount-label');
    const amountHint = form.querySelector('.voucher-amount-hint');

    if (!form || !amountGroup || !maxGroup || !amountInput || !maxInput) return;

    amountGroup.classList.toggle('d-none', type === 'free_shipping');
    maxGroup.classList.toggle('d-none', type !== 'percentage');
    amountInput.disabled = type === 'free_shipping';
    maxInput.disabled = type !== 'percentage';
    amountInput.required = type !== 'free_shipping';

    if (type === 'percentage') {
        amountLabel.textContent = 'Phần trăm giảm (%)';
        amountInput.placeholder = 'Ví dụ: 20';
        amountInput.max = '100';
        amountHint.textContent = 'Nhập từ 1 đến 100.';
    } else {
        amountLabel.textContent = 'Số tiền giảm (VNĐ)';
        amountInput.placeholder = 'Ví dụ: 50000';
        amountInput.removeAttribute('max');
        amountHint.textContent = 'Áp dụng cho mã giảm tiền cố định.';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.voucher-type-select').forEach((select) => {
        syncVoucherForm(select);
        select.addEventListener('change', () => syncVoucherForm(select));
    });
});
</script>
@endpush
