@extends('layouts.visitor')

@section('content')
@php
    $services = $services ?? [];
    $total_items = $total_items ?? 0;
    $search = $search ?? '';
    $min_price = $min_price ?? '';
    $max_price = $max_price ?? '';
@endphp

<div style="max-width:1200px; margin:20px auto; padding:0 10px;">
  <div style="position:relative; display:inline-block; margin-left:20px;">
    <a href="{{ url('/pages/cart.php') }}" id="cart-icon" style="position:relative; display:flex; align-items:center; text-decoration:none; color:#333; font-size:24px;">
        <i class="bi bi-cart-fill"></i>
        @if($total_items > 0)
            <span id="cart-count" style="position:absolute; top:-5px; right:-10px; background:red; color:white; font-size:12px; padding:2px 6px; border-radius:50%;">{{ $total_items }}</span>
        @endif
    </a>
  </div>

    <form method="GET" class="mb-3" style="display:flex; gap:10px; flex-wrap:wrap;">
        <input type="text" name="q" value="{{ $search }}" placeholder="Tìm theo tên..." style="flex:1; padding:8px; border:1px solid #ccc; border-radius:4px;">
        <input type="number" name="min" value="{{ $min_price }}" placeholder="Giá tối thiểu" style="width:150px; padding:8px; border:1px solid #ccc; border-radius:4px;">
        <input type="number" name="max" value="{{ $max_price }}" placeholder="Giá tối đa" style="width:150px; padding:8px; border:1px solid #ccc; border-radius:4px;">
        <button type="submit" style="padding:8px 15px; background:#007bff; color:white; border:none; border-radius:4px; cursor:pointer;">Tìm kiếm</button>
    </form>

    <div class="product-grid">
        @foreach($services as $service)
            <div class="product-card" data-service-id="{{ $service['id'] }}">
                <a href="{{ url('pages/service-detail.php') }}?id={{ $service['id'] }}">
                    <img src="{{ !empty($service['image']) ? url('/uploads/services/'.$service['image']) : url('/assets/images/default.png') }}" alt="{{ $service['name'] }}" class="product-image">
                </a>
                <div class="product-desc">{{ $service['name'] }}</div>
                <div class="product-price">{{ formatCurrency($service['price']) }}</div>
                @php $avg = isset($service['avg_rating']) ? number_format($service['avg_rating'],1) : 0; $total = $service['total_reviews'] ?? 0; @endphp
                <div class="mb-2">
                @for ($i=1;$i<=5;$i++)
                    <span style="color: gold; font-size: 18px;">{!! ($i <= $avg) ? '★' : '☆' !!}</span>
                @endfor
                <span class="text-muted">({{ $avg }} / 5, {{ $total }} đánh giá)</span>
                </div>

                <a href="{{ url('/login') }}"><button class="btn-add-cart">+</button></a>
            </div>
        @endforeach
    </div>

<style>
.product-grid { display: grid; grid-template-columns: repeat(auto-fill,minmax(180px,1fr)); gap: 15px; }
.product-desc { padding: 5px 0; font-weight: 500; color: #333; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.product-card { display: flex; flex-direction: column; text-decoration: none; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.1); transition: transform 0.2s, box-shadow 0.2s; text-align:center; position: relative; }
.product-image { width:100%; height:140px; object-fit:cover; }
.product-price { padding:8px 0; font-weight:700; color:#e53935; font-size:1.1rem; }
.btn-add-cart { margin: 5px auto 10px; padding: 5px 10px; background: #28a745; color: white; border: none; border-radius: 50%; cursor: pointer; font-size: 1.2rem; font-weight: bold; width: 36px; height: 36px; line-height: 26px; transition: transform 0.2s, background 0.2s; }
</style>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
@endsection
