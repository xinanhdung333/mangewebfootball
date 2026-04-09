
@foreach($services as $service)
@php
$avg = $service->avg_rating ? number_format($service->avg_rating,1) : 0;
$total = $service->total_reviews ?? 0;
@endphp
<div class="product-card" data-service-id="{{ $service->id }}">

<a href="{{ route('user.serviceDetail', $service->id) }}">

<img
src="{{ $service->image ? asset('uploads/services/'.$service->image) : asset('assets/images/default.png') }}"
class="product-image">

</a>

<div class="product-desc">
{{ $service->name }}
</div>

<div class="product-price">
{{ formatCurrency($service->price) }}
</div>

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
<button class="btn-add-cart"
data-service-id="{{ $service->id }}">
+
</button>

</div>

@endforeach