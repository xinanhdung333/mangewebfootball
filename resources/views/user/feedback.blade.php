@extends('layouts.app')
@section('content')

<div class="container-fluid py-4">

```
<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-chat-left-heart"></i> Feedback của bạn</h1>
    </div>
</div>


{{-- ALERTS --}}
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <strong>Lỗi!</strong>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif


@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif



{{-- NAV TABS --}}
<ul class="nav nav-tabs mb-4">

    <li class="nav-item">
        <button class="nav-link active"
                data-bs-toggle="tab"
                data-bs-target="#serviceTab"
                onclick="location.hash='serviceTab'">
            <i class="bi bi-bag-check"></i>
            Dịch vụ đã mua
        </button>
    </li>

    <li class="nav-item">
        <button class="nav-link"
                data-bs-toggle="tab"
                data-bs-target="#bookingTab"
                onclick="location.hash='bookingTab'">
            <i class="bi bi-calendar-check"></i>
            Booking sân
        </button>
    </li>

</ul>



<div class="tab-content">

    {{-- ================= SERVICES TAB ================= --}}
    <div class="tab-pane fade show active"
         id="serviceTab">


        @if($services->count())

            <div class="table-responsive">

                <table class="table table-hover">

                    <thead class="table-light">
                    <tr>
                        <th>#ID</th>
                        <th>Dịch vụ</th>
                        <th>Ảnh</th>
                        <th>Tổng tiền</th>
                        <th>Feedback</th>
                        <th>Rating</th>
                        <th>Thao tác</th>
                    </tr>
                    </thead>


                    <tbody>

                    @foreach($services as $service)

                        <tr>

                            <td>
                                <strong>#{{ $service->order_item_id }}</strong>
                            </td>

                            <td>
                                {{ $service->service_name }}
                            </td>


                            <td>

                                @if($service->service_image)

                                    <img
                                        src="{{ asset('uploads/services/'.$service->service_image) }}"
                                        width="60"
                                        height="60"
                                        class="rounded"
                                        style="object-fit:cover">

                                @else

                                    <span class="text-muted">
                                        Không có ảnh
                                    </span>

                                @endif

                            </td>


                            <td>

                                <span class="fw-bold text-success">

                                    {{ number_format($service->total,0,',','.') }}
                                    VNĐ

                                </span>

                            </td>


                            <td>

                                {{ $service->feedback_message ?? 'Chưa có feedback' }}

                            </td>


                            <td>

                                @if($service->feedback_rating)

                                    <span style="color:#ffc107">

                                        @for($i=0;$i<$service->feedback_rating;$i++)
                                            ★
                                        @endfor

                                        @for($i=$service->feedback_rating;$i<5;$i++)
                                            ☆
                                        @endfor

                                    </span>

                                @endif

                            </td>


                            <td>

                                @if(!$service->feedback_message)

                                    <button
                                        class="btn btn-sm btn-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#feedbackModal"
                                        data-type="service"
                                        data-item-id="{{ $service->order_item_id }}">

                                        <i class="bi bi-pencil"></i>
                                        Gửi

                                    </button>

                                @else

                                    <span class="badge bg-success">
                                        Đã gửi
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @endforeach

                    </tbody>

                </table>

            </div>


            <div class="mt-3">
                {{ $services->links('pagination::bootstrap-5') }}
            </div>


        @else

            <div class="alert alert-info text-center py-5">
                Bạn chưa mua dịch vụ nào
            </div>

        @endif


    </div>



    {{-- ================= BOOKINGS TAB ================= --}}
    <div class="tab-pane fade"
         id="bookingTab">


        @if($bookings->count())

            <div class="table-responsive">

                <table class="table table-hover">

                    <thead class="table-light">

                    <tr>

                        <th>#ID</th>
                        <th>Sân</th>
                        <th>Ảnh</th>
                        <th>Ngày đặt</th>
                        <th>Thời gian</th>
                        <th>Feedback</th>
                        <th>Rating</th>
                        <th>Thao tác</th>

                    </tr>

                    </thead>


                    <tbody>

                    @foreach($bookings as $booking)

                        <tr>

                            <td>
                                <strong>
                                    #{{ $booking->booking_id }}
                                </strong>
                            </td>


                            <td>
                                {{ $booking->field_name }}
                            </td>


                            <td>

                                @if($booking->field_image)

                                    <img
                                        src="{{ asset('uploads/fields/'.$booking->field_image) }}"
                                        width="60"
                                        height="60"
                                        class="rounded"
                                        style="object-fit:cover">

                                @else

                                    Không có ảnh

                                @endif

                            </td>


                            <td>

                                {{ date('d/m/Y',
                                strtotime($booking->booking_date)) }}

                            </td>


                            <td>

                                {{ $booking->start_time }}
                                -
                                {{ $booking->end_time }}

                            </td>


                            <td>

                                {{ $booking->feedback_message
                                ?? 'Chưa có feedback' }}

                            </td>


                            <td>

                                @if($booking->feedback_rating)

                                    <span style="color:#ffc107">

                                        @for($i=0;$i<$booking->feedback_rating;$i++)
                                            ★
                                        @endfor

                                        @for($i=$booking->feedback_rating;$i<5;$i++)
                                            ☆
                                        @endfor

                                    </span>

                                @endif

                            </td>


                            <td>

                                @if(!$booking->feedback_message)

                                    <button
                                        class="btn btn-sm btn-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#feedbackModal"
                                        data-type="booking"
                                        data-item-id="{{ $booking->booking_id }}">

                                        <i class="bi bi-pencil"></i>
                                        Gửi

                                    </button>

                                @else

                                    <span class="badge bg-success">
                                        Đã gửi
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @endforeach

                    </tbody>

                </table>

            </div>


            <div class="mt-3">
                {{ $bookings->links('pagination::bootstrap-5') }}
            </div>


        @else

            <div class="alert alert-info text-center py-5">
                Bạn chưa có booking nào
            </div>

        @endif


    </div>

</div>
```

</div>

<script>

document.addEventListener("DOMContentLoaded",function(){

    const hash=window.location.hash;

    if(hash){

        const trigger=document.querySelector(
            `button[data-bs-target="${hash}"]`
        );

        if(trigger){

            new bootstrap.Tab(trigger).show();

        }

    }

});

</script>

@endsection
