@foreach($services as $service)

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

<button class="btn-add-cart"
data-service-id="{{ $service->id }}">
+
</button>

</div>

@endforeach