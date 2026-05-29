@extends('layouts.app')

@section('content')
<div class="container py-5">
<div class="row justify-content-center">
<div class="col-lg-10">

<div class="card shadow border-0 back">
<div class="card-body p-4 p-md-5">


{{-- HEADER --}}
<div class="text-center mb-4">

<span class="badge bg-primary mb-2">
{{ $type === 'booking' ? 'BOOKING' : 'ORDER' }}
</span>

<h2 class="fw-bold">
Chọn phương thức thanh toán
</h2>

<p class="text-muted mb-1">
{{ $description }}
</p>

<div class="fs-3 fw-bold text-primary">
{{ number_format($amount,0,',','.') }}đ
</div>

</div>



{{-- BOOKING INFO --}}
@if($type === 'booking')

<div class="card bg-light border-0 mb-4 ll">
<div class="card-body ">

<div class="row align-items-center">


{{-- IMAGE --}}
<div class="col-md-3 text-center">

<img
src="{{ !empty($item->field->image)
? asset('uploads/fields/'.$item->field->image)
: asset('assets/images/banner.jpg') }}"
class="img-fluid rounded shadow-sm"
style="max-height:120px"
>

</div>


{{-- INFO --}}
<div class="col-md-9">

<h5 class="fw-semibold">
{{ $item->field->name }}
</h5>

<div class="text-muted small mb-2">

📅 {{ \Carbon\Carbon::parse($item->booking_date)->format('d/m/Y') }}

|

⏰ {{ substr($item->start_time,0,5) }}
-
{{ substr($item->end_time,0,5) }}

</div>


{{-- BOOKING SERVICES --}}
@if(isset($services) && $services->count())

<div class="mt-3">

<div class="fw-semibold mb-2">
Dịch vụ đi kèm
</div>

<ul class="list-group list-group-flush">

@foreach($services as $service)

<li class="list-group-item d-flex justify-content-between align-items-center px-0">

<div class="d-flex align-items-center gap-3">

<img
src="{{ !empty($service->image)
? asset('uploads/services/'.$service->image)
: asset('assets/images/banner.jpg') }}"
width="45"
height="45"
style="object-fit:cover;border-radius:8px"
>

<div>

<div>{{ $service->name }}</div>

<div class="small text-muted">
x {{ $service->quantity }}
</div>

</div>

</div>

<span class="text-muted">

{{ number_format(
$service->price * $service->quantity,
0,
',',
'.'
) }}đ

</span>

</li>

@endforeach

</ul>

</div>

@endif

</div>

</div>

</div>
</div>

@endif



{{-- ORDER INFO --}}
@if($type === 'order' && isset($services) && $services->count())

<div class="card bg-light border-0 mb-4">

<div class="card-body">

<div class="fw-semibold mb-2">
Sản phẩm trong đơn hàng
</div>

<ul class="list-group list-group-flush">

@foreach($services as $service)

<li class="list-group-item d-flex justify-content-between align-items-center px-0">

<div class="d-flex align-items-center gap-3">

<img
src="{{ !empty($service->image)
? asset('uploads/services/'.$service->image)
: asset('assets/images/banner.jpg') }}"
width="45"
height="45"
style="object-fit:cover;border-radius:8px"
>

<div>

<div>{{ $service->name }}</div>

<div class="small text-muted">
x {{ $service->quantity }}
</div>

</div>

</div>

<span class="text-muted">

{{ number_format(
$service->price * $service->quantity,
0,
',',
'.'
) }}đ

</span>

</li>

@endforeach

</ul>

</div>

</div>

