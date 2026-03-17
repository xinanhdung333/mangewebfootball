@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1><i class="bi bi-file-earmark-pdf"></i> Xuất Hóa Đơn PDF</h1>
            <p class="text-muted">Chọn loại hóa đơn và nhập ID để xuất file PDF</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Tạo Hóa Đơn</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('boss.export.invoice') }}" class="needs-validation">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="type" class="form-label"><strong>Loại Hóa Đơn</strong></label>
                            <select name="type" id="type" class="form-select" required>
                                <option value="">-- Chọn loại --</option>
                                <option value="booking">Booking Sân</option>
                                <option value="service">Đơn Dịch Vụ</option>
                            </select>
                            <small class="text-muted">Chọn loại hóa đơn cần xuất</small>
                        </div>

                        <div class="mb-3">
                            <label for="id" class="form-label"><strong>ID Hóa Đơn</strong></label>
                            <input type="number" name="id" id="id" class="form-control" required placeholder="Nhập ID" min="1">
                            <small class="text-muted">Nhập ID của booking hoặc đơn dịch vụ</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-download"></i> Xuất PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="alert alert-info mt-3" role="alert">
                <i class="bi bi-info-circle"></i>
                <strong>Lưu ý:</strong> Chỉ có thể xuất hóa đơn cho những đơn có trạng thái "Đã xác nhận" hoặc "Hoàn thành"
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Hướng Dẫn Sử Dụng</h5>
                </div>
                <div class="card-body">
                    <h6>Bước 1: Chọn Loại Hóa Đơn</h6>
                    <ul>
                        <li><strong>Booking Sân:</strong> Cho các đơn đặt sân</li>
                        <li><strong>Dịch Vụ:</strong> Cho các đơn mua dịch vụ</li>
                    </ul>

                    <h6 class="mt-3">Bước 2: Nhập ID</h6>
                    <ul>
                        <li>ID Booking: Lấy từ danh sách "Quản Lý Booking"</li>
                        <li>ID Đơn Dịch Vụ: Lấy từ danh sách "Lịch Sử Mua Dịch Vụ"</li>
                    </ul>

                    <h6 class="mt-3">Bước 3: Xuất PDF</h6>
                    <ul>
                        <li>Nhấn "Xuất PDF" để tải file</li>
                        <li>File sẽ được tải về máy tính của bạn</li>
                    </ul>

                    <div class="alert alert-warning mt-3 mb-0">
                        <small><strong>⚠️ Lưu ý:</strong> Chỉ các đơn đã xác nhận hoặc hoàn thành mới có thể xuất hóa đơn</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection