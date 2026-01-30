@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h1><i class="bi bi-person-circle"></i> Hồ sơ quản lý</h1>
            <p class="text-muted">Quản lý thông tin cá nhân của bạn</p>
        </div>
    </div>

    <div class="row">
        <!-- CỘT TRÁI -->
        <div class="col-md-4">

            {{-- Thông tin cá nhân --}}
            <div class="card text-center mb-3">
                <div class="card-body">
                    <img
                        src="{{ $boss->avatar
                            ? asset('storage/avatars/'.$boss->avatar)
                            : asset('images/default.png') }}"
                        class="rounded-circle mb-2"
                        style="width:120px;height:120px;object-fit:cover;"
                    >

                    <h5>{{ $boss->name }}</h5>
                    <p>{{ $boss->email }}</p>
                    <p><strong>Vai trò:</strong> {{ $boss->role === 'admin' ? 'Quản lý' : 'Người dùng' }}</p>
                    <p><strong>Ngày tạo:</strong> {{ $boss->created_at->format('d/m/Y') }}</p>
                </div>
            </div>

            {{-- Lịch sử giao dịch --}}
            <div class="card" style="max-height:500px; overflow-y:auto;">
                <div class="card-body">
                    <h5 class="mb-3">Lịch sử giao dịch</h5>

                    {{-- Đặt sân --}}
                    <h6 class="text-primary">
                        <i class="bi bi-calendar-check"></i> Đặt sân
                    </h6>

                    @forelse($bookingHistory as $b)
                        <div class="mb-2">
                            <strong>{{ $b->field_name }}</strong><br>
                            <small>
                                {{ \Carbon\Carbon::parse($b->booking_date)->format('d/m/Y') }}
                                | {{ $b->start_time }} - {{ $b->end_time }}
                            </small><br>
                            <span class="fw-bold text-success">
                                {{ number_format($b->total_price) }} đ
                            </span>
                        </div>
                    @empty
                        <p class="text-muted">Không có lịch sử đặt sân</p>
                    @endforelse

                    <hr>

                    {{-- Mua dịch vụ --}}
                    <h6 class="text-primary">
                        <i class="bi bi-bag-check"></i> Mua dịch vụ
                    </h6>

                    @forelse($serviceHistory as $s)
                        <div class="mb-2">
                            <strong>{{ $s->service_name }}</strong><br>
                            <small>{{ $s->created_at->format('d/m/Y H:i') }}</small><br>
                            <span class="fw-bold text-success">
                                {{ number_format($s->total) }} đ
                            </span>
                        </div>
                    @empty
                        <p class="text-muted">Không có lịch sử dịch vụ</p>
                    @endforelse

                </div>
            </div>
        </div>

        <!-- CỘT PHẢI -->
        <div class="col-md-8">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <h5>Cập nhật thông tin</h5>

                    <form method="POST"
                          action="{{ route('boss.profile.update') }}"
                          enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label>Họ tên</label>
                            <input type="text" name="name"
                                   class="form-control"
                                   value="{{ old('name', $boss->name) }}"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label>Số điện thoại</label>
                            <input type="text" name="phone"
                                   class="form-control"
                                   value="{{ old('phone', $boss->phone) }}"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label>Upload avatar</label>
                            <input type="file" name="avatar" class="form-control">
                        </div>

                        <hr>
                        <h6>Đổi mật khẩu (tuỳ chọn)</h6>

                        <div class="mb-3">
                            <label>Mật khẩu hiện tại</label>
                            <input type="password" name="current_password" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label>Mật khẩu mới</label>
                            <input type="password" name="password" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label>Xác nhận mật khẩu</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>

                        <button class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Cập nhật
                        </button>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
