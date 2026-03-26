
@extends('layouts.app')
@section('content')

<h3>Thanh toán MOMO (Demo)</h3>

<form id="momoForm" action="{{ route('user.momo.pay') }}" method="POST">
    @csrf
    <input type="hidden" name="order_id" value="{{ $order->id }}">
</form>

<script>
    document.getElementById('momoForm').submit();
</script>
<p>Đang chuyển sang MoMo...</p>
@endsection