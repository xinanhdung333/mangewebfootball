@extends('layouts.app')

@section('content')

<div class="container-fluid py-4">

    <div class="row mb-4">
        <div class="col-md-12">

            <a href="{{ route('user.cart') }}"
               class="btn btn-secondary mb-3">

                <i class="bi bi-arrow-left"></i>
                Quay lại
            </a>

            <h1>
                Chi tiết đơn hàng #{{ $order->id }}
            </h1>

        </div>
    </div>

    <div class="row">

        <!-- LEFT -->
        <div class="col-md-8">

            <!-- THÔNG TIN ĐƠN -->
            <div class="card mb-4">

                <div class="card-body">

                    <h5 class="card-title">
                        Thông tin đơn hàng
                    </h5>

                    <div class="table-responsive">

                        <table class="table table-borderless">

                            <tr>
                                <td class="fw-bold">
                                    Ngày đặt:
                                </td>

                                <td>
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </td>
                            </tr>

                            <tr>
                                <td class="fw-bold">
                                    Tổng tiền:
                                </td>

                                <td>
                                    <span class="text-success fw-bold fs-5">
                                        {{ number_format($order->total_amount, 0, ',', '.') }} VNĐ
                                    </span>
                                </td>
                            </tr>

                            <tr>

                                <td class="fw-bold">
                                    Trạng thái:
                                </td>

                                <td>

                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'confirmed' => 'success',
                                            'processing' => 'info',
                                            'completed' => 'success',
                                            'cancelled' => 'danger'
                                        ];

                                        $statusTexts = [
                                            'pending' => 'Chờ xử lý',
                                            'confirmed' => 'Đã xác nhận',
                                            'processing' => 'Đang xử lý',
                                            'completed' => 'Hoàn tất',
                                            'cancelled' => 'Đã hủy'
                                        ];
                                    @endphp

                                    <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">

                                        {{ $statusTexts[$order->status] ?? 'Không xác định' }}

                                    </span>

                                </td>

                            </tr>

                        </table>

                    </div>

                </div>

            </div>

            <!-- DANH SÁCH DỊCH VỤ -->
            <div class="card">

                <div class="card-body">

                    <h5 class="card-title mb-4">
                        Dịch vụ đã mua
                    </h5>

                    @if($orderItems && count($orderItems) > 0)

                        <div class="row g-3">

                            @foreach($orderItems as $item)

                                <div class="col-12">

                                    <div class="card border-light">

                                        <div class="card-body">

                                            <div class="row align-items-center">

                                                <div class="col-auto">

                                                    <img
                                                        src="{{ !empty($item->service->image)
                                                            ? asset('uploads/services/' . $item->service->image)
                                                            : asset('images/default.png') }}"

                                                        alt="{{ $item->service->name }}"

                                                        style="
                                                            width:100px;
                                                            height:100px;
                                                            object-fit:cover;
                                                            border-radius:8px;
                                                        ">

                                                </div>

                                                <div class="col">

                                                    <h6 class="card-title mb-2">
                                                        {{ $item->service->name }}
                                                    </h6>

                                                    <p class="card-text mb-1">
                                                        <strong>Số lượng:</strong>
                                                        {{ $item['quantity'] }}
                                                    </p>

                                                    <p class="card-text mb-1">
                                                        <strong>Đơn giá:</strong>
                                                        {{ number_format($item['price'], 0, ',', '.') }} VNĐ
                                                    </p>

                                                    <p class="card-text mb-0">

                                                        <strong>Thành tiền:</strong>

                                                        <span class="text-success fw-bold">

                                                            {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }} VNĐ

                                                        </span>

                                                    </p>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    @else

                        <div class="alert alert-info text-center">

                            <i class="bi bi-info-circle"></i>

                            Không có dịch vụ trong đơn hàng này.

                        </div>

                    @endif

                </div>

            </div>

        </div>

        <!-- RIGHT -->
        <div class="col-md-4">

            <div class="card sticky-top"
                 style="top:20px;">

                <div class="card-body">

                    <h5 class="card-title mb-3">
                        Tóm tắt đơn hàng
                    </h5>

                    <div class="table-responsive">

                        <table class="table table-borderless">

                            <tr>

                                <td>
                                    Tổng sản phẩm:
                                </td>

                                <td class="text-end fw-bold">

                                    {{ $orderItems ? count($orderItems) : 0 }}
                                    mục

                                </td>

                            </tr>

                            <tr>

                                <td>
                                    Tổng tiền:
                                </td>

                                <td class="text-end fw-bold text-primary">

                                    {{ number_format($order->total_amount, 0, ',', '.') }}
                                    VNĐ

                                </td>

                            </tr>

                        </table>

                    </div>

                    <hr>

                    @if($order->status === 'pending')
                        <a href="{{ route('user.payment.order', $order->id) }}"
                           class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-credit-card"></i>
                            Thanh toán ngay
                        </a>
                    @endif

                    @if($order->status === 'confirmed' || $order->status === 'completed')

                        <a href="{{ route('user.exportInvoice', $order->id) }}"
                           target="_blank"
                           class="btn btn-danger w-100 mb-2">

                            <i class="bi bi-file-pdf"></i>
                            Xuất hóa đơn

                        </a>

                    @endif

                    <a href="{{ route('user.order.tracking', $order->id) }}"
                       class="btn btn-success w-100">
                        <i class="bi bi-truck"></i>
                        Theo dõi vận chuyển
                    </a>

                </div>

            </div>

            <!-- HỖ TRỢ -->
            <div class="card mt-3">

                <div class="card-body">

                    <h6 class="card-title mb-3">
                        Hỗ trợ
                    </h6>

                    <p class="small text-muted mb-2">

                        <i class="bi bi-telephone"></i>

                        Liên hệ:
                        <strong>0123 456 789</strong>

                    </p>

                    <p class="small text-muted mb-2">

                        <i class="bi bi-envelope"></i>

                        Email:
                        <strong>support@footballbooking.com</strong>

                    </p>

                    <p class="small text-muted mb-3">

                        <i class="bi bi-geo-alt"></i>

                        Địa chỉ giao hàng:

                        <strong>

                            @if($order->userAddress)

                                {{ $order->userAddress->street_address }}

                                @if($order->userAddress->ward)
                                    , {{ $order->userAddress->ward }}
                                @endif

                                @if($order->userAddress->district)
                                    , {{ $order->userAddress->district }}
                                @endif

                                , {{ $order->userAddress->city }}

                            @else

                                Chưa có địa chỉ

                            @endif

                        </strong>

                    </p>

                    <!-- BUTTON -->
                    <button type="button"
                            class="btn btn-outline-primary w-100"
                            data-bs-toggle="modal"
                            data-bs-target="#editAddressModal">

                        <i class="bi bi-pencil"></i>
                        Sửa địa chỉ giao hàng

                    </button>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- MODAL -->
<div class="modal fade"
     id="editAddressModal"
     tabindex="-1">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Đổi địa chỉ giao hàng
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <form action="{{ route('user.address.order.edit', $order->id) }}"
                  method="POST">

                @csrf

                <div class="modal-body">

                    <label class="form-label">
                        Chọn địa chỉ
                    </label>

                    <select name="selected_address_id"
                            class="form-select"
                            required>

                        @foreach($addresses as $address)

                            <option value="{{ $address->id }}"
                                {{ $order->user_address_id == $address->id ? 'selected' : '' }}>

                                {{ $address->name ?? Auth::user()->name }}
                                -
                                {{ $address->phone }}
                                -
                                {{ $address->street_address }}

                                @if($address->ward)
                                    , {{ $address->ward }}
                                @endif

                                @if($address->district)
                                    , {{ $address->district }}
                                @endif

                                , {{ $address->city }}

                            </option>

                        @endforeach

                    </select>

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">

                        Đóng

                    </button>

                    <button type="submit"
                            class="btn btn-primary">

                        Cập nhật

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection