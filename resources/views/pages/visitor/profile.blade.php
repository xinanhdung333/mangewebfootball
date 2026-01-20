@extends('layouts.app')

@section('content')
<div class="py-4">
    <h1>Thông tin cá nhân</h1>
    @if($user)
        <ul class="list-group">
            <li class="list-group-item"><strong>Họ tên:</strong> {{ $user->name }}</li>
            <li class="list-group-item"><strong>Email:</strong> {{ $user->email }}</li>
            <li class="list-group-item"><strong>Vai trò:</strong> {{ $user->role }}</li>
        </ul>
    @else
        <p>Vui lòng đăng nhập để xem thông tin.</p>
    @endif
</div>
@endsection
