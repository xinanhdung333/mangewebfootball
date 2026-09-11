@php
    $type = $voucher->discount_type ?? 'fixed';
@endphp

<div class="modal" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ $action }}" method="POST">
                @csrf
                @if($method !== 'POST')
                    @method($method)
                @endif

                <div class="modal-header">
                    <h5 class="modal-title">{{ $title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mã giảm giá</label>
                        <input type="text" name="code" class="form-control" value="{{ old('code', $voucher->code ?? '') }}" required placeholder="Ví dụ: SALE50K">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Loại ưu đãi</label>
                        <select name="discount_type" class="form-select voucher-type-select" required>
                            @foreach($voucherTypes as $value => $label)
                                <option value="{{ $value }}" @selected(old('discount_type', $type) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3 voucher-amount-group">
                        <label class="form-label fw-bold voucher-amount-label">Số tiền giảm (VNĐ)</label>
                        <input type="number" name="discount_amount" class="form-control" value="{{ old('discount_amount', $voucher->discount_amount ?? '') }}" min="0" step="0.01">
                        <small class="text-muted voucher-amount-hint">Áp dụng cho mã giảm tiền cố định.</small>
                    </div>

                    <div class="mb-3 voucher-max-group">
                        <label class="form-label fw-bold">Giảm tối đa (chỉ mã %)</label>
                        <input type="number" name="max_discount_amount" class="form-control" value="{{ old('max_discount_amount', $voucher->max_discount_amount ?? '') }}" min="0" step="1000" placeholder="Ví dụ: 100000">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Đơn tối thiểu (VNĐ)</label>
                        <input type="number" name="min_order_amount" class="form-control" value="{{ old('min_order_amount', $voucher->min_order_amount ?? 0) }}" min="0" step="1000" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Hạn sử dụng</label>
                        <input type="datetime-local" name="expires_at" class="form-control" value="{{ old('expires_at', $voucher && $voucher->expires_at ? \Carbon\Carbon::parse($voucher->expires_at)->format('Y-m-d\TH:i') : '') }}">
                        <small class="text-muted">Để trống nếu không giới hạn</small>
                    </div>

                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="{{ $modalId }}-active" {{ old('is_active', $voucher->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="{{ $modalId }}-active">Kích hoạt</label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Huỷ</button>
                    <button type="submit" class="btn btn-primary">{{ $submitText }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
