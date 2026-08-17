@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="{{ route('user.orderDetail', $order->id) }}" class="btn btn-secondary mb-3">
                <i class="bi bi-arrow-left"></i> Quay lại chi tiết đơn hàng
            </a>
            <h2>Theo dõi đơn hàng #{{ $order->id }}</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Thông tin vận chuyển</h5>
                </div>
                <div class="card-body">
                    <p><strong>Mã vận đơn:</strong> {{ $shipment->tracking_code }}</p>
                    <p>
                        <strong>Nhà vận chuyển:</strong> 
                        <span class="badge bg-secondary">{{ strtoupper($shipment->provider) }}</span>
                    </p>
                    <p>
                        <strong>Trạng thái hiện tại:</strong> 
                        <span class="badge bg-success" id="current-status">{{ $shipment->statusLabel() }}</span>
                    </p>

                    @if($shipment->provider === 'demo' || $shipment->provider === 'ghn_test')
                        <hr>
                        <h6>Cập nhật trạng thái (Demo)</h6>
                        <select id="status-select" class="form-select mb-2">
                            @foreach(\App\Models\OrderShipment::STATUSES as $statusVal)
                                <option value="{{ $statusVal }}" {{ $shipment->status === $statusVal ? 'selected' : '' }}>
                                    {{ \App\Models\OrderShipment::labels()[$statusVal] ?? $statusVal }}
                                </option>
                            @endforeach
                        </select>
                        <button id="update-status-btn" class="btn btn-outline-primary w-100">
                            Cập nhật & Di chuyển
                        </button>
                    @endif
                </div>
            </div>

            @if($shipment->provider_error)
            <div class="alert alert-warning shadow-sm">
                <strong>Lưu ý:</strong> API GHN gặp lỗi, hệ thống đang dùng chế độ Demo.<br>
                <small>{{ $shipment->provider_error }}</small>
            </div>
            @endif
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div id="map" style="height: 500px; border-radius: 0.375rem;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const trackingData = @json($tracking);
    
    // Khởi tạo bản đồ
    const map = L.map('map');
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Custom Icons
    const pickupIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    const deliveryIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    const shipperIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-orange.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    // Markers
    const pickupMarker = L.marker([trackingData.pickup.lat, trackingData.pickup.lng], {icon: pickupIcon})
        .addTo(map)
        .bindPopup('<b>Điểm lấy hàng</b>');

    const deliveryMarker = L.marker([trackingData.delivery.lat, trackingData.delivery.lng], {icon: deliveryIcon})
        .addTo(map)
        .bindPopup('<b>Điểm giao hàng</b>');

    let shipperMarker = L.marker([trackingData.shipper.lat, trackingData.shipper.lng], {icon: shipperIcon})
        .addTo(map)
        .bindPopup('<b>Shipper</b>');

    // Vẽ tuyến đường
    if (trackingData.route && trackingData.route.length > 0) {
        const latlngs = trackingData.route.map(p => [p.lat, p.lng]);
        const polyline = L.polyline(latlngs, {color: 'blue', weight: 3, opacity: 0.5}).addTo(map);
        map.fitBounds(polyline.getBounds());
    } else {
        const bounds = L.latLngBounds([
            [trackingData.pickup.lat, trackingData.pickup.lng],
            [trackingData.delivery.lat, trackingData.delivery.lng]
        ]);
        map.fitBounds(bounds);
    }

    // Cập nhật trạng thái và di chuyển shipper
    const updateBtn = document.getElementById('update-status-btn');
    if (updateBtn) {
        updateBtn.addEventListener('click', function() {
            const status = document.getElementById('status-select').value;
            const originalText = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';
            this.disabled = true;

            fetch(`{{ route('user.order.tracking.status', $order->id) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ status: status })
            })
            .then(res => res.json())
            .then(data => {
                document.getElementById('current-status').innerText = data.status_label;
                
                // Di chuyển mượt mà
                const newLat = data.shipper.lat;
                const newLng = data.shipper.lng;
                
                // Cập nhật vị trí
                shipperMarker.setLatLng([newLat, newLng]);
                map.panTo([newLat, newLng]);
                
            })
            .catch(err => {
                console.error(err);
                alert('Có lỗi xảy ra khi cập nhật.');
            })
            .finally(() => {
                this.innerHTML = originalText;
                this.disabled = false;
            });
        });
    }
});
</script>
@endsection
