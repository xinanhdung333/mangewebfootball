@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-bag-check"></i> Quản lý đơn hàng</h1>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Bộ lọc -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Lọc theo khách hàng</label>
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
            <div class="col-md-4">
                <label class="form-label">Lọc theo trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">-- Tất cả --</option>
                    <option value="pending" @if(request('status') == 'pending') selected @endif>Chờ xác nhận</option>
                    <option value="confirmed" @if(request('status') == 'confirmed') selected @endif>Xác nhận</option>
                    <option value="processing" @if(request('status') == 'processing') selected @endif>Đang xử lý</option>
                    <option value="in_progress" @if(request('status') == 'in_progress') selected @endif>Đang giao</option>
                    <option value="completed" @if(request('status') == 'completed') selected @endif>Hoàn thành</option>
                    <option value="cancelled" @if(request('status') == 'cancelled') selected @endif>Hủy</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-search"></i> Lọc
                </button>
                <a href="{{ route('boss.manage.orders') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Đặt lại
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Bảng dữ liệu -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Danh sách đơn hàng ({{ $orders->total() }} đơn)</h5>
    </div>

    <div class="card-body">
        @if($orders->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Mã ĐH</th>
                            <th>Khách hàng</th>
                            <th>SĐT</th>
                            <th>Dịch vụ</th>
                            <th style="width: 100px;">Trạng thái</th>
                            <th style="width: 120px;">Tổng tiền</th>
                            <th>Ngày tạo</th>
                            <th style="width: 120px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td>
                                    <strong>#{{ $order->id }}</strong>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $order->user->name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $order->user->email ?? '' }}</small>
                                </td>
                                <td>{{ $order->user->phone ?? '---' }}</td>
                                <td>
                                    @if($order->items->count() > 0)
                                        <div class="small">
                                            @foreach($order->items as $item)
                                                <div>
                                                    • {{ $item->service->name ?? 'N/A' }} 
                                                    <span class="text-muted">(x{{ $item->quantity }})</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">---</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusClass = [
                                            'pending' => 'warning',
                                            'confirmed' => 'info',
                                            'processing' => 'primary',
                                            'in_progress' => 'secondary',
                                            'completed' => 'success',
                                            'cancelled' => 'danger',
                                        ][$order->status] ?? 'secondary';
                                        
                                        $statusText = [
                                            'pending' => 'Chờ xác nhận',
                                            'confirmed' => 'Xác nhận',
                                            'processing' => 'Đang xử lý',
                                            'in_progress' => 'Đang giao',
                                            'completed' => 'Hoàn thành',
                                            'cancelled' => 'Hủy',
                                        ][$order->status] ?? $order->status;
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}">{{ $statusText }}</span>
                                </td>
                                <td>
                                    <strong>{{ number_format($order->total_amount, 0, ',', '.') }} đ</strong>
                                </td>
                                <td>
                                    <small>{{ $order->created_at->format('d/m/Y H:i') }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('boss.edit.status.order', ['id' => $order->id]) }}" 
                                       class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" 
                                            data-bs-target="#detailModal{{ $order->id }}" title="Chi tiết">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            
                            <!-- Modal Chi tiết -->
                            <div class="modal fade" id="detailModal{{ $order->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Chi tiết đơn hàng #{{ $order->id }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <p><strong>Khách hàng:</strong> {{ $order->user->name }}</p>
                                                    <p><strong>Email:</strong> {{ $order->user->email }}</p>
                                                    <p><strong>SĐT:</strong> {{ $order->user->phone }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Mã đơn:</strong> #{{ $order->id }}</p>
                                                    <p><strong>Ngày tạo:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
                                                    <p><strong>Trạng thái:</strong> 
                                                        <span class="badge bg-{{ $statusClass }}">{{ $statusText }}</span>
                                                    </p>
                                                </div>
                                            </div>

                                            <h6 class="mb-3">Chi tiết dịch vụ:</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Dịch vụ</th>
                                                            <th style="width: 80px;">Số lượng</th>
                                                            <th style="width: 100px;">Giá</th>
                                                            <th style="width: 100px;">Thành tiền</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($order->items as $item)
                                                            <tr>
                                                                <td>{{ $item->service->name ?? 'N/A' }}</td>
                                                                <td class="text-center">{{ $item->quantity }}</td>
                                                                <td class="text-end">{{ number_format($item->price, 0, ',', '.') }} đ</td>
                                                                <td class="text-end fw-bold">{{ number_format($item->price * $item->quantity, 0, ',', '.') }} đ</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="4" class="text-center text-muted">Không có dịch vụ</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                    <tfoot>
                                                        <tr class="table-light">
                                                            <td colspan="3" class="text-end fw-bold">Tổng cộng:</td>
                                                            <td class="text-end fw-bold">{{ number_format($order->total_amount, 0, ',', '.') }} đ</td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                            <a href="{{ route('boss.edit.status.order', ['id' => $order->id]) }}" 
                                               class="btn btn-warning">
                                                <i class="bi bi-pencil"></i> Chỉnh sửa trạng thái
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center text-muted py-5">
                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                <p class="mt-3">Không có đơn hàng nào</p>
            </div>
        @endif
    </div>
</div>

<!-- Pagination -->
@if($orders->hasPages())
    <div class="mt-4">
        <nav aria-label="Page navigation">
            {{ $orders->links() }}
        </nav>
    </div>
@endif

@endsection