@extends('layouts.visitor')

@section('content')
@php
    $serviceFeedbacks = $serviceFeedbacks ?? [];
    $bookingFeedbacks = $bookingFeedbacks ?? [];
@endphp

<h1>Feedback khách hàng</h1>

<ul class="nav nav-tabs" id="feedbackTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#serviceTab">
            Dịch vụ
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#bookingTab">
            Booking sân
        </button>
    </li>
</ul>

<div class="tab-content mt-3">

    {{-- ================= FEEDBACK DỊCH VỤ ================= --}}
    <div class="tab-pane fade show active" id="serviceTab">
        <div class="table-responsive">

            @if(empty($serviceFeedbacks))
                <div class="alert alert-info mt-3">Không có feedback dịch vụ.</div>
            @else
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Người dùng</th>
                            <th>Dịch vụ</th>
                            <th>Giá</th>
                            <th>Feedback</th>
                            <th>Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($serviceFeedbacks as $row)
                            <tr>
                                <td>{{ $row['feedback_id'] }}</td>
                                <td>{{ $row['user_name'] }}</td>
                                <td>{{ $row['service_name'] }}</td>
                                <td>{{ number_format($row['service_price'], 0, ',', '.') }} VNĐ</td>
                                <td>{{ $row['feedback'] }}</td>
                                <td>{{ $row['rating'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

        </div>
    </div>

    {{-- ================= FEEDBACK BOOKING ================= --}}
    <div class="tab-pane fade" id="bookingTab">
        <div class="table-responsive">

            @if(empty($bookingFeedbacks))
                <div class="alert alert-info mt-3">Không có feedback booking sân.</div>
            @else
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Người dùng</th>
                            <th>Sân</th>
                            <th>Ngày</th>
                            <th>Thời gian</th>
                            <th>Feedback</th>
                            <th>Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookingFeedbacks as $b)
                            <tr>
                                <td>{{ $b['booking_id'] }}</td>
                                <td>{{ $b['user_name'] }}</td>
                                <td>{{ $b['field_name'] }}</td>
                                <td>{{ \Carbon\Carbon::parse($b['booking_date'])->format('d/m/Y') }}</td>
                                <td>{{ $b['start_time'] }} - {{ $b['end_time'] }}</td>
                                <td>{{ $b['feedback_message'] }}</td>
                                <td>{{ $b['feedback_rating'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

        </div>
    </div>

</div>
@endsection
