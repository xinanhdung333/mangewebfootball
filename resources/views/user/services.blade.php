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
<form id="searchForm" class="mb-3" style="display:flex; gap:10px; flex-wrap:wrap;">

<input type="text"
name="q"
value="{{ request('q') }}"
placeholder="Tìm theo tên..."
style="flex:1; padding:8px; border:1px solid #ccc; border-radius:4px;">

<select id="serviceSort"
style="padding:8px; border:1px solid #ccc; border-radius:4px;">

<option value="name">Sắp xếp theo tên</option>
<option value="priceAsc">Giá thấp đến cao</option>
<option value="priceDesc">Giá cao đến thấp</option>
<option value="rating">Đánh giá cao nhất</option>

</select>

<button type="submit"
style="padding:8px 15px; background:#007bff; color:white; border:none; border-radius:4px; cursor:pointer;">
Tìm kiếm
</button>

</form>


<!-- PRODUCT GRID -->
<div class="product-grid" id="productList">
@foreach($services as $service)

<div class="product-card"
data-service-id="{{ $service->id }}"
data-price="{{ $service->price }}"
data-rating="{{ $service->avg_rating ?? 0 }}">
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

const searchForm = document.getElementById('searchForm');

searchForm.addEventListener('input', function(){

    let formData = new FormData(searchForm);

    let queryString = new URLSearchParams(formData).toString();

    fetch("{{ route('user.services') }}?" + queryString,{
        headers:{
            'X-Requested-With':'XMLHttpRequest'
        }
    })

    .then(res=>res.text())

    .then(html=>{

        document.getElementById('productList').innerHTML = html;

    });

});
document.addEventListener('DOMContentLoaded', function(){

    document.querySelectorAll('.btn-add-cart').forEach(btn => {

        btn.addEventListener('click', (e) => {

            e.preventDefault();
            e.stopPropagation();

            const serviceId = btn.dataset.serviceId;

            if (!serviceId) {
                alert('Không có ID');
                return;
            }

            fetch("{{ route('user.cart.add.ajax') }}", {
                method:'POST',
                headers:{
                    'Content-Type':'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept':'application/json'
                },
                credentials:'same-origin',
                body:`service_id=${serviceId}&quantity=1`
            })
            .then(res=>res.json())
            .then(data=>{

                if(data.success){

                    const cartCount = document.getElementById('cart-count');

                    let current = parseInt(cartCount.textContent || '0');

                    current++;

                    cartCount.textContent = current;
                    cartCount.style.display='inline-block';

                    btn.style.transform='scale(1.4)';
                    setTimeout(()=>btn.style.transform='scale(1)',200);

                }else{

btn.innerHTML = "✓";

setTimeout(()=>{
    btn.innerHTML = "+";
},1000);
                }

            })
            .catch(()=>alert('Lỗi kết nối AJAX'));

        });

    });

});
document.getElementById('serviceSort').addEventListener('change', function(){

let type = this.value;

let container = document.getElementById('productList');

let items = Array.from(document.querySelectorAll('.product-card'));

items.sort(function(a,b){

let nameA = a.querySelector('.product-desc').innerText.toLowerCase();
let nameB = b.querySelector('.product-desc').innerText.toLowerCase();

let priceA = parseInt(a.dataset.price);
let priceB = parseInt(b.dataset.price);

let ratingA = parseFloat(a.dataset.rating);
let ratingB = parseFloat(b.dataset.rating);

if(type==="name") return nameA.localeCompare(nameB);

if(type==="priceAsc") return priceA-priceB;

if(type==="priceDesc") return priceB-priceA;

if(type==="rating") return ratingB-ratingA;

});

items.forEach(item=>container.appendChild(item));

});


</script>

@endsection