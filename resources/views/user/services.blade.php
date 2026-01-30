@extends('layouts.app')

@section('content')
<div style="max-width:1200px; margin:20px auto; padding:0 10px;">
    <h1><i class="bi bi-bag"></i> Dịch vụ & Đồ ăn</h1>

    <!-- Giỏ hàng icon -->
    <div style="position:fixed; bottom:20px; right:20px; z-index:1000;">
        <a href="{{ route('cart.index') }}" id="cart-icon" style="position:relative; display:flex; align-items:center; text-decoration:none; color:#333; font-size:24px; background:#fff; padding:15px; border-radius:50%; box-shadow:0 4px 12px rgba(0,0,0,0.15);">
            <i class="bi bi-cart-fill"></i>
            @if($totalItems > 0)
                <span id="cart-count" style="position:absolute; top:-5px; right:-10px; background:red; color:white; font-size:12px; padding:2px 6px; border-radius:50%;">{{ $totalItems }}</span>
            @endif
        </a>
    </div>

    <!-- Filter -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="q" class="form-control" placeholder="Tìm dịch vụ..." value="{{ request('q') }}">
                </div>
                <div class="col-md-3">
                    <input type="number" name="min" class="form-control" placeholder="Giá tối thiểu" value="{{ request('min') }}">
                </div>
                <div class="col-md-3">
                    <input type="number" name="max" class="form-control" placeholder="Giá tối đa" value="{{ request('max') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Lọc</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Danh sách dịch vụ -->
    <div class="row">
        @if($services && count($services) > 0)
            @foreach($services as $service)
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="{{ !empty($service->image) ? asset('uploads/services/'.$service->image) : asset('assets/images/no-image.png') }}" 
                             class="card-img-top" alt="{{ $service->name }}" style="height:200px; object-fit:cover;">
                        <div class="card-body">
                            <h5 class="card-title">{{ $service->name }}</h5>
                            <p class="text-success fw-bold">{{ formatCurrency($service->price) }}</p>
                            <p class="text-muted small">Còn: {{ $service->quantity }} cái</p>

                            @php
                                $avg = $service->avg_rating ? round($service->avg_rating, 1) : 0;
                                $total = $service->total_reviews ?? 0;
                            @endphp
                            <div class="mb-3">
                                @for ($i = 1; $i <= 5; $i++)
                                    <span style="color: gold; font-size: 16px;">
                                        @if($i <= $avg)★@else☆@endif
                                    </span>
                                @endfor
                                <span class="text-muted small">({{ $total }})</span>
                            </div>

                            <form action="{{ route('cart.add') }}" method="POST" class="d-flex gap-2">
                                @csrf
                                <input type="hidden" name="service_id" value="{{ $service->id }}">
                                <input type="number" name="quantity" class="form-control" value="1" min="1" max="{{ $service->quantity }}" style="max-width:70px;">
                                <button type="submit" class="btn btn-primary flex-grow-1">
                                    <i class="bi bi-cart-plus"></i> Thêm
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-12">
                <div class="alert alert-info">Không tìm thấy dịch vụ nào.</div>
            </div>
        @endif
    </div>
</div>
@endsection
