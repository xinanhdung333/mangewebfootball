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




{{-- PAYMENT FORM --}}
<form method="POST" action="{{ $submitRoute }}">
@csrf


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
</style>

@endsection