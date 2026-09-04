@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('admin.settings') }}" class="text-decoration-none text-muted">
                <i class="bi bi-arrow-left"></i> Quay lại cài đặt
            </a>
            <h3 class="fw-bold mt-2 mb-0">Quản lý đơn vị vận chuyển</h3>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-2">Mốc miễn phí vận chuyển</h5>
            <p class="text-muted small mb-3">Đơn hàng đạt từ mốc này sẽ được miễn phí phí vận chuyển. Nhập 0 để tắt.</p>
            <form action="{{ route('admin.shipping-methods.free-threshold') }}" method="POST" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="free_shipping_threshold">Giá trị đơn hàng tối thiểu (đ)</label>
                    <input
                        id="free_shipping_threshold"
                        type="number"
                        name="free_shipping_threshold"
                        class="form-control"
                        min="0"
                        step="1000"
                        value="{{ old('free_shipping_threshold', $freeShippingThreshold) }}"
                        required
                    >
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary">Lưu mốc miễn phí</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Thêm phương thức mới</h5>
            <form action="{{ route('admin.shipping-methods.store') }}" method="POST" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tên phương thức</label>
                    <input type="text" name="name" class="form-control" placeholder="VD: Standard" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Mã</label>
                    <input type="text" name="code" class="form-control" placeholder="standard">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Mô tả</label>
                    <input type="text" name="description" class="form-control" placeholder="2-3 ngày">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Phụ phí (đ)</label>
                    <input type="number" min="0" step="1000" name="extra_fee" class="form-control" value="0">
                </div>
                <div class="col-md-1">
                    <label class="form-label fw-semibold">Hiển thị</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                    </div>
                </div>
                <div class="col-md-1 d-grid">
                    <button type="submit" class="btn btn-primary">Thêm</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Tên</th>
                            <th>Mã</th>
                            <th>Mô tả</th>
                            <th>Phụ phí</th>
                            <th>Trạng thái</th>
                            <th class="text-end">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shippingMethods as $method)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $method->name }}</td>
                                <td><span class="badge bg-light text-dark">{{ $method->code }}</span></td>
                                <td>{{ $method->description ?? '—' }}</td>
                                <td>{{ number_format((float) $method->extra_fee, 0, ',', '.') }}đ</td>
                                <td>
                                    @if($method->is_active)
                                        <span class="badge bg-success">Hiện</span>
                                    @else
                                        <span class="badge bg-secondary">Ẩn</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        <form action="{{ route('admin.shipping-methods.update', $method) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="input-group input-group-sm" style="width: 180px;">
                                                <input type="text" class="form-control" name="name" value="{{ $method->name }}" required>
                                                <button type="submit" class="btn btn-outline-primary" title="Cập nhật">
                                                    <i class="bi bi-check2"></i>
                                                </button>
                                            </div>
                                            <div class="mt-2 d-flex gap-2 align-items-center">
                                                <input type="text" class="form-control form-control-sm" name="code" value="{{ $method->code }}" placeholder="Mã">
                                                <input type="number" class="form-control form-control-sm" name="extra_fee" min="0" step="1000" value="{{ (float) $method->extra_fee }}" style="width: 110px;">
                                            </div>
                                            <div class="mt-2 d-flex gap-2 align-items-center">
                                                <input type="text" class="form-control form-control-sm" name="description" value="{{ $method->description ?? '' }}" placeholder="Mô tả">
                                                <div class="form-check form-switch mt-1">
                                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $method->is_active ? 'checked' : '' }}>
                                                </div>
                                            </div>
                                        </form>

                                        <form action="{{ route('admin.shipping-methods.destroy', $method) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xoá phương thức này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Xoá">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Chưa có phương thức vận chuyển nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
