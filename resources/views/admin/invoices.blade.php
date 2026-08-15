@extends('layouts.app')

@section('content')

<div class="row mb-4">
<div class="col-md-12">
<h1>
<i class="bi bi-receipt"></i>
Danh sách hóa đơn đã xuất
</h1>
</div>
</div>

<div class="row mb-4">
<div class="col-md-12">
<a href="{{ route('admin.export.invoice') }}"
class="btn btn-success">

<i class="bi bi-file-earmark-pdf"></i>
Xuất báo cáo

</a>
</div>
</div>

<div class="row">
<div class="col-md-12">

<div class="card">

<div class="card-header">
<h5 class="mb-0">Danh sách hóa đơn</h5>
</div>

<div class="card-body">

@if ($invoices->count() > 0)

@include('partials.admin-table-search', ['tableId' => 'admin-invoices-table', 'placeholder' => 'Tìm mã hóa đơn, khách hàng, dịch vụ...'])

<div class="table-responsive">

<table id="admin-invoices-table" class="table table-striped table-hover">

<thead>
<tr>
<th>Mã hóa đơn</th>
<th>User ID</th>
<th>Tên khách</th>
<th>Ảnh</th>
<th>Loại</th>
<th>Sân / Dịch vụ</th>
<th>Số tiền</th>
<th>Ngày xuất</th>
<th>Hành động</th>
</tr>
</thead>

<tbody>

@foreach ($invoices as $invoice)

<tr>

<td>{{ $invoice->invoice_code }}</td>

<td>
{{ $invoice->booking?->user?->id
?? $invoice->order?->user?->id
?? '-' }}
</td>

<td>
{{ $invoice->booking?->user?->name
?? $invoice->order?->user?->name
?? '-' }}
</td>

<td>

@if($invoice->booking?->user?->image)

<img
src="{{ asset('storage/'.$invoice->booking->user->image) }}"
width="50"
class="rounded"
/>

@elseif($invoice->order?->user?->image)

<img
src="{{ asset('storage/'.$invoice->order->user->image) }}"
width="50"
class="rounded"
/>

@else

-

@endif

</td>

<td>

@if($invoice->booking_id)

Booking sân ⚽

@elseif($invoice->order_id)

Dịch vụ 🧾

@else

-

@endif

</td>

<td>

@if($invoice->booking_id)

{{ $invoice->booking?->field?->name ?? '-' }}

@elseif($invoice->order_id)

@foreach($invoice->order?->items ?? [] as $item)

{{ $item->service?->name }}<br>

@endforeach

@endif

</td>

<td>

{{ number_format($invoice->total_amount,0,',','.') }}đ

</td>

<td>

{{ $invoice->issued_at }}

</td>

<td>

<a
href="{{ route('admin.export.invoice', [
'type' => $invoice->booking_id ? 'booking' : 'service',
'id' => $invoice->booking_id ?? $invoice->order_id
]) }}"
class="btn btn-sm btn-success">

Xuất PDF

</a>

</td>

</tr>

@endforeach

</tbody>
</table>

</div>

@else

<p class="text-muted">
Chưa có hóa đơn nào được xuất.
</p>

@endif

</div>
</div>
</div>
</div>
</br>
</br>
</br>
</br>
</br>
@endsection
