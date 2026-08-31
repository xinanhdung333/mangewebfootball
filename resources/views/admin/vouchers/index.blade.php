@extends('layouts.app')

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
                            <th>Mã Giảm Giá</th>
                            <th>Mức Giảm</th>
                            <th>Đơn Tối Thiểu</th>
                            <th>Hạn Sử Dụng</th>
                            <th>Trạng Thái</th>
                            <th class="text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vouchers as $voucher)
                        <tr>
                            <td class="ps-4">#{{ $voucher->id }}</td>
                            <td><span class="badge bg-secondary font-monospace fs-6">{{ $voucher->code }}</span></td>
                            <td class="text-danger fw-bold">{{ number_format($voucher->discount_amount, 0, ',', '.') }}đ</td>
                            <td>{{ number_format($voucher->min_order_amount, 0, ',', '.') }}đ</td>
                            <td>
                                @if($voucher->expires_at)
                                    {{ \Carbon\Carbon::parse($voucher->expires_at)->format('d/m/Y H:i') }}
                                    @if(\Carbon\Carbon::parse($voucher->expires_at)->isPast())
                                        <span class="badge bg-danger ms-1">Hết hạn</span>
                                    @endif
                                @else
                                    <span class="text-muted">Vĩnh viễn</span>
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

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editVoucherModal-{{ $voucher->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.vouchers.update', $voucher->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Cập nhật Voucher: {{ $voucher->code }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Mã giảm giá</label>
                                                <input type="text" name="code" class="form-control" value="{{ $voucher->code }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Số tiền giảm (VNĐ)</label>
                                                <input type="number" name="discount_amount" class="form-control" value="{{ $voucher->discount_amount }}" min="0" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Đơn tối thiểu (VNĐ)</label>
                                                <input type="number" name="min_order_amount" class="form-control" value="{{ $voucher->min_order_amount }}" min="0" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Hạn sử dụng</label>
                                                <input type="datetime-local" name="expires_at" class="form-control" value="{{ $voucher->expires_at ? \Carbon\Carbon::parse($voucher->expires_at)->format('Y-m-d\TH:i') : '' }}">
                                                <small class="text-muted">Để trống nếu không giới hạn</small>
                                            </div>
                                            <div class="form-check form-switch mt-3">
                                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="activeSwitch{{ $voucher->id }}" {{ $voucher->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label" for="activeSwitch{{ $voucher->id }}">Kích hoạt</label>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Huỷ</button>
                                            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Chưa có mã giảm giá nào.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addVoucherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.vouchers.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Thêm Voucher Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mã giảm giá</label>
                        <input type="text" name="code" class="form-control" required placeholder="Ví dụ: SALE50K">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Số tiền giảm (VNĐ)</label>
                        <input type="number" name="discount_amount" class="form-control" min="0" required placeholder="50000">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Đơn tối thiểu (VNĐ)</label>
                        <input type="number" name="min_order_amount" class="form-control" min="0" value="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Hạn sử dụng</label>
                        <input type="datetime-local" name="expires_at" class="form-control">
                        <small class="text-muted">Để trống nếu không giới hạn</small>
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="addActiveSwitch" checked>
                        <label class="form-check-label" for="addActiveSwitch">Kích hoạt ngay</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Huỷ</button>
                    <button type="submit" class="btn btn-primary">Thêm mới</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
