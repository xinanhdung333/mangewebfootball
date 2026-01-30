@extends('layouts.app')
@section('content')

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1><i class="bi bi-chat-left-heart"></i> Feedback của bạn</h1>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Lỗi!</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- TABS -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="serviceTab-tab" data-bs-toggle="tab" data-bs-target="#serviceTab" 
                    type="button" role="tab" aria-controls="serviceTab" aria-selected="true">
                <i class="bi bi-bag-check"></i> Dịch vụ đã mua
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="bookingTab-tab" data-bs-toggle="tab" data-bs-target="#bookingTab" 
                    type="button" role="tab" aria-controls="bookingTab" aria-selected="false">
                <i class="bi bi-calendar-check"></i> Booking sân
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- ================= TAB DỊCH VỤ ================= -->
        <div class="tab-pane fade show active" id="serviceTab" role="tabpanel" aria-labelledby="serviceTab-tab">
            @if($services && count($services) > 0)
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
                                        <strong>#{{ $service['order_item_id'] }}</strong>
                                    </td>
                                    <td>{{ $service['service_name'] }}</td>
                                    <td>
                                        @if($service['service_image'])
                                            <img src="{{ asset('uploads/services/' . $service['service_image']) }}" 
                                                 alt="Service" width="60" height="60" class="rounded">
                                        @else
                                            <span class="text-muted">Không có ảnh</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">
                                            {{ number_format($service['total'], 0, ',', '.') }} VNĐ
                                        </span>
                                    </td>
                                    <td>
                                        @if($service['feedback_message'])
                                            <span class="text-muted small">{{ $service['feedback_message'] }}</span>
                                        @else
                                            <span class="text-muted small">Chưa có feedback</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($service['feedback_rating'])
                                            <span style="color: #ffc107;">
                                                @for($i = 0; $i < $service['feedback_rating']; $i++)
                                                    ★
                                                @endfor
                                                @for($i = $service['feedback_rating']; $i < 5; $i++)
                                                    ☆
                                                @endfor
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$service['feedback_message'])
                                            <button class="btn btn-sm btn-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#feedbackModal"
                                                    data-type="service"
                                                    data-item-id="{{ $service['order_item_id'] }}">
                                                <i class="bi bi-pencil"></i> Gửi
                                            </button>
                                        @else
                                            <span class="badge bg-success">Đã gửi</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info text-center py-5">
                    <i class="bi bi-info-circle" style="font-size: 3rem;"></i>
                    <p class="mt-3">Bạn chưa mua dịch vụ nào.</p>
                </div>
            @endif
        </div>

        <!-- ================= TAB BOOKING ================= -->
        <div class="tab-pane fade" id="bookingTab" role="tabpanel" aria-labelledby="bookingTab-tab">
            @if($bookings && count($bookings) > 0)
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
                                        <strong>#{{ $booking['booking_id'] }}</strong>
                                    </td>
                                    <td>{{ $booking['field_name'] }}</td>
                                    <td>
                                        @if($booking['field_image'])
                                            <img src="{{ asset('uploads/fields/' . $booking['field_image']) }}" 
                                                 alt="Field" width="60" height="60" class="rounded">
                                        @else
                                            <span class="text-muted">Không có ảnh</span>
                                        @endif
                                    </td>
                                    <td>{{ date('d/m/Y', strtotime($booking['booking_date'])) }}</td>
                                    <td>{{ $booking['start_time'] }} - {{ $booking['end_time'] }}</td>
                                    <td>
                                        @if($booking['feedback_message'])
                                            <span class="text-muted small">{{ $booking['feedback_message'] }}</span>
                                        @else
                                            <span class="text-muted small">Chưa có feedback</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($booking['feedback_rating'])
                                            <span style="color: #ffc107;">
                                                @for($i = 0; $i < $booking['feedback_rating']; $i++)
                                                    ★
                                                @endfor
                                                @for($i = $booking['feedback_rating']; $i < 5; $i++)
                                                    ☆
                                                @endfor
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$booking['feedback_message'])
                                            <button class="btn btn-sm btn-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#feedbackModal"
                                                    data-type="booking"
                                                    data-item-id="{{ $booking['booking_id'] }}">
                                                <i class="bi bi-pencil"></i> Gửi
                                            </button>
                                        @else
                                            <span class="badge bg-success">Đã gửi</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info text-center py-5">
                    <i class="bi bi-info-circle" style="font-size: 3rem;"></i>
                    <p class="mt-3">Bạn chưa đặt sân nào hoặc chưa hoàn thành.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- FEEDBACK MODAL -->
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="feedbackModalLabel">Gửi Feedback</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form method="POST" action="{{ route('user.sendFeedback') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="feedback_type" id="feedbackType">
                    <input type="hidden" name="item_id" id="itemId">

                    <div class="mb-3">
                        <label for="message" class="form-label">Nhận xét của bạn</label>
                        <textarea class="form-control @error('message') is-invalid @enderror" 
                                  id="message" name="message" rows="3" required 
                                  placeholder="Viết nhận xét chi tiết về dịch vụ..."></textarea>
                        @error('message')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="rating" class="form-label">Đánh giá (1-5 sao)</label>
                        <div class="rating-selector" id="ratingSelector">
                            <input type="radio" id="star5" name="rating" value="5">
                            <label for="star5" class="star" title="Tuyệt vời">★</label>

                            <input type="radio" id="star4" name="rating" value="4">
                            <label for="star4" class="star" title="Tốt">★</label>

                            <input type="radio" id="star3" name="rating" value="3">
                            <label for="star3" class="star" title="Bình thường">★</label>

                            <input type="radio" id="star2" name="rating" value="2">
                            <label for="star2" class="star" title="Tệ">★</label>

                            <input type="radio" id="star1" name="rating" value="1">
                            <label for="star1" class="star" title="Rất tệ">★</label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Gửi Feedback
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.rating-selector {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 10px;
    font-size: 2rem;
}

.rating-selector input {
    display: none;
}

.rating-selector .star {
    color: #ddd;
    cursor: pointer;
    transition: all 0.2s;
}

.rating-selector input:checked ~ .star,
.rating-selector .star:hover,
.rating-selector .star:hover ~ .star {
    color: #ffc107;
}

.rating-selector label:hover {
    transform: scale(1.1);
}
</style>

<script>
// Fill form when modal opens
const feedbackModal = document.getElementById('feedbackModal');
feedbackModal.addEventListener('show.bs.modal', function(e) {
    const button = e.relatedTarget;
    document.getElementById('feedbackType').value = button.dataset.type;
    document.getElementById('itemId').value = button.dataset.itemId;
    document.getElementById('message').value = '';
    
    // Reset rating
    document.querySelectorAll('input[name="rating"]').forEach(input => {
        input.checked = false;
    });
});
</script>

@endsection
