@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-bag-check"></i> Lịch sử dịch vụ đã mua</h1>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Khách hàng</th>
                <th>Ảnh</th>
                <th>Dịch vụ</th>
                <th>Số lượng</th>
                <th>Thành tiền</th>
                <th>Ngày mua</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    <td>{{ $row->user_name }}</td>
                    <td>
                        @if($row->image)
                            <img src="{{ asset('uploads/services/' . $row->image) }}" width="60" alt="Service">
                        @else
                            <img src="{{ asset('assets/images/no-image.png') }}" width="60" alt="No image">
                        @endif
                    </td>
                    <td>{{ $row->name }}</td>
                    <td>{{ $row->quantity }}</td>
                    <td>{{ number_format($row->quantity * $row->price, 0, ',', '.') }} VNĐ</td>
                    <td>{{ \Carbon\Carbon::parse($row->created_at)->format('d/m/Y H:i') }}</td>
                    <td>
                        @php
                            $statusColors = [
                                'pending'    => 'secondary',
                                'confirmed'  => 'info',
                                'processing' => 'primary',
                                'completed'  => 'success',
                                'cancelled'  => 'danger'
                            ];
                            $statusColor = $statusColors[$row->status] ?? 'dark';
                        @endphp
                        <span class="badge bg-{{ $statusColor }}">
                            {{ ucfirst($row->status) }}
                        </span>
                    </td>
                    <td>
                        @if($row->status === 'completed')
                            <a href="{{ route('boss.export.invoice', ['type' => 'service', 'id' => $row->order_id]) }}" class="btn btn-success btn-sm" title="Xuất hóa đơn">
                                <i class="bi bi-file-earmark-pdf"></i> Hóa đơn
                            </a>
                        @endif
                        <a href="{{ route('boss.edit.status') }}?id={{ $row->order_id }}" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil-square"></i> Sửa
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">Không có dữ liệu</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($data->hasPages())
    <div class="mt-3">
        {{ $data->links() }}
    </div>
@endif

@endsection