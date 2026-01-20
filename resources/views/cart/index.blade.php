@extends('layouts.app')

@section('content')
<h1>Giỏ hàng</h1>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(count($cart) === 0)
    <div class="alert alert-info">Giỏ hàng trống.</div>
@else
    <table class="table">
        <thead><tr><th>Sản phẩm</th><th>SL</th><th>Giá</th><th>Tổng</th><th></th></tr></thead>
        <tbody>
        @php $total = 0; @endphp
        @foreach($cart as $item)
            @php $subtotal = $item['price'] * $item['qty']; $total += $subtotal; @endphp
            <tr>
                <td>{{ $item['name'] }}</td>
                <td>
                    <form method="POST" action="{{ route('cart.updateQuantity') }}">
                        @csrf
                        <input type="hidden" name="id" value="{{ $item['id'] }}">
                        <input type="number" name="qty" value="{{ $item['qty'] }}" min="1" style="width:80px; display:inline-block;" class="form-control d-inline-block">
                        <button class="btn btn-sm btn-secondary" type="submit">Cập nhật</button>
                    </form>
                </td>
                <td>{{ number_format($item['price'],0,',','.') }}</td>
                <td>{{ number_format($subtotal,0,',','.') }}</td>
                <td>
                    <form method="POST" action="{{ route('cart.remove') }}">
                        @csrf
                        <input type="hidden" name="id" value="{{ $item['id'] }}">
                        <button class="btn btn-sm btn-danger">Xóa</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="text-end fw-bold">Tổng: {{ number_format($total,0,',','.') }}</div>

    <form method="POST" action="{{ route('checkout') }}">
        @csrf
        <button class="btn btn-success mt-3">Thanh toán</button>
    </form>
@endif

@endsection