@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        ❌ {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        ✅ {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if($type === 'order')
{{-- SHIPPING ADDRESS SECTION --}}
<div class="card bg-light border-0 mb-4">

<div class="card-body">

<div class="d-flex align-items-center gap-2 mb-3">

<i class="bi bi-geo-alt-fill text-primary fs-5"></i>

<h5 class="mb-0">Địa chỉ giao hàng</h5>

</div>

@if(isset($addresses) && $addresses->count() > 0)

<div class="row g-3">

@foreach($addresses as $address)

<div class="col-md-6">

<label class="w-100">

<input 
type="radio" 
name="address_id" 
value="{{ $address->id }}" 
class="d-none address-option"
@if($address->is_default || $loop->first) checked @endif
>

<div class="card address-card border-2 h-100">

<div class="card-body">

<div class="d-flex justify-content-between align-items-start">

<div class="flex-grow-1">

<div class="fw-semibold">
{{ $address->name ?? Auth::user()->name }}
@if($address->is_default)
<span class="badge bg-success ms-2">Mặc định</span>
@endif
</div>

<p class="mb-1 text-muted small">
<i class="bi bi-house"></i>
{{ $address->street_address }}
@if($address->ward), {{ $address->ward }}@endif
@if($address->district), {{ $address->district }}@endif
, {{ $address->city }}
@if($address->postal_code)- {{ $address->postal_code }}@endif
</p>

@if($address->phone)
<p class="mb-0 text-muted small">
<i class="bi bi-telephone"></i>
{{ $address->phone }}
</p>
@endif

</div>

</div>

</div>

</label>

</div>

@endforeach

</div>

<small class="d-block mt-3 text-muted">
<i class="bi bi-info-circle"></i> 
Chọn địa chỉ giao hàng cho đơn hàng của bạn
</small>

@else

<div class="alert alert-warning">

<i class="bi bi-exclamation-triangle"></i>
Bạn chưa có địa chỉ giao hàng. 

<a href="{{ route('user.profile') }}" class="alert-link">Thêm địa chỉ ngay</a>

</div>

@endif

</div>

</div>

@endif
{{-- PAYMENT FORM --}}
<form method="POST" action="{{ $submitRoute }}">
@csrf

<input type="hidden" id="selectedAddressId" name="selected_address_id" value="">

<div class="row g-4">


{{-- MOMO --}}
<div class="col-md-6">

<label class="w-100">

<input
type="radio"
name="payment_method"
value="momo"
class="d-none payment-option"
checked
>

<div class="card payment-card border-2 h-100 tien">

<div class="card-body">

<div class="d-flex align-items-center gap-3 mb-3">

<img
src="{{ asset('assets/momo.webp') }}"
width="40"
>

<div>

<h5 class="mb-1">
MoMo
</h5>

<div class="text-muted small">
Thanh toán online nhanh chóng
</div>

</div>

</div>

<div class="small text-muted">

✔ Xử lý ngay lập tức  
✔ Không cần tiền mặt  
✔ Bảo mật cao

</div>

</div>

</div>

</label>

</div>



{{-- CASH --}}
<div class="col-md-6">

<label class="w-100">

<input
type="radio"
name="payment_method"
value="cash"
class="d-none payment-option"
>

<div class="card payment-card border-2 h-100 gion">

<div class="card-body">

<div class="d-flex align-items-center gap-3 mb-3">

<span class="fs-2">💵</span>

<div>

<h5 class="mb-1">
Tiền mặt
</h5>

<div class="text-muted small">
Thanh toán trực tiếp tại sân
</div>

</div>

</div>

<div class="small text-muted">

✔ Không cần ví điện tử  
✔ Thanh toán linh hoạt

</div>

</div>

</div>

</label>

</div>


</div>



{{-- FOOTER --}}
<div class="d-flex justify-content-between align-items-center mt-4">

<a
href="{{ $type === 'booking'
? route('user.myBookings')
: route('user.myServices') }}"
class="btn btn-outline-secondary"
>

Quay lại

</a>


<button
type="submit"
class="btn btn-primary px-4"
>

Tiếp tục thanh toán →

</button>

</div>


</form>



</div>
</div>
</div>
</div>
</div>


<style>
.tien{
background: linear-gradient(90deg, #ecb8d9, #e57dcd);
}
.gion{
    background: linear-gradient(90deg, #a1c4fd, #c2e9fb);
}
.payment-card{
cursor:pointer;
transition:.2s;
}

.payment-card:hover{
transform:translateY(-4px);
box-shadow:0 10px 25px rgba(0,0,0,.12);
}

.payment-option:checked + .payment-card{
border-color:#0d6efd;
box-shadow:0 0 0 0.2rem rgba(13,110,253,.15);
}
.back{
background: white;
}
.ll{
    background: linear-gradient(90deg, #806e6e, #e0e0e0);
}

/* Address Card Styles */
.address-card {
    cursor: pointer;
    transition: all 0.2s ease;
    border-color: #ddd !important;
}

.address-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

.address-option:checked + .address-card {
    border-color: #0d6efd !important;
    background-color: #f0f7ff;
    box-shadow: 0 0 0 0.2rem rgba(13,110,253,.15);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle address selection
    const addressOptions = document.querySelectorAll('.address-option');
    const selectedAddressInput = document.getElementById('selectedAddressId');
    
    // Set initial value
    const checkedOption = document.querySelector('.address-option:checked');
    if (checkedOption) {
        selectedAddressInput.value = checkedOption.value;
    }
    
    // Update hidden input when address is selected
    addressOptions.forEach(option => {
        option.addEventListener('change', function() {
            if (this.checked) {
                selectedAddressInput.value = this.value;
            }
        });
    });
});
</script>

@endsection