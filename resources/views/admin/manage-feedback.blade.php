@extends('layouts.app')

@section('content')
<h1><i class="bi bi-chat-dots"></i> Feedback khách hàng</h1>

<ul class="nav nav-tabs" id="feedbackTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="service-tab" data-bs-toggle="tab" data-bs-target="#serviceTab">Dịch vụ</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="booking-tab" data-bs-toggle="tab" data-bs-target="#bookingTab">Booking sân</button>
    </li>
</ul>

<div class="tab-content mt-3">
    <!-- ================= TAB DỊCH VỤ ================= -->
    <div class="tab-pane fade show active" id="serviceTab">
        <div class="table-responsive">
            @if($serviceFeedbacks->isEmpty())
                <div class="alert alert-info mt-3">Không có feedback dịch vụ.</div>
            @else
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Người dùng</th>
                            <th>Tên dịch vụ</th>
                            <th>Ảnh</th>
                            <th>Ngày Feedback</th>
                            <th>Feedback</th>
                            <th>Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($serviceFeedbacks as $service)
                            <tr>
                                <td>{{ $service->service_id }}</td>
                                <td>{{ htmlspecialchars($service->user_name) }}</td>
                                <td>{{ htmlspecialchars($service->service_name) }}</td>
                                <td>
                                    @if($service->service_image)
                                        <img src="{{ asset('uploads/services/' . $service->service_image) }}" 
                                             style="width:60px;height:60px;object-fit:cover;">
                                    @endif
                                </td>
                                <td>{{ date('d/m/Y H:i:s', strtotime($service->created_at)) }}</td>
                                <td>{{ htmlspecialchars($service->feedback_message) }}</td>
                                <td>
                                    @if($service->feedback_rating)
                                        <span style="color:gold;">
                                            {{ str_repeat('★', $service->feedback_rating) }}{{ str_repeat('☆', 5 - $service->feedback_rating) }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <!-- ================= TAB BOOKING ================= -->
    <div class="tab-pane fade" id="bookingTab">
        <div class="table-responsive">
            @if($bookingFeedbacks->isEmpty())
                <div class="alert alert-info mt-3">Không có feedback booking sân.</div>
            @else
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Người dùng</th>
                            <th>Tên sân</th>
                            <th>Ảnh</th>
                            <th>Ngày Feedback</th>
                            <th>Feedback</th>
                            <th>Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookingFeedbacks as $booking)
                            <tr>
                                <td>{{ $booking->booking_id }}</td>
                                <td>{{ htmlspecialchars($booking->user_name) }}</td>
                                <td>{{ htmlspecialchars($booking->field_name) }}</td>
                                <td>
                                    @if($booking->field_image)
                                        <img src="{{ asset('uploads/fields/' . $booking->field_image) }}" 
                                            style="width:60px;height:60px;object-fit:cover;">
                                    @endif
                                </td>
                                <td>{{ date('d/m/Y H:i:s', strtotime($booking->created_at)) }}</td>
                                <td>{{ htmlspecialchars($booking->feedback_message) }}</td>
                                <td>
                                    @if($booking->feedback_rating)
                                        <span style="color:gold;">
                                            {{ str_repeat('★', $booking->feedback_rating) }}{{ str_repeat('☆', 5 - $booking->feedback_rating) }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

@endsection