@extends('layouts.visitor')

@section('content')
@php $service = $service ?? []; $error = $error ?? null; @endphp

<div style="max-width:1200px; margin:30px auto; background:#fff; padding:20px; border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,0.1); font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">

    @if(request()->has('bought'))
        <div style="padding:10px; background:#d4edda; color:#155724; border-radius:8px; margin-bottom:15px;">Mua thành công!</div>
    @endif

    @if($error)
        <div style="padding:10px; background:#f8d7da; color:#721c24; border-radius:8px; margin-bottom:15px;">{{ $error }}</div>
    @endif

    <div style="display:flex; gap:20px; flex-wrap:wrap;">
        <div style="flex:7; min-width:300px;">
            <img id="mainImg" style="width:100%; height:480px; object-fit:contain; border:1px solid #ddd; border-radius:12px; background:#fff;" 
                 src="{{ !empty($service['image']) ? url('/uploads/services/'.$service['image']) : url('/assets/images/default.png') }}" alt="{{ $service['name'] ?? '' }}">
            <div id="thumbs" style="display:flex; gap:10px; margin-top:15px;">
                <img class="thumb active" src="{{ !empty($service['image']) ? url('/uploads/services/'.$service['image']) : url('/assets/images/default.png') }}" style="width:80px; height:80px; object-fit:cover; border:1px solid #ccc; border-radius:8px; cursor:pointer;">
            </div>
        </div>

        <div style="flex:5; display:flex; flex-direction:column; gap:15px;">
            <h2>{{ $service['name'] ?? '' }}</h2>
            <div>Brand: {{ $service['brand'] ?? 'N/A' }}</div>

            <div style="display:flex; align-items:end; gap:12px; margin-top:10px;">
                <div style="font-size:32px; font-weight:bold; color:#ff3b85;">{{ formatCurrency($service['price'] ?? 0) }}</div>
            </div>

            <p>Delivery: {{ $service['location'] ?? 'Tỉnh/Thành phố' }}</p>
            <p>100% Authentic • 30 Days Free Return</p>

            <h4>Quantity</h4>
            <div style="display:flex; align-items:center; gap:10px;">
                <button class="qty-btn" onclick="changeQty(-1)" style="width:32px; height:32px; border:1px solid #aaa; background:#fff; cursor:pointer;">-</button>
                <span id="qty">1</span>
                <button class="qty-btn" onclick="changeQty(1)" style="width:32px; height:32px; border:1px solid #aaa; background:#fff; cursor:pointer;">+</button>
                <span style="color:#777;">Available: {{ $service['quantity'] ?? 0 }}</span>
            </div>

            <div style="display:flex; gap:10px; margin-top:20px;">
                <a href="{{ url('/login') }}"><button id="btnAddToCart" style="flex:1; width:100%; padding:14px; border-radius:12px; cursor:pointer; font-weight:bold; border:1px solid #ff3b85; background:#ff3b85; color:#fff;">Add to Cart</button></a>

                <a href="{{ url('/login') }}"><button type="submit" name="buy_now" style="width:100%; padding:14px; border-radius:12px; cursor:pointer; font-weight:bold; border:1px solid #ff3b85; background:#fff; color:#ff3b85;">Buy Now</button></a>
            </div>

            <p style="margin-top:20px; color:#666;">Shipping fee: {{ formatCurrency($service['shipping_fee'] ?? 0) }}</p>
        </div>
    </div>
</div>

@endsection
