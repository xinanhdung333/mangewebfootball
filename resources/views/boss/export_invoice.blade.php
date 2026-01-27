@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-file-earmark-pdf"></i> Xuất Hóa Đơn</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Xuất Báo Cáo Hóa Đơn</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Tính năng này đang được phát triển. Vui lòng quay lại sau.</p>
                <a href="{{ route('boss.invoices') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-arrow-left"></i> Quay lại danh sách hóa đơn
                </a>
            </div>
        </div>
    </div>
</div>
