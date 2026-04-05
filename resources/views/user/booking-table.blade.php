            <div class="row">
                    <div class="col-md-12">
                        @if($bookings && $bookings->count() > 0)
                            <div class="table-responsive rounded-3 overflow-hidden">
                                <table class="table table-striped table-hover mb-0">
                                    <thead>
                                        <tr style="background-color: #061625ff;">
                                            <th>ID</th>
                                            <th>Sân</th>
                                            <th>Ngày</th>
                                            <th>Thời gian</th>
                                            <th>Giá</th>
                                            <th>Dịch vụ đã mua</th>
                                            <th>Trạng thái</th>
                                            <th>Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bookings as $booking)
                                            <tr>
                                                <td>#{{ $booking->id }}</td>
                                                <td>{{ $booking->field->name ?? 'N/A' }}</td>
                                                <td>{{ date('d/m/Y', strtotime($booking->booking_date)) }}</td>
                                                <td>{{ $booking->start_time }} - {{ $booking->end_time }}</td>
                                                <td>{{ formatCurrency($booking->total_price) }}</td>
                                                <td>
                                                    @if($booking->services && count($booking->services) > 0)
                                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#servicesModal{{ $booking->id }}">
                                                            Xem dịch vụ ({{ count($booking->services) }})
                                                        </button>
                                                    @else
                                                        <span class="text-muted">Không có</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $statusMap = [
                                                            'pending' => ['warning', 'Chờ xác nhận'],
                                                            'confirmed' => ['success', 'Xác nhận'],
                                                            'in_progress' => ['info', 'Đang diễn ra'],
                                                            'completed' => ['success', 'Hoàn thành'],
                                                            'cancelled' => ['danger', 'Hủy'],
                                                            'expired' => ['dark', 'Hết hạn'],
                                                        ];
                                                        $status = $statusMap[$booking->status] ?? ['secondary', 'Không xác định'];
                                                    @endphp
                                                    <span class="badge bg-{{ $status[0] }}">{{ $status[1] }}</span>
                                                </td>
                                                <td>
                                                <a href="{{ route('user.bookingdetail', $booking->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye"></i> Xem
                </a>
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
                            <div class="alert alert-info">
                                Bạn chưa có đặt sân nào. <a href="{{ route('user.fields') }}">Đặt ngay</a>
                            </div>
                        @endif
                    </div>
                </div>

              <!-- Modal Dịch vụ -->
@foreach($bookings as $booking)

@if($booking->services && count($booking->services) > 0)

<div class="modal fade" id="servicesModal{{ $booking->id }}" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">

<div class="modal-header">
<h5 class="modal-title">
Dịch vụ - Booking #{{ $booking->id }}
</h5>

<button type="button"
class="btn-close"
data-bs-dismiss="modal">
</button>
</div>

<div class="modal-body">

<table class="table table-sm">

<thead>
<tr>
<th>Dịch vụ</th>
<th>Số lượng</th>
<th>Giá</th>
</tr>
</thead>

<tbody>

@foreach($booking->services as $service)

<tr>
<td>{{ $service->name }}</td>
<td>{{ $service->pivot->quantity }}</td>
<td>
{{ formatCurrency($service->price * $service->pivot->quantity) }}
</td>
</tr>

@endforeach

</tbody>

</table>

</div>

</div>
</div>
</div>

@endif

@endforeach