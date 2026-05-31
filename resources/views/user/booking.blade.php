@extends('layouts.app')
@section('content')
@if(session('success'))
    <div id="success-alert" class="alert alert-success">
        {{ session('success') }}
    </div>

    <script>
        setTimeout(() => {
            document.getElementById('success-alert').remove();
        }, 2500);

        setTimeout(() => {
            window.location.href = "{{ route('user.myBookings') }}";
        }, 3000);
    </script>
@endif
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1><i class="bi bi-calendar-plus"></i> Đặt sân: {{ $field->name }}</h1>
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
<div id="client-errors" class="alert alert-danger d-none mb-3"></div>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('user.bookingstore') }}" id="bookingForm">
                        <input type="hidden" name="field_id" value="{{ $field->id }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Ngày đặt sân</label>
                                    <input type="date" class="form-control @error('booking_date') is-invalid @enderror" 
                                           name="booking_date" 
                                           min="{{ date('Y-m-d') }}" 
                                                  id="booking_date"
                                           value="{{ old('booking_date') }}" 
                                           required>
                                    @error('booking_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Bắt đầu</label>
                                    <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                                           name="start_time" 
                                                  id="start_time"
                                           value="{{ old('start_time') }}" 
                                           required>
                                    @error('start_time')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Kết thúc</label>
                                    <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                                           name="end_time" 
                                                  id="end_time"
                                           value="{{ old('end_time') }}" 
                                           required>
                                    @error('end_time')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div id="check-result" class="mt-2"></div>
<div id="price_note" style="white-space: pre-line; color: red;"></div>
                        <!-- Dịch vụ dạng lưới với ảnh -->
                        @if($services && count($services) > 0)
                            <hr>
                            <h5 class="mb-3">Dịch vụ thêm</h5>
                            <div style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 15px; border-radius: 8px;">
                                <div class="row g-2">
                                    @foreach($services as $service)
                                        <div class="col-6 col-md-4 col-lg-3">
                                            <div class="card text-center service-card" style="cursor: pointer; transition: all 0.3s;">
                                                <img src="{{ asset('uploads/services/' . ($service->image ?? 'default.png')) }}" 
                                                     class="card-img-top" style="height: 100px; object-fit: cover; border-radius: 6px 6px 0 0;">
                                                <div class="card-body p-2">
                                                    <strong class="d-block text-truncate" title="{{ $service->name }}">
                                                        {{ $service->name }}
                                                    </strong>
                                                    <span class="text-success d-block fw-bold">{{ number_format($service->price, 0, ',', '.') }} VNĐ</span>
                                                    <span class="text-muted d-block" style="font-size: 0.8em;">Còn: {{ $service->quantity }}</span>
                                                    
                                                    <div class="mt-2">
                                                        <button type="button" class="btn btn-sm btn-outline-primary add-service" 
                                                                data-id="{{ $service->id }}" 
                                                                data-price="{{ $service->price }}" 
                                                                data-qty="{{ $service->quantity }}"
                                                                data-name="{{ $service->name }}">
                                                            +
                                                        </button>
                                                        <span class="qty-badge" data-id="{{ $service->id }}" style="margin: 0 5px;">0</span>
                                                        <button type="button" class="btn btn-sm btn-outline-danger remove-service" 
                                                                data-id="{{ $service->id }}">
                                                            -
                                                        </button>
                                                        <input type="hidden" name="services[{{ $service->id }}]" 
                                                               class="qty-input" 
                                                               id="input_{{ $service->id }}" 
                                                               value="0" 
                                                               data-price="{{ $service->price }}" 
                                                               data-stock="{{ $service->quantity }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Tổng giá:</h5>
                            <span id="total_price" class="text-primary fw-bold" style="font-size: 1.3rem;">0 VNĐ</span>
                        </div>

                        <button id="submitBtn" type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-check-circle"></i> Đặt sân
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Chi tiết sân -->
        <div class="col-md-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-body">
                    <h5 class="card-title mb-3">Chi tiết sân</h5>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên sân</label>
                        <p class="form-control-plaintext">{{ $field->name }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Địa chỉ</label>
                        <p class="form-control-plaintext">{{ $field->location }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Mô tả</label>
                        <p class="form-control-plaintext small">{{ $field->description }}</p>
                    </div>

                    <div class="alert alert-info mb-3">
                        <strong>Giá sân/giờ:</strong> 
                        <span class="text-success fw-bold">{{ number_format($field->price_per_hour, 0, ',', '.') }} VNĐ</span>
                    </div>

                    <hr>
                    <h6 class="mb-2">Giá dự kiến</h6>
                    <p id="estimated_price" class="fs-5 text-primary fw-bold">0 VNĐ</p>

                    <!-- Thông tin thêm -->
                    @if($field->image)
                        <div class="mt-3">
                            <img src="{{ asset('uploads/fields/' . $field->image) }}" 
                                 alt="{{ $field->name }}" 
                                 class="img-fluid rounded">
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.service-card {
    border: 2px solid #f0f0f0;
}

.service-card:hover {
    border-color: #007bff;
    box-shadow: 0 2px 8px rgba(0, 123, 255, 0.2);
    transform: translateY(-2px);
}

.qty-badge {
    font-weight: bold;
    color: #007bff;
    font-size: 1.1rem;
}
</style>
<script>
    const errorBox = document.getElementById('client-errors');

function showError(message) {
    errorBox.innerHTML = message;
    errorBox.classList.remove('d-none');
}

function clearError() {
    errorBox.innerHTML = '';
    errorBox.classList.add('d-none');
}
document.addEventListener('DOMContentLoaded', function () {

    const fieldPricePerHour = {{ $field->price_per_hour }};
    const priceRules = @json($priceRules ?? []);
    const submitBtn = document.getElementById('submitBtn');

    // ===== TIME UTILS =====
    function toMinutes(time) {
        let [h, m] = time.split(':').map(Number);
        return h * 60 + m;
    }

    function formatTime(minutes) {
        minutes = minutes % 1440;
        let h = Math.floor(minutes / 60).toString().padStart(2, '0');
        let m = (minutes % 60).toString().padStart(2, '0');
        return `${h}:${m}`;
    }

    // ===== TÍNH TỔNG =====
    function updateTotal() {
        const startTime = document.getElementById('start_time').value;
        const endTime = document.getElementById('end_time').value;

        let total = 0;
        let surgeNotes = [];

        if (startTime && endTime) {
            let start = toMinutes(startTime);
            let end = toMinutes(endTime);

            // qua ngày
            if (end <= start) end += 1440;

            let current = start;

            while (current < end) {
                let next = Math.min(current + 60, end);
                let diff = (next - current) / 60;

                let applied = fieldPricePerHour;
                let currentInDay = current % 1440;

                for (let rule of priceRules) {
                    let ruleStart = toMinutes(rule.start_time);
                    let ruleEnd = toMinutes(rule.end_time);

                    if (
                        (ruleStart <= ruleEnd && currentInDay >= ruleStart && currentInDay < ruleEnd) ||
                        (ruleStart > ruleEnd && (currentInDay >= ruleStart || currentInDay < ruleEnd))
                    ) {
                        applied = fieldPricePerHour * parseFloat(rule.multiplier);
                        surgeNotes.push(`${formatTime(current)} - ${formatTime(next)} x${rule.multiplier}`);
                        break;
                    }
                }

                total += diff * applied;
                current = next;
            }
        }

        // ===== DỊCH VỤ =====
        document.querySelectorAll('.qty-input').forEach(input => {
            const qty = parseInt(input.value) || 0;
            const price = parseFloat(input.dataset.price) || 0;
            total += qty * price;
        });

        // ===== UI =====
        const formatted = new Intl.NumberFormat('vi-VN').format(total);
        document.getElementById('total_price').innerText = formatted + ' VNĐ';
        document.getElementById('estimated_price').innerText = formatted + ' VNĐ';

       const noteBox = document.getElementById('price_note');

if (noteBox) {
    if (priceRules.length > 0) {
        let text = "Khung giờ giá cao:\n";

        priceRules.forEach(rule => {
            text += `Từ ${rule.start_time} - ${rule.end_time} giá x${rule.multiplier}\n`;
        });

        noteBox.innerText = text;
    } else {
        noteBox.innerText = "";
    }
}
    }

    // ===== THÊM/XÓA DỊCH VỤ =====
    document.querySelectorAll('.add-service').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const maxQty = parseInt(this.dataset.qty);
            const input = document.getElementById('input_' + id);

            let qty = parseInt(input.value) || 0;

            if (qty >= maxQty) {
               showError('Vượt quá số lượng dịch vụ còn lại');
                return;
            }

            qty++;
            input.value = qty;
            document.querySelector(`.qty-badge[data-id="${id}"]`).innerText = qty;

            updateTotal();
        });
    });

    document.querySelectorAll('.remove-service').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const input = document.getElementById('input_' + id);

            let qty = parseInt(input.value) || 0;
            if (qty <= 0) return;

            qty--;
            input.value = qty;
            document.querySelector(`.qty-badge[data-id="${id}"]`).innerText = qty;

            updateTotal();
        });
    });

    // ===== CHANGE TIME =====
    document.getElementById('start_time').addEventListener('change', updateTotal);
    document.getElementById('end_time').addEventListener('change', updateTotal);

    // ===== CHECK BOOKING =====
    function checkBooking() {
        let field_id = {{ $field->id }};
        let booking_date = document.getElementById('booking_date').value;
        let start_time = document.getElementById('start_time').value;
        let end_time = document.getElementById('end_time').value;

        if (!booking_date || !start_time || !end_time) return;

        fetch("{{ route('user.check.booking') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                field_id,
                booking_date,
                start_time,
                end_time
            })
        })
        .then(res => res.json())
        .then(data => {
            let result = document.getElementById('check-result');

            if (data.available) {
                result.innerHTML = "✅ Khung giờ còn trống";
                result.style.color = "green";
                submitBtn.disabled = false;
            } else {
                result.innerHTML = "❌ Khung giờ đã có người đặt";
                result.style.color = "red";
                submitBtn.disabled = true;
            }
        });
    }

    document.querySelectorAll('#booking_date, #start_time, #end_time')
        .forEach(el => el.addEventListener('change', checkBooking));

    // ===== SUBMIT =====
    document.getElementById('bookingForm').addEventListener('submit', function (e) {

        if (submitBtn.disabled) {
            e.preventDefault();
            return;
        }

     clearError();

const bookingDate = document.getElementById('booking_date').value;
const startTime = document.getElementById('start_time').value;
const endTime = document.getElementById('end_time').value;

if (!bookingDate || !startTime || !endTime) {
    e.preventDefault();
    showError('Vui lòng nhập đủ thông tin');
    return;
}

if (startTime === endTime) {
    e.preventDefault();
    showError('Không cho phép đặt sân');
    return;
}
if (startTime >= endTime) {
    e.preventDefault();
    showError('Giờ kết thúc phải lớn hơn giờ bắt đầu');
    return;
}
const today = new Date();
today.setHours(0,0,0,0);

const selectedDate = new Date(bookingDate);

if (selectedDate < today) {
    e.preventDefault();
    showError('Ngày không hợp lệ');
    return;
}

submitBtn.disabled = true;
submitBtn.innerHTML = 'Đang xử lý...';
        });
    // ===== INIT =====
    updateTotal();
});
</script>
@endsection
