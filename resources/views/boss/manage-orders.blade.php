@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-cash-stack"></i> Quản lý doanh thu</h1>
    </div>
</div>

<!-- Bộ lọc -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Lọc theo người dùng</label>
                <select name="user_id" class="form-select">
                    <option value="">-- Tất cả --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" 
                            @if(request('user_id') == $u->id) selected @endif>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Lọc
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bảng dữ liệu -->
<div class="card">
    <div class="card-header">
        <h5>Danh sách doanh thu</h5>
    </div>

    <div class="card-body table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Người dùng</th>
                    <th>SĐT</th>
                    <th>Đặt sân</th>
                    <th>Dịch vụ</th>
                    <th>Tổng chi tiêu</th>
                    <th>Cập nhật lần cuối</th>
                </tr>
            </thead>
            <tbody>
                @if($orders->count() > 0)
                    @foreach($orders as $row)
                        <tr>
                            <td>#{{ $row->id }}</td>
                            <td>{{ $row->user_name ?? 'Không rõ' }}</td>
                            <td>{{ $row->user_phone ?? '---' }}</td>
                            <td>{{ number_format($row->total_booking, 0, ',', '.') }} VNĐ</td>
                            <td>{{ number_format($row->total_services, 0, ',', '.') }} VNĐ</td>
                            <td><strong>{{ number_format($row->total_spent, 0, ',', '.') }} VNĐ</strong></td>
                            <td>{{ $row->last_update }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="7" class="text-center">Không có dữ liệu</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

@if($orders->hasPages())
    <div class="mt-3">
        {{ $orders->links() }}
    </div>
@endif

@endsection