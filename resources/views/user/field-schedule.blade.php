@extends('layouts.app')
@section('content')

<div class="container py-4">

    {{-- PAGE HEADER --}}
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Lịch đặt sân</h2>
        <p class="text-muted mb-0">
            Danh sách toàn bộ khung giờ đã được đặt của các sân
        </p>
    </div>


    @if($fields && count($fields) > 0)

        @foreach($fields as $field)

        <div class="card field-card mb-4">

            {{-- FIELD HEADER --}}
            <div class="card-header field-header">

                <div class="d-flex justify-content-between align-items-center">

                    <div>
                        <div class="field-name">
                            {{ $field->name }}
                        </div>

                        @if($field->location)
                        <div class="field-location">
                            {{ $field->location }}
                        </div>
                        @endif
                    </div>

                </div>

            </div>


            {{-- FIELD BODY --}}
            <div class="card-body">

                @if(isset($bookingMap[$field->id]) && count($bookingMap[$field->id]) > 0)

                <div class="table-responsive">

                    <table class="table table-modern">

                        <thead>
                            <tr>
                                <th>Ngày</th>
                                <th>Bắt đầu</th>
                                <th>Kết thúc</th>
                                <th>Người đặt</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>


                        <tbody>

                            @foreach($bookingMap[$field->id] as $booking)

                            <tr>

                                <td>
                                    {{ Carbon\Carbon::parse($booking->booking_date)->format('d/m/Y') }}
                                </td>

                                <td>
                                    <span class="time-badge">
                                        {{ substr($booking->start_time,0,5) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="time-badge">
                                        {{ substr($booking->end_time,0,5) }}
                                    </span>
                                </td>

                                <td>
                                    {{ $booking->user?->name ?? 'Khách' }}
                                </td>

                                <td>

                                    @if($booking->status === 'confirmed')

                                        <span class="status success">
                                            Đã xác nhận
                                        </span>

                                    @else

                                        <span class="status pending">
                                            Chờ xác nhận
                                        </span>

                                    @endif

                                </td>

                            </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>


                @else

                <div class="empty-state">

                    Chưa có lịch đặt sân

                </div>

                @endif

            </div>

        </div>

        @endforeach


    @else

    <div class="empty-wrapper">

        Không có dữ liệu sân hoặc lịch đặt

    </div>

    @endif

</div>



<style>

.field-card {

border-radius:14px;
border:none;
box-shadow:0 2px 12px rgba(0,0,0,.06);

}


.field-header {

background:#fff;
border-bottom:1px solid #eee;

}


.field-name {

font-weight:600;
font-size:18px;

}


.field-location {

font-size:13px;
color:#888;

}


.table-modern thead {

background:#fafafa;

}


.table-modern th {

font-weight:600;
font-size:14px;
color:#666;

}


.table-modern td {

vertical-align:middle;

}


.time-badge {

background:#f2f4f7;
padding:6px 12px;
border-radius:8px;
font-size:13px;

}


.status {

padding:6px 12px;
border-radius:8px;
font-size:13px;

}


.status.success {

background:#e8f8f0;
color:#1f9254;

}


.status.pending {

background:#fff6e5;
color:#c47a00;

}


.empty-state {

background:#fafafa;
padding:20px;
border-radius:10px;
text-align:center;
color:#888;

}


.empty-wrapper {

background:#fafafa;
padding:40px;
border-radius:12px;
text-align:center;
color:#888;

}

</style>


@endsection