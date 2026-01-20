@extends('layouts.visitor')

@section('content')
<div style="max-width:1200px; margin:30px auto;">
    <div style="display:flex; gap:20px; flex-wrap:wrap;">
        <div style="flex:7; min-width:300px;">
            <img id="mainImg" style="width:100%; height:480px; object-fit:contain; border:1px solid #ddd; border-radius:12px; background:#fff;" 
                 src="{{ $service->image ? asset('uploads/services/'.$service->image) : asset('assets/images/default.png') }}" 
                 alt="{{ $service->name }}">
            <div id="thumbs" style="display:flex; gap:10px; margin-top:15px;">
                <img class="thumb active" src="{{ $service->image ? asset('uploads/services/'.$service->image) : asset('assets/images/default.png') }}" 
                     style="width:80px; height:80px; object-fit:cover; border:1px solid #ccc; border-radius:8px; cursor:pointer;">
            </div>
        </div>

        <div style="flex:5; display:flex; flex-direction:column; gap:15px;">
            <h2>{{ $service->name }}</h2>
            <div>Brand: {{ $service->brand ?? 'N/A' }}</div>

            <div style="display:flex; align-items:end; gap:12px; margin-top:10px;">
                <div style="font-size:32px; font-weight:bold; color:#ff3b85;">{{ \App\Helpers\Formatter::formatCurrency($service->price) }}</div>
                @if(!empty($service->old_price))
                    <div style="text-decoration:line-through; color:#666; font-size:14px;">{{ \App\Helpers\Formatter::formatCurrency($service->old_price) }}</div>
                    <div style="color:#19a463; font-size:14px; font-weight:bold;">-{{ round((($service->old_price-$service->price)/$service->old_price)*100) }}%</div>
                @endif
            </div>

            <p>Delivery: {{ $service->location ?? 'Tỉnh/Thành phố' }}</p>
            <p>100% Authentic • 30 Days Free Return</p>

            <h4>Quantity</h4>
            <div style="display:flex; align-items:center; gap:10px;">
                <button class="qty-btn btn btn-light" onclick="changeQty(-1)" style="width:32px; height:32px;">-</button>
                <span id="qty">1</span>
                <button class="qty-btn btn btn-light" onclick="changeQty(1)" style="width:32px; height:32px;">+</button>
                <span style="color:#777;">Available: {{ $service->quantity ?? 0 }}</span>
            </div>

            <div style="display:flex; gap:10px; margin-top:20px;">
                @guest
                    <a href="{{ route('login') }}" class="w-100">
                        <button id="btnAddToCart" class="btn btn-primary w-100">Add to Cart</button>
                    </a>
                    <a href="{{ route('login') }}" class="w-100">
                        <button class="btn btn-outline-primary w-100">Buy Now</button>
                    </a>
                @else
                    <form method="POST" action="{{ route('cart.add') }}" class="d-flex gap-2 w-100">
                        @csrf
                        <input type="hidden" name="service_id" value="{{ $service->id }}">
                        <input type="hidden" name="quantity" id="inputQty" value="1">
                        <button type="submit" class="btn btn-primary flex-grow-1">Add to Cart</button>
                    </form>
                    <form method="POST" action="{{ route('checkout') }}" class="d-flex gap-2 w-100">
                        @csrf
                        <input type="hidden" name="service_id" value="{{ $service->id }}">
                        <input type="hidden" name="quantity" id="inputQtyBuy" value="1">
                        <button type="submit" class="btn btn-outline-primary flex-grow-1">Buy Now</button>
                    </form>
                @endguest
            </div>

            <p style="margin-top:20px; color:#666;">Shipping fee: {{ \App\Helpers\Formatter::formatCurrency($service->shipping_fee ?? 0) }}</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
function changeQty(delta){
    const qtyEl = document.getElementById('qty');
    const inputQty = document.getElementById('inputQty');
    const inputQtyBuy = document.getElementById('inputQtyBuy');
    let v = parseInt(qtyEl.innerText||'1');
    v = Math.max(1, v + delta);
    qtyEl.innerText = v;
    if(inputQty) inputQty.value = v;
    if(inputQtyBuy) inputQtyBuy.value = v;
}
// thumbnail click
document.querySelectorAll('#thumbs .thumb').forEach(t=> t.addEventListener('click', function(){ document.getElementById('mainImg').src = this.src; }));
</script>
@endpush

@endsection
