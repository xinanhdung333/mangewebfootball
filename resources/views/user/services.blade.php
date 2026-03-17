@extends('layouts.app')

@section('content')

<div style="max-width:1200px; margin:20px auto; padding:0 10px;">

<!-- CART ICON -->
<div style="position:relative; display:inline-block; margin-left:20px;">
<a href="{{ route('user.cart') }}" id="cart-icon"
style="position:relative; display:flex; align-items:center; text-decoration:none; color:#333; font-size:24px;">
<i class="bi bi-cart-fill"></i>

@if(!empty($totalItems) && $totalItems > 0)
<span id="cart-count"
style="position:absolute; top:-5px; right:-10px; background:red; color:white; font-size:12px; padding:2px 6px; border-radius:50%;">
{{ $totalItems }}
</span>
@else
<span id="cart-count" style="display:none;"></span>
@endif

</a>
</div>


<!-- SEARCH -->
<form method="GET" class="mb-3" style="display:flex; gap:10px; flex-wrap:wrap;">

<input type="text"
name="q"
value="{{ request('q') }}"
placeholder="Tìm theo tên..."
style="flex:1; padding:8px; border:1px solid #ccc; border-radius:4px;">

<input type="number"
name="min"
value="{{ request('min') }}"
placeholder="Giá tối thiểu"
style="width:150px; padding:8px; border:1px solid #ccc; border-radius:4px;">

<input type="number"
name="max"
value="{{ request('max') }}"
placeholder="Giá tối đa"
style="width:150px; padding:8px; border:1px solid #ccc; border-radius:4px;">

<button type="submit"
style="padding:8px 15px; background:#007bff; color:white; border:none; border-radius:4px; cursor:pointer;">
Tìm kiếm
</button>

</form>


<!-- PRODUCT GRID -->
<div class="product-grid">

@foreach($services as $service)

<div class="product-card" data-service-id="{{ $service->id }}">

<a href="{{ route('user.serviceDetail', $service->id) }}">

<img
src="{{ $service->image ? asset('uploads/services/'.$service->image) : asset('assets/images/default.png') }}"
alt="{{ $service->name }}"
class="product-image">

</a>

<div class="product-desc">
{{ $service->name }}
</div>

<div class="product-price">
{{ formatCurrency($service->price) }}
</div>


@php
$avg = $service->avg_rating ? number_format($service->avg_rating,1) : 0;
$total = $service->total_reviews ?? 0;
@endphp


<div class="mb-2">

@for ($i = 1; $i <= 5; $i++)
<span style="color: gold; font-size: 18px;">
@if($i <= $avg)
★
@else
☆
@endif
</span>
@endfor

<span class="text-muted">
({{ $avg }} / 5 , {{ $total }} đánh giá)
</span>

</div>


<button class="btn-add-cart" data-service-id="{{ $service->id }}">
+
</button>

</div>

@endforeach

</div>

</div>


<style>

.product-grid{
display:grid;
grid-template-columns:repeat(auto-fill,minmax(180px,1fr));
gap:15px;
}

.product-desc{
padding:5px 0;
font-weight:500;
color:#333;
font-size:0.9rem;
white-space:nowrap;
overflow:hidden;
text-overflow:ellipsis;
}

.product-card{
display:flex;
flex-direction:column;
text-decoration:none;
background:#fff;
border-radius:8px;
overflow:hidden;
box-shadow:0 2px 8px rgba(0,0,0,0.1);
transition:transform 0.2s, box-shadow 0.2s;
text-align:center;
position:relative;
}

.product-card:hover{
transform:translateY(-3px);
box-shadow:0 6px 15px rgba(0,0,0,0.15);
}

.product-image{
width:100%;
height:140px;
object-fit:cover;
}

.product-price{
padding:8px 0;
font-weight:700;
color:#e53935;
font-size:1.1rem;
}

.btn-add-cart{
margin:5px auto 10px;
padding:5px 10px;
background:#28a745;
color:white;
border:none;
border-radius:50%;
cursor:pointer;
font-size:1.2rem;
font-weight:bold;
width:36px;
height:36px;
line-height:26px;
transition:transform 0.2s, background 0.2s;
}

.btn-add-cart:hover{
background:#218838;
transform:scale(1.2);
}

</style>


<script>

document.querySelectorAll('.btn-add-cart').forEach(btn => {

btn.addEventListener('click', () => {

const serviceId = btn.dataset.serviceId;

fetch("{{ route('cart.add.ajax') }}",{

method:'POST',

headers:{
'Content-Type':'application/x-www-form-urlencoded',
'X-CSRF-TOKEN': '{{ csrf_token() }}'
},

body:`service_id=${serviceId}&quantity=1`

})

.then(res=>res.json())

.then(data=>{

if(data.success){

const cartCount = document.getElementById('cart-count');

let current = parseInt(cartCount.textContent || '0');

current += 1;

cartCount.textContent = current;

cartCount.style.display='inline-block';

btn.style.transform='scale(1.4)';

setTimeout(()=>btn.style.transform='scale(1.2)',200);

}else{

alert(data.error || 'Lỗi thêm vào giỏ hàng');

}

})

.catch(()=>alert('Lỗi kết nối AJAX'));

});

});

</script>

@endsection