@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-bag-check"></i> Quản Lý Đơn Hàng</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Danh sách đơn hàng</h5>
            </div>
            <div class="card-body">
                @if ($orders && $orders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Ngày tạo</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>
                                        <td>#{{ $order->id }}</td>
                                        <td>{{ $order->created_at }}</td>
                                        <td><span class="badge bg-secondary">Pending</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary">Chi tiết</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if ($orders->hasPages())
                        <div class="mt-3">
                            {{ $orders->links() }}
                        </div>
                    @endif
                @else
                    <p class="text-muted">Không có đơn hàng nào</p>
                @endif
            </div>
        </div>
    </div>
</div>
