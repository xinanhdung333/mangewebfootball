@extends('layouts.app')

@section('content')

<div class="container-fluid py-4">

<div class="row mb-4">
<div class="col-md-12">
<h1><i class="bi bi-chat-left-heart"></i> Feedback của bạn</h1>
</div>
</div>

@if($errors->any())

<div class="alert alert-danger alert-dismissible fade show">
<strong>Lỗi!</strong>
<ul class="mb-0">
@foreach($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
<button class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('success'))

<div class="alert alert-success alert-dismissible fade show">
<i class="bi bi-check-circle"></i>
{{ session('success') }}
<button class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<ul class="nav nav-tabs mb-4">

<li class="nav-item">
<button class="nav-link active"
data-bs-toggle="tab"
data-bs-target="#serviceTab"
onclick="location.hash='serviceTab'">

<i class="bi bi-bag-check"></i>
Dịch vụ đã mua

</button>
</li>

<li class="nav-item">
<button class="nav-link"
data-bs-toggle="tab"
data-bs-target="#bookingTab"
onclick="location.hash='bookingTab'">

<i class="bi bi-calendar-check"></i>
Booking sân

</button>
</li>

</ul>

<div class="tab-content">

<!-- ================= SERVICES ================= -->

<div class="tab-pane fade show active"
id="serviceTab">

@if($services->count())

<div class="history-list">

@foreach($services as $service)

<div class="card history-item mb-3 shadow-sm border-0">

<div class="card-body">

<div class="row align-items-center">

<div class="col-auto">

@if($service->service_image)

<img
src="{{ asset('uploads/services/'.$service->service_image) }}"
style="width:120px;height:120px;object-fit:cover;border-radius:8px;">

@else

<img
src="{{ asset('images/default.png') }}"
style="width:120px;height:120px;object-fit:cover;border-radius:8px;">

@endif

</div>

<div class="col">

<h5 class="fw-bold">
{{ $service->service_name }}
</h5>

<p class="mb-1">
<strong>Mã đơn:</strong>
#{{ $service->order_item_id }}
</p>

<p class="mb-1">

<strong>Tổng tiền:</strong>

<span class="text-success fw-bold">

{{ number_format($service->total,0,',','.') }}
VNĐ

</span>

</p>

<p class="mb-2">

<strong>Feedback:</strong>

{{ $service->feedback_message ?? 'Chưa có feedback' }}

</p>

@if($service->feedback_rating)

<div class="text-warning mb-2">

@for($i=0;$i<$service->feedback_rating;$i++)
★
@endfor

@for($i=$service->feedback_rating;$i<5;$i++)
☆
@endfor

</div>

@endif

@if(!$service->feedback_message)

<button
class="btn btn-sm btn-primary"
data-bs-toggle="modal"
data-bs-target="#feedbackModal"
data-type="service"
data-item-id="{{ $service->order_item_id }}">

Gửi feedback

</button>

@else

<span class="badge bg-success">

Đã gửi feedback

</span>

@endif

</div>

</div>

</div>

</div>

@endforeach

</div>

<div class="mt-3">

{{ $services->links('pagination::bootstrap-5') }}

</div>

@else

<div class="alert alert-info text-center py-5">

Bạn chưa mua dịch vụ nào

</div>

@endif

</div>

<!-- ================= BOOKINGS ================= -->

<div class="tab-pane fade"
id="bookingTab">

@if($bookings->count())

<div class="history-list">

@foreach($bookings as $booking)

<div class="card history-item mb-3 shadow-sm border-0">

<div class="card-body">

<div class="row align-items-center">

<div class="col-auto">

@if($booking->field_image)

<img
src="{{ asset('uploads/fields/'.$booking->field_image) }}"
style="width:120px;height:120px;object-fit:cover;border-radius:8px;">

@else

<img
src="{{ asset('images/default.png') }}"
style="width:120px;height:120px;object-fit:cover;border-radius:8px;">

@endif

</div>

<div class="col">

<h5 class="fw-bold">

{{ $booking->field_name }}

</h5>

<p class="mb-1">

<strong>Mã booking:</strong>

#{{ $booking->booking_id }}

</p>

<p class="mb-1">

<strong>Ngày:</strong>

{{ date('d/m/Y',
strtotime($booking->booking_date)) }}

</p>

<p class="mb-1">

<strong>Thời gian:</strong>

{{ $booking->start_time }}

*

{{ $booking->end_time }}

</p>

<p class="mb-2">

<strong>Feedback:</strong>

{{ $booking->feedback_message
?? 'Chưa có feedback' }}

</p>

@if($booking->feedback_rating)

<div class="text-warning mb-2">

@for($i=0;$i<$booking->feedback_rating;$i++)
★
@endfor

@for($i=$booking->feedback_rating;$i<5;$i++)
☆
@endfor

</div>

@endif

@if(!$booking->feedback_message)

<button
class="btn btn-sm btn-primary"
data-bs-toggle="modal"
data-bs-target="#feedbackModal"
data-type="booking"
data-item-id="{{ $booking->booking_id }}">

Gửi feedback

</button>

@else

<span class="badge bg-success">

Đã gửi feedback

</span>

@endif

</div>

</div>

</div>

</div>

@endforeach

</div>

<div class="mt-3">

{{ $bookings->links('pagination::bootstrap-5') }}

</div>

@else

<div class="alert alert-info text-center py-5">

Bạn chưa có booking nào

</div>

@endif

</div>

</div>

</div>

<!-- ================= MODAL ================= -->

<div class="modal fade"
id="feedbackModal">

<div class="modal-dialog">

<form method="POST"
action="{{ route('user.sendFeedback') }}">

@csrf

<input type="hidden"
name="feedback_type"
id="feedbackType">

<input type="hidden"
name="item_id"
id="feedbackItemId">

<div class="modal-content">

<div class="modal-header">

<h5 class="modal-title">

Gửi feedback

</h5>

<button class="btn-close"
data-bs-dismiss="modal">

</button>

</div>

<div class="modal-body">

<label class="mb-2">

Đánh giá sao

</label>

<select
name="rating"
class="form-control mb-3"
required>

<option value="5">★★★★★</option>
<option value="4">★★★★☆</option>
<option value="3">★★★☆☆</option>
<option value="2">★★☆☆☆</option>
<option value="1">★☆☆☆☆</option>

</select>

<label>

Nội dung feedback

</label>

<textarea
name="message"
class="form-control"
rows="4"
required>

</textarea>

</div>

<div class="modal-footer">

<button
class="btn btn-secondary"
data-bs-dismiss="modal">

Huỷ

</button>

<button
class="btn btn-primary">

Gửi feedback

</button>

</div>

</div>

</form>

</div>

</div>

<style>

.history-item{

transition:0.25s ease;

border-left:4px solid #3b82f6;

border-radius:12px;

}


.history-item:hover{

transform:translateY(-3px);

box-shadow:0 6px 20px rgba(0,0,0,0.08);

}

</style>

<script>

document.addEventListener(
"DOMContentLoaded",
function(){

const hash=
window.location.hash;

if(hash){

const trigger=
document.querySelector(
`button[data-bs-target="${hash}"]`
);

if(trigger){

new bootstrap.Tab(
trigger
).show();

}

}

});


const feedbackModal=
document.getElementById(
'feedbackModal'
);


feedbackModal.addEventListener(
'show.bs.modal',
function(event){

const button=
event.relatedTarget;


document.getElementById(
'feedbackType'
).value=
button.getAttribute(
'data-type'
);


document.getElementById(
'feedbackItemId'
).value=
button.getAttribute(
'data-item-id'
);

});

</script>

@endsection
