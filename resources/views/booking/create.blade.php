@extends('layouts.app')

@section('content')
<h1>Đặt sân</h1>
@if($field)
    <h4>{{ $field->name }}</h4>
@endif
<p>Form đặt sân (mô phỏng). Bạn có thể triển khai lưu booking vào DB.</p>
@endsection
