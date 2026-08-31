@extends('layouts.app')

@section('content')

{{-- Leaflet CSS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

<style>
/* ══════════════════════════════════════════════
   TRACKING PAGE STYLES
══════════════════════════════════════════════ */
:root {
    --track-primary: #4f46e5;
    --track-success: #10b981;
    --track-warning: #f59e0b;
    --track-danger:  #ef4444;
    --track-muted:   #94a3b8;
    --track-bg:      #f8fafc;
    --card-radius:   16px;
}

body { background: var(--track-bg); }

.tracking-header {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: #fff;
    border-radius: var(--card-radius);
    padding: 1.5rem 2rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 20px rgba(79,70,229,.3);
}
.tracking-header h2 { font-weight: 700; margin: 0; }
.tracking-header small { opacity: .8; }

/* ── Tracking card ── */
.track-card {
    background: #fff;
    border-radius: var(--card-radius);
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    overflow: hidden;
    margin-bottom: 1.25rem;
}
.track-card-header {
    padding: .85rem 1.25rem;
    font-weight: 600;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: .5rem;
    font-size: .95rem;
}
.track-card-body { padding: 1.25rem; }

/* ── Timeline ── */
.timeline {
    position: relative;
    padding: 0;
    margin: 0;
    list-style: none;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 19px;
    top: 0; bottom: 0;
    width: 2px;
    background: #e2e8f0;
}
.timeline-item {
    position: relative;
    padding-left: 52px;
    margin-bottom: 1.5rem;
    transition: opacity .3s;
}
.timeline-item:last-child { margin-bottom: 0; }
.timeline-dot {
    position: absolute;
    left: 8px;
    top: 2px;
    width: 24px; height: 24px;
    border-radius: 50%;
    background: #e2e8f0;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #e2e8f0;
    display: flex; align-items: center; justify-content: center;
    font-size: .65rem;
    color: #fff;
    transition: all .4s ease;
    z-index: 1;
}
.timeline-dot.active {
    background: var(--track-primary);
    box-shadow: 0 0 0 4px rgba(79,70,229,.25);
    animation: pulse-dot 2s infinite;
}
.timeline-dot.done {
    background: var(--track-success);
    box-shadow: 0 0 0 2px rgba(16,185,129,.2);
}
@keyframes pulse-dot {
    0%,100% { box-shadow: 0 0 0 4px rgba(79,70,229,.25); }
    50%      { box-shadow: 0 0 0 8px rgba(79,70,229,.1); }
}
.timeline-label {
    font-weight: 600;
    font-size: .9rem;
    color: #1e293b;
    line-height: 1.3;
}
.timeline-label.muted { color: #94a3b8; font-weight: 400; }
.timeline-time {
    font-size: .76rem;
    color: #94a3b8;
    margin-top: .1rem;
}

/* ── Status badge ── */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .4rem 1rem;
    border-radius: 50px;
    font-size: .85rem;
    font-weight: 600;
}
.status-badge.created      { background: #ede9fe; color: #5b21b6; }
.status-badge.picked_up    { background: #dbeafe; color: #1d4ed8; }
.status-badge.transporting { background: #fef3c7; color: #92400e; }
.status-badge.delivering   { background: #fed7aa; color: #9a3412; }
.status-badge.delivered    { background: #d1fae5; color: #065f46; }

/* ── Map ── */
#map {
    height: 420px;
    border-radius: 0 0 var(--card-radius) var(--card-radius);
}

/* ── Demo control ── */
.demo-control { background: #fafafa; border-radius: 12px; padding: 1rem; }
.demo-control select { border-radius: 8px; }
.demo-btn {
    background: linear-gradient(135deg, var(--track-primary), #7c3aed);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: .55rem 1rem;
    font-weight: 600;
    width: 100%;
    cursor: pointer;
    transition: opacity .2s, transform .1s;
}
.demo-btn:hover   { opacity: .9; }
.demo-btn:active  { transform: scale(.98); }
.demo-btn:disabled{ opacity: .6; cursor: not-allowed; }

/* ── Shipper marker animation ── */
.shipper-icon-inner {
    width: 36px; height: 36px;
    background: #f59e0b;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,.3);
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
}

/* ── Info row ── */
.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: .5rem 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: .9rem;
}
.info-row:last-child { border: none; padding-bottom: 0; }
.info-label { color: #64748b; }
.info-value { font-weight: 600; color: #1e293b; }

/* ── Polling indicator ── */
.poll-badge {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    font-size: .73rem;
    color: #64748b;
    background: #f1f5f9;
    border-radius: 20px;
    padding: .2rem .7rem;
}
.poll-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: var(--track-success);
    animation: blink 1.4s ease-in-out infinite;
}
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.2} }

.back-btn {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    color: rgba(255,255,255,.85);
    text-decoration: none;
    font-size: .9rem;
    margin-bottom: .75rem;
    transition: color .2s;
}
.back-btn:hover { color: #fff; }
</style>

<div class="container py-4">

    {{-- Header --}}
    <div class="tracking-header">
        <a href="{{ route('user.orderDetail', $order->id) }}" class="back-btn">
            <i class="bi bi-arrow-left"></i> Quay lại chi tiết đơn
        </a>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h2><i class="bi bi-truck me-2"></i>Theo dõi đơn #{{ $order->id }}</h2>
                <small>Mã vận đơn: <strong>{{ $shipment->tracking_code }}</strong></small>
            </div>
            <div class="text-end">
                <div id="status-badge-wrap">
                    <span class="status-badge {{ $shipment->status }}" id="status-badge">
                        <i class="bi bi-circle-fill" style="font-size:.5rem"></i>
                        <span id="status-text">{{ $shipment->statusLabel() }}</span>
                    </span>
                </div>
                <div class="mt-1">
                    <span class="poll-badge" id="poll-indicator">
                        <span class="poll-dot" id="poll-dot"></span>
                        <span id="poll-text">Đang theo dõi...</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">

        {{-- ── LEFT COLUMN ── --}}
        <div class="col-lg-4">

            {{-- Provider info --}}
            <div class="track-card">
                <div class="track-card-header">
                    <i class="bi bi-info-circle text-primary"></i> Thông tin vận chuyển
                </div>
                <div class="track-card-body">
                    <div class="info-row">
                        <span class="info-label">Nhà vận chuyển</span>
                        <span class="info-value">
                            <span class="badge bg-secondary">{{ strtoupper($shipment->provider) }}</span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Mã vận đơn</span>
                        <span class="info-value" style="font-family:monospace">{{ $shipment->tracking_code }}</span>
                    </div>
                    @if($order->userAddress)
                    <div class="info-row">
                        <span class="info-label">Giao đến</span>
                        <span class="info-value text-end" style="max-width:180px;font-size:.82rem">
                            @php
                                $addrParts = array_filter([
                                    $order->userAddress->street_address,
                                    $order->userAddress->ward,
                                    $order->userAddress->district,
                                    $order->userAddress->city,
                                ]);
                            @endphp
                            {{ implode(', ', $addrParts) }}
                        </span>
                    </div>
                    @endif
                    <div class="info-row">
                        <span class="info-label">Cập nhật lúc</span>
                        <span class="info-value" id="last-updated">{{ $shipment->last_status_at?->format('H:i d/m/Y') ?? '---' }}</span>
                    </div>
                </div>
            </div>

            {{-- Timeline --}}
            <div class="track-card">
                <div class="track-card-header">
                    <i class="bi bi-list-check text-success"></i> Tiến trình giao hàng
                </div>
                <div class="track-card-body">
                    <ul class="timeline" id="timeline">
                        @php
                            $statuses = \App\Models\OrderShipment::STATUSES;
                            $labels   = \App\Models\OrderShipment::labels();
                            $currentIndex = array_search($shipment->status, $statuses);
                            $icons = [
                                'created'      => 'bi-box',
                                'picked_up'    => 'bi-bag-check',
                                'transporting' => 'bi-truck',
                                'delivering'   => 'bi-house-door',
                                'delivered'    => 'bi-check2-circle',
                            ];
                        @endphp
                        @foreach($statuses as $i => $sv)
                        <li class="timeline-item" data-status="{{ $sv }}">
                            <div class="timeline-dot {{ $i < $currentIndex ? 'done' : ($i === $currentIndex ? 'active' : '') }}">
                                @if($i < $currentIndex)
                                    <i class="bi bi-check" style="font-size:.7rem"></i>
                                @elseif($i === $currentIndex)
                                    <i class="bi {{ $icons[$sv] ?? 'bi-circle' }}" style="font-size:.65rem"></i>
                                @endif
                            </div>
                            <div class="timeline-label {{ $i > $currentIndex ? 'muted' : '' }}">
                                {{ $labels[$sv] ?? $sv }}
                            </div>
                            @if($i <= $currentIndex)
                            <div class="timeline-time">
                                {{ $i === $currentIndex ? ($shipment->last_status_at?->format('H:i, d/m/Y') ?? '') : '' }}
                            </div>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>



        </div>

        {{-- ── RIGHT COLUMN: MAP ── --}}
        <div class="col-lg-8">
            <div class="track-card" style="overflow:visible">
                <div class="track-card-header">
                    <i class="bi bi-map text-primary"></i> Bản đồ theo dõi thời gian thực
                    <span class="ms-auto poll-badge">
                        <span class="poll-dot"></span> Cập nhật mỗi 10 giây
                    </span>
                </div>
                <div id="map"></div>
            </div>

            {{-- Map legend --}}
            <div class="track-card">
                <div class="track-card-body py-2">
                    <div class="d-flex flex-wrap gap-3 align-items-center" style="font-size:.82rem">
                        <span><span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#3b82f6;margin-right:4px"></span>Điểm lấy hàng</span>
                        <span><span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#10b981;margin-right:4px"></span>Điểm giao hàng</span>
                        <span><span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#f59e0b;margin-right:4px"></span>Vị trí shipper</span>
                        <span><span style="display:inline-block;width:32px;height:3px;background:#4f46e5;margin-right:4px;vertical-align:middle"></span>Tuyến đường</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Leaflet JS --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Initial data from server ── */
    let trackingData = @json($tracking);
    const orderId    = {{ $order->id }};
    const dataUrl    = '{{ route('user.order.tracking.data', $order->id) }}';
    const statusUrl  = '{{ route('user.order.tracking.status', $order->id) }}';
    const csrfToken  = '{{ csrf_token() }}';
    const allStatuses = @json(\App\Models\OrderShipment::STATUSES);
    const allLabels   = @json(\App\Models\OrderShipment::labels());

    /* ── Map init ── */
    const map = L.map('map', { zoomControl: true });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 18,
    }).addTo(map);

    /* ── Custom icons ── */
    function makeIcon(color, emoji) {
        return L.divIcon({
            className: '',
            html: `<div style="
                width:36px;height:36px;
                background:${color};
                border-radius:50%;
                border:3px solid #fff;
                box-shadow:0 2px 8px rgba(0,0,0,.3);
                display:flex;align-items:center;justify-content:center;
                font-size:16px;
            ">${emoji}</div>`,
            iconSize: [36, 36],
            iconAnchor: [18, 18],
            popupAnchor: [0, -20],
        });
    }

    const pickupIcon   = makeIcon('#3b82f6', '🏪');
    const deliveryIcon = makeIcon('#10b981', '🏠');
    const shipperIcon  = makeIcon('#f59e0b', '🛵');

    /* ── Markers ── */
    const pickupMarker = L.marker([trackingData.pickup.lat, trackingData.pickup.lng], {icon: pickupIcon})
        .addTo(map).bindPopup('<b>🏪 Điểm lấy hàng</b>');

    const deliveryMarker = L.marker([trackingData.delivery.lat, trackingData.delivery.lng], {icon: deliveryIcon})
        .addTo(map).bindPopup('<b>🏠 Điểm giao hàng</b>');

    let shipperMarker = L.marker([trackingData.shipper.lat, trackingData.shipper.lng], {icon: shipperIcon})
        .addTo(map).bindPopup('<b>🛵 Shipper</b>');

    /* ── Route polyline ── */
    let routePolyline = null;
    function drawRoute(points) {
        if (routePolyline) map.removeLayer(routePolyline);
        if (!points || points.length === 0) return;
        const latlngs = points.map(p => [p.lat, p.lng]);
        routePolyline = L.polyline(latlngs, {
            color: '#4f46e5',
            weight: 4,
            opacity: .6,
            dashArray: '8,4',
        }).addTo(map);
    }

    drawRoute(trackingData.route);

    /* ── Fit bounds ── */
    function fitAll() {
        const bounds = L.latLngBounds([
            [trackingData.pickup.lat, trackingData.pickup.lng],
            [trackingData.delivery.lat, trackingData.delivery.lng],
            [trackingData.shipper.lat, trackingData.shipper.lng],
        ]);
        map.fitBounds(bounds.pad(.3));
    }
    fitAll();

    /* ── Smooth move shipper ── */
    function animateMarker(marker, fromLat, fromLng, toLat, toLng, steps, ms) {
        let step = 0;
        const timer = setInterval(() => {
            step++;
            const ratio = step / steps;
            const lat = fromLat + (toLat - fromLat) * ratio;
            const lng = fromLng + (toLng - fromLng) * ratio;
            marker.setLatLng([lat, lng]);
            if (step >= steps) clearInterval(timer);
        }, ms / steps);
    }

    /* ── Update UI from tracking payload ── */
    function applyTrackingData(data) {
        trackingData = data;

        // Status badge
        const badgeEl = document.getElementById('status-badge');
        const textEl  = document.getElementById('status-text');
        if (badgeEl && textEl) {
            badgeEl.className = 'status-badge ' + data.status;
            textEl.textContent = data.status_label;
        }

        // Timeline
        updateTimeline(data.status);

        // Select
        const sel = document.getElementById('status-select');
        if (sel) sel.value = data.status;

        // Animate shipper
        const cur = shipperMarker.getLatLng();
        animateMarker(shipperMarker,
            cur.lat, cur.lng,
            data.shipper.lat, data.shipper.lng,
            30, 600
        );

        // Redraw route if changed
        if (data.route && data.route.length > 0) {
            drawRoute(data.route);
        }

        // Pan to shipper if delivered – fitAll otherwise
        if (data.status === 'delivered') {
            setTimeout(() => map.panTo([data.delivery.lat, data.delivery.lng]), 700);
        } else {
            setTimeout(fitAll, 700);
        }
    }

    /* ── Timeline update ── */
    function updateTimeline(currentStatus) {
        const currentIndex = allStatuses.indexOf(currentStatus);
        document.querySelectorAll('.timeline-item').forEach((item, i) => {
            const dot   = item.querySelector('.timeline-dot');
            const label = item.querySelector('.timeline-label');
            const time  = item.querySelector('.timeline-time');
            dot.className = 'timeline-dot';
            if (i < currentIndex) {
                dot.className += ' done';
                dot.innerHTML = '<i class="bi bi-check" style="font-size:.7rem"></i>';
            } else if (i === currentIndex) {
                dot.className += ' active';
                dot.innerHTML = '';
            } else {
                dot.innerHTML = '';
            }
            if (label) label.classList.toggle('muted', i > currentIndex);
            if (time) {
                if (i === currentIndex) {
                    time.textContent = new Date().toLocaleTimeString('vi-VN', {hour:'2-digit',minute:'2-digit'}) + ', ' + new Date().toLocaleDateString('vi-VN');
                } else if (i > currentIndex) {
                    time.textContent = '';
                }
            }
        });
    }

    /* ── Auto-poll every 10 seconds ── */
    const pollText = document.getElementById('poll-text');
    let pollCountdown = 10;
    function tick() {
        pollCountdown--;
        if (pollText) pollText.textContent = `Làm mới sau ${pollCountdown}s`;
        if (pollCountdown <= 0) {
            pollCountdown = 10;
            fetch(dataUrl, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
                .then(r => r.json())
                .then(data => {
                    applyTrackingData(data);
                    if (pollText) pollText.textContent = 'Đã cập nhật';
                    setTimeout(() => { if (pollText) pollText.textContent = `Làm mới sau 10s`; }, 1200);
                })
                .catch(() => {});
        }
    }
    setInterval(tick, 1000);

});
</script>

@endsection
