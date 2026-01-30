@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1><i class="bi bi-person"></i> Hồ sơ cá nhân</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card text-center mb-3">
            <div class="card-body">
                <img src="{{ !empty(Auth::user()->avatar) ? asset('uploads/avatars/'.Auth::user()->avatar) : asset('assets/images/default.png') }}" 
                     alt="Avatar" class="rounded-circle mb-2" style="width:120px;height:120px;object-fit:cover;">
                <h5>{{ Auth::user()->name }}</h5>
                <p>{{ Auth::user()->email }}</p>
                <p><strong>Vai trò:</strong> {{ Auth::user()->role=='admin'?'Quản lý':'Người dùng' }}</p>
                <p><strong>Ngày tạo:</strong> {{ formatDateTime(Auth::user()->created_at) }}</p>
            </div>
        </div>

        <div class="card" style="max-height:500px; overflow-y:auto;">
            <div class="card-body">
                <h5 class="mb-3">Lịch sử giao dịch</h5>

                <h6 class="text-primary"><i class="bi bi-calendar-check"></i> Đặt sân</h6>
                @if($bookingHistory && count($bookingHistory) > 0)
                    <ul class="list-group list-group-flush mb-3">
                        @foreach($bookingHistory as $b)
                            <li class="list-group-item">
                                <strong>{{ $b->field_name ?? 'N/A' }}</strong><br>
                                <small>
                                    {{ date('d/m/Y', strtotime($b->booking_date)) }} |
                                    {{ $b->start_time }} - {{ $b->end_time }}
                                </small><br>
                                <span class="fw-bold text-success">{{ formatCurrency($b->total_price) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">Không có lịch sử đặt sân.</p>
                @endif

                <h6 class="text-primary"><i class="bi bi-bag-check"></i> Mua dịch vụ</h6>
                @if($serviceHistory && count($serviceHistory) > 0)
                    <ul class="list-group list-group-flush">
                        @foreach($serviceHistory as $s)
                            <li class="list-group-item">
                                <strong>{{ $s->service_name ?? 'N/A' }}</strong><br>
                                <small>{{ date('d/m/Y H:i', strtotime($s->created_at)) }}</small><br>
                                <span class="fw-bold text-success">{{ formatCurrency($s->total) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">Không có lịch sử dịch vụ.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-body">
                <h5>Cập nhật thông tin</h5>

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-3">
                        <label>Họ tên</label>
                        <input type="text" name="name" class="form-control" 
                               value="{{ old('name', Auth::user()->name) }}" required>
                    </div>

                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" 
                               value="{{ old('email', Auth::user()->email) }}" required>
                    </div>

                    <div class="mb-3">
                        <label>Số điện thoại</label>
                        <input type="text" name="phone" class="form-control" 
                               value="{{ old('phone', Auth::user()->phone) }}">
                    </div>

                    <div class="mb-3">
                        <label>Upload avatar</label>
                        <input type="file" name="avatar" class="form-control" accept="image/*">
                    </div>

                    <hr>
                    <h6>Đổi mật khẩu (tùy chọn)</h6>

                    <div class="mb-3">
                        <label>Mật khẩu hiện tại</label>
                        <input type="password" name="current_password" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Mật khẩu mới</label>
                        <input type="password" name="new_password" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Xác nhận mật khẩu mới</label>
                        <input type="password" name="new_password_confirmation" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Cập nhật
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
