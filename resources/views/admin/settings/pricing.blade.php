@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="fw-bold mb-4">Cài đặt giá & giảm giá</h2>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <h4 class="mt-4 mb-3">Giá sân theo khung giờ</h4>
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.settings.pricing.store') }}">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Sân</label>
                        <select name="field_id" class="form-control">
                            <option value="">Toàn bộ</option>
                            @foreach($fields as $field)
                                <option value="{{ $field->id }}">{{ $field->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Từ</label>
                        <input type="time" name="start_time" class="form-control" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Đến</label>
                        <input type="time" name="end_time" class="form-control" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Hệ số</label>
                        <input type="number" step="0.1" name="multiplier" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <button class="btn btn-primary w-100">+ Thêm</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle bg-white">
            <thead class="table-light">
                <tr>
                    <th>Sân</th>
                    <th>Khung giờ</th>
                    <th>Hệ số</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rules as $rule)
                    <tr class="{{ $rule->is_active ? '' : 'table-secondary opacity-75' }}">
                        <td>{{ $rule->field->name ?? 'Toàn bộ' }}</td>
                        <td>{{ $rule->start_time }} - {{ $rule->end_time }}</td>
                        <td>x{{ $rule->multiplier }}</td>
                        <td>
                            @if($rule->is_active)
                                <span class="badge bg-success">Hoạt động</span>
                            @else
                                <span class="badge bg-secondary">Tạm dừng</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-end gap-2">
                                <form method="POST" action="{{ route('admin.settings.pricing.toggle', $rule->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm {{ $rule->is_active ? 'btn-warning' : 'btn-success' }}" type="submit">
                                        {{ $rule->is_active ? 'Tạm dừng' : 'Bật lại' }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.settings.pricing.delete', $rule->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button onclick="return confirm('Xoá khung giá này?')" class="btn btn-danger btn-sm">Xoá</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Chưa có khung giá sân nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <hr>

    <h4 class="mt-4 mb-3">Giảm giá dịch vụ</h4>
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.settings.service-discount.store') }}">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Dịch vụ</label>
                        <select name="service_id" class="form-control">
                            <option value="">Toàn bộ</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Từ</label>
                        <input type="time" name="start_time" class="form-control" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Đến</label>
                        <input type="time" name="end_time" class="form-control" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Hệ số</label>
                        <input type="number" step="0.1" name="multiplier" class="form-control" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Lý do</label>
                        <input type="text" name="note" class="form-control" placeholder="VD: Sale 2/9">
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-success w-100">+ Thêm</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle bg-white">
            <thead class="table-light">
                <tr>
                    <th>Dịch vụ</th>
                    <th>Khung giờ</th>
                    <th>Giảm / Tăng</th>
                    <th>Lý do</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($serviceDiscounts as $discount)
                    <tr class="{{ !$discount->is_active ? 'table-secondary opacity-75' : ($discount->multiplier < 1 ? 'table-success' : 'table-warning') }}">
                        <td>{{ $discount->service->name ?? 'Toàn bộ' }}</td>
                        <td>{{ $discount->start_time }} - {{ $discount->end_time }}</td>
                        <td>
                            @if($discount->multiplier < 1)
                                <span class="text-success fw-bold">-{{ (1 - $discount->multiplier) * 100 }}%</span>
                            @else
                                <span class="text-danger fw-bold">+{{ ($discount->multiplier - 1) * 100 }}%</span>
                            @endif
                        </td>
                        <td>
                            @if($discount->note)
                                <span class="badge bg-danger">{{ $discount->note }}</span>
                            @else
                                <span class="text-muted">---</span>
                            @endif
                        </td>
                        <td>
                            @if($discount->is_active)
                                <span class="badge bg-success">Hoạt động</span>
                            @else
                                <span class="badge bg-secondary">Tạm dừng</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-end gap-2">
                                <form method="POST" action="{{ route('admin.settings.service-discount.toggle', $discount->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm {{ $discount->is_active ? 'btn-warning' : 'btn-success' }}" type="submit">
                                        {{ $discount->is_active ? 'Tạm dừng' : 'Bật lại' }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.settings.service-discount.delete', $discount->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button onclick="return confirm('Xoá giảm giá này?')" class="btn btn-danger btn-sm">Xoá</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Chưa có giảm giá dịch vụ nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
