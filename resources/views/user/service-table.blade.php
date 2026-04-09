<div class="container-fluid">
<div class="row">

<!-- LEFT -->
<div class="col-md-8">

@if(isset($myServices) && $myServices->count())

<div class="history-list">

@foreach($myServices as $h)

<div class="card history-item mb-3">
<div class="card-body">

<div class="row align-items-center">

<div class="col-auto">

<img
src="{{ !empty($h->image)
? asset('uploads/services/' . $h->image)
: asset('images/default.png') }}"
style="width:120px;height:120px;object-fit:cover;border-radius:8px;">

</div>


<div class="col">

<h5>{{ $h->service->name ?? 'Không có tên dịch vụ' }}</h5>

<strong>Trạng thái:</strong>

@if($h->order->status === 'pending')
<span class="badge bg-warning text-dark">Chờ xử lý</span>

@elseif($h->order->status === 'paid')
<span class="badge bg-success">Đã thanh toán</span>

@elseif($h->order->status === 'cancelled')
<span class="badge bg-danger">Đã huỷ</span>
@endif


<p class="mb-1">
<strong>Số lượng:</strong>
{{ $h->quantity }}
</p>


<p class="mb-1">
<strong>Tổng đơn:</strong>

<span class="text-success">
{{ number_format($h->total_amount,0,',','.') }} VNĐ
</span>

</p>


<p>
<strong>Ngày mua:</strong>
{{ $h->created_at->format('d/m/Y H:i') }}
</p>


<a href="{{ route('user.orderDetail',$h->order_id) }}"
class="btn btn-sm btn-info">

👁 Xem chi tiết

</a>

</div>

</div>
</div>
</div>

@endforeach

</div>

@else

<div class="alert alert-info">Không có dịch vụ nào để hiển thị.</div>

@endif

</div>


<!-- RIGHT -->
<div class="col-md-4" id="ll">

<div class="card shadow-sm mb-3">
<div class="card-body">

<h5>📊 Thống kê</h5>

<p>Tổng dịch vụ: {{ isset($myServices) ? $myServices->total() : 0 }}</p>

<p>Chờ xử lý:
{{ isset($myServices) ? $myServices->filter(fn($i)=>$i->order->status=='pending')->count() : 0 }}
</p>

<p>Đã thanh toán:
{{ isset($myServices) ? $myServices->filter(fn($i)=>$i->order->status=='paid')->count() : 0 }}
</p>

</div>
</div>


<div class="card shadow-sm">
<div class="card-body text-center">

<h5>🎁 Khuyến mãi</h5>

<p>Giảm 20% dịch vụ hôm nay</p>

<a href="{{ route('user.services') }}"
class="btn btn-primary btn-sm">

Xem dịch vụ

</a>

</div>
</div>

</div>

</div>
</div>


<div class="mt-3">
@if(isset($myServices))
{{ $myServices->links('pagination::bootstrap-5') }}
@endif
</div>