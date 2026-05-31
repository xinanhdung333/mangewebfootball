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
<div class="col-md-4">

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
<div class="col-md-4">

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

{{-- BANK TRANSFER --}}
<div class="col-md-4">

<label class="w-100">

<input
type="radio"
name="payment_method"
value="bank_transfer"
class="d-none payment-option"
@disabled(empty($bankTransfer['account_no'] ?? null))
>

<div class="card payment-card border-2 h-100 bank">

<div class="card-body">

<div class="d-flex align-items-center gap-3 mb-3">

<span class="fs-2"><i class="bi bi-qr-code-scan"></i></span>

<div>

<h5 class="mb-1">
Chuy&#7875;n kho&#7843;n MBBank
</h5>

<div class="text-muted small">
Qu&#233;t m&#227; VietQR b&#7857;ng app ng&#226;n h&#224;ng
</div>

</div>

</div>

<div class="small text-muted">

&#10004; T&#7921; &#273;i&#7873;n s&#7889; ti&#7873;n  
&#10004; C&#243; n&#7897;i dung &#273;&#7889;i so&#225;t  
&#10004; X&#225;c nh&#7853;n sau khi ki&#7875;m tra

</div>

@if(empty($bankTransfer['account_no'] ?? null))
<div class="alert alert-warning small mt-3 mb-0 py-2">
Ch&#432;a c&#7845;u h&#236;nh t&#224;i kho&#7843;n MBBank.
</div>
@endif

</div>

</div>

</label>

</div>


</div>

<div id="bankTransferDetails" class="card border-0 bg-light mt-4 d-none">
<div class="card-body">
<div class="row g-4 align-items-center">

<div class="col-md-4 text-center">
@if(!empty($bankTransfer['qr_url'] ?? null))
<img
src="{{ $bankTransfer['qr_url'] }}"
alt="MBBank VietQR"
class="img-fluid rounded shadow-sm bg-white p-2"
style="max-height:260px"
>
@else
<div class="alert alert-warning mb-0">
Ch&#432;a c&#243; QR v&#236; thi&#7871;u s&#7889; t&#224;i kho&#7843;n MBBank trong .env
</div>
@endif
</div>

<div class="col-md-8">
<h5 class="fw-semibold mb-3">Th&#244;ng tin chuy&#7875;n kho&#7843;n</h5>

<div class="row g-3">
<div class="col-sm-6">
<div class="small text-muted">Ng&#226;n h&#224;ng</div>
<div class="fw-semibold">{{ $bankTransfer['bank_name'] ?? 'MBBank' }}</div>
</div>

<div class="col-sm-6">
<div class="small text-muted">S&#7889; t&#224;i kho&#7843;n</div>
<div class="fw-semibold">{{ $bankTransfer['account_no'] ?? 'Chua cau hinh' }}</div>
</div>

<div class="col-sm-6">
<div class="small text-muted">Ch&#7911; t&#224;i kho&#7843;n</div>
<div class="fw-semibold">{{ $bankTransfer['account_name'] ?? 'Chua cau hinh' }}</div>
</div>

<div class="col-sm-6">
<div class="small text-muted">S&#7889; ti&#7873;n</div>
<div class="fw-semibold text-primary">{{ number_format($amount,0,',','.') }}&#273;</div>
</div>

<div class="col-12">
<div class="small text-muted">N&#7897;i dung chuy&#7875;n kho&#7843;n</div>
<div class="fw-bold text-danger">{{ $bankTransfer['transfer_code'] ?? '' }}</div>
</div>
</div>

<div class="alert alert-info small mt-3 mb-0">
Sau khi chuy&#7875;n kho&#7843;n, b&#7845;m "Ti&#7871;p t&#7909;c thanh to&#225;n" &#273;&#7875; h&#7879; th&#7889;ng ghi nh&#7853;n v&#224; ch&#7901; &#273;&#7889;i so&#225;t.
</div>
</div>

</div>
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
.bank{
    background: linear-gradient(90deg, #d7f9f1, #bce7ff);
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
.payment-option:disabled + .payment-card{
opacity:.6;
cursor:not-allowed;
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

    const paymentOptions = document.querySelectorAll('.payment-option');
    const bankTransferDetails = document.getElementById('bankTransferDetails');

    function toggleBankTransferDetails() {
        const checkedPayment = document.querySelector('.payment-option:checked');
        if (!bankTransferDetails || !checkedPayment) {
            return;
        }

        bankTransferDetails.classList.toggle('d-none', checkedPayment.value !== 'bank_transfer');
    }

    paymentOptions.forEach(option => {
        option.addEventListener('change', toggleBankTransferDetails);
    });

    toggleBankTransferDetails();
});
</script>

@endsection
