<div class="container-fluid">
<div class="row">

<!-- LEFT -->
<div class="col-md-8">


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