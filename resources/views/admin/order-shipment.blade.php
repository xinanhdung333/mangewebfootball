@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

<style>
:root{--p:#4f46e5;--s:#10b981;--w:#f59e0b;}
.track-card{background:#fff;border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.07);overflow:hidden;margin-bottom:1.2rem;}
.track-card-header{padding:.8rem 1.2rem;font-weight:600;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:.5rem;}
.track-card-body{padding:1.2rem;}
.info-row{display:flex;justify-content:space-between;align-items:center;padding:.45rem 0;border-bottom:1px solid #f8fafc;font-size:.88rem;}
.info-row:last-child{border:none;padding-bottom:0;}
.info-label{color:#64748b;}
.info-value{font-weight:600;color:#1e293b;}
.timeline{list-style:none;padding:0;margin:0;position:relative;}
.timeline::before{content:'';position:absolute;left:19px;top:0;bottom:0;width:2px;background:#e2e8f0;}
.timeline-item{position:relative;padding-left:50px;margin-bottom:1.4rem;}
.timeline-item:last-child{margin-bottom:0;}
.timeline-dot{position:absolute;left:8px;top:2px;width:24px;height:24px;border-radius:50%;background:#e2e8f0;border:3px solid #fff;box-shadow:0 0 0 2px #e2e8f0;display:flex;align-items:center;justify-content:center;font-size:.62rem;color:#fff;transition:all .3s;z-index:1;}
.timeline-dot.done{background:var(--s);box-shadow:0 0 0 2px rgba(16,185,129,.2);}
.timeline-dot.active{background:var(--p);box-shadow:0 0 0 4px rgba(79,70,229,.2);animation:pd 2s infinite;}
@keyframes pd{0%,100%{box-shadow:0 0 0 4px rgba(79,70,229,.2);}50%{box-shadow:0 0 0 8px rgba(79,70,229,.07);}}
.tl-label{font-weight:600;font-size:.88rem;color:#1e293b;}
.tl-label.muted{color:#94a3b8;font-weight:400;}
#map{height:440px;}
.admin-select{border-radius:8px;font-size:.88rem;}
.admin-btn{border-radius:8px;font-weight:600;}
.status-badge{display:inline-flex;align-items:center;gap:.35rem;padding:.35rem .9rem;border-radius:50px;font-size:.82rem;font-weight:600;}
.status-badge.created{background:#ede9fe;color:#5b21b6;}
.status-badge.picked_up{background:#dbeafe;color:#1d4ed8;}
.status-badge.transporting{background:#fef3c7;color:#92400e;}
.status-badge.delivering{background:#fed7aa;color:#9a3412;}
.status-badge.delivered{background:#d1fae5;color:#065f46;}
</style>

<div class="row mb-3">
    <div class="col">
        <a href="{{ route('admin.manage.orders') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại quản lý đơn hàng
        </a>
        <h3 class="mt-2 mb-0">
            <i class="bi bi-truck me-1 text-primary"></i>
            Theo dõi vận chuyển – Đơn #{{ $order->id }}
            <small class="text-muted fs-6">({{ $order->user->name ?? 'N/A' }})</small>
        </h3>
    </div>
</div>

<div class="row g-3">

    {{-- LEFT --}}
    <div class="col-lg-4">

        <div class="track-card">
            <div class="track-card-header"><i class="bi bi-info-circle text-primary"></i> Thông tin vận đơn</div>
            <div class="track-card-body">
                <div class="info-row"><span class="info-label">Mã vận đơn</span><span class="info-value" style="font-family:monospace">{{ $shipment->tracking_code }}</span></div>
                <div class="info-row"><span class="info-label">Nhà vận chuyển</span><span class="info-value"><span class="badge bg-secondary">{{ strtoupper($shipment->provider) }}</span></span></div>
                <div class="info-row">
                    <span class="info-label">Trạng thái hiện tại</span>
                    <span id="status-badge" class="status-badge {{ $shipment->status }}">
                        <i class="bi bi-circle-fill" style="font-size:.45rem"></i>
                        <span id="status-text">{{ $shipment->statusLabel() }}</span>
                    </span>
                </div>
                @if($order->userAddress)
                <div class="info-row"><span class="info-label">Giao đến</span><span class="info-value text-end" style="max-width:190px;font-size:.8rem">{{ $order->userAddress->street_address }}, {{ $order->userAddress->city }}</span></div>
                @endif
            </div>
        </div>

        {{-- Timeline --}}
        <div class="track-card">
            <div class="track-card-header"><i class="bi bi-list-check text-success"></i> Tiến trình</div>
            <div class="track-card-body">
                @php
                    $statuses = \App\Models\OrderShipment::STATUSES;
                    $labels   = \App\Models\OrderShipment::labels();
                    $currentIndex = array_search($shipment->status, $statuses);
                    $icons = ['created'=>'bi-box','picked_up'=>'bi-bag-check','transporting'=>'bi-truck','delivering'=>'bi-house-door','delivered'=>'bi-check2-circle'];
                @endphp
                <ul class="timeline" id="timeline">
                    @foreach($statuses as $i => $sv)
                    <li class="timeline-item" data-status="{{ $sv }}">
                        <div class="timeline-dot {{ $i < $currentIndex ? 'done' : ($i === $currentIndex ? 'active' : '') }}">
                            @if($i < $currentIndex)<i class="bi bi-check" style="font-size:.6rem"></i>
                            @elseif($i === $currentIndex)<i class="bi {{ $icons[$sv] ?? 'bi-circle' }}" style="font-size:.6rem"></i>@endif
                        </div>
                        <div class="tl-label {{ $i > $currentIndex ? 'muted' : '' }}">{{ $labels[$sv] ?? $sv }}</div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Admin control --}}
        <div class="track-card">
            <div class="track-card-header"><i class="bi bi-sliders text-warning"></i> Cập nhật trạng thái (Admin)</div>
            <div class="track-card-body">
                <label class="form-label fw-semibold mb-1" style="font-size:.85rem">Chọn trạng thái mới</label>
                <select id="admin-status-select" class="form-select admin-select mb-2">
                    @foreach(\App\Models\OrderShipment::STATUSES as $sv)
                    <option value="{{ $sv }}" {{ $shipment->status === $sv ? 'selected' : '' }}>
                        {{ \App\Models\OrderShipment::labels()[$sv] ?? $sv }}
                    </option>
                    @endforeach
                </select>
                <button id="admin-update-btn" class="btn btn-primary admin-btn w-100">
                    <i class="bi bi-arrow-clockwise me-1"></i> Cập nhật & Di chuyển
                </button>
                <div id="update-msg" class="mt-2" style="display:none"></div>
            </div>
        </div>

    </div>

    {{-- RIGHT: MAP --}}
    <div class="col-lg-8">
        <div class="track-card">
            <div class="track-card-header">
                <i class="bi bi-map text-primary"></i> Bản đồ theo dõi
                <span class="ms-auto badge bg-light text-secondary" style="font-size:.75rem">
                    <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#10b981;margin-right:3px;animation:pd 1.4s infinite"></span>Live
                </span>
            </div>
            <div id="map"></div>
        </div>
        <div class="track-card">
            <div class="track-card-body py-2">
                <div class="d-flex flex-wrap gap-3" style="font-size:.8rem">
                    <span><span style="display:inline-block;width:11px;height:11px;border-radius:50%;background:#3b82f6;margin-right:3px"></span>Lấy hàng</span>
                    <span><span style="display:inline-block;width:11px;height:11px;border-radius:50%;background:#10b981;margin-right:3px"></span>Giao hàng</span>
                    <span><span style="display:inline-block;width:11px;height:11px;border-radius:50%;background:#f59e0b;margin-right:3px"></span>Shipper</span>
                    <span><span style="display:inline-block;width:28px;height:3px;background:#4f46e5;margin-right:3px;vertical-align:middle"></span>Tuyến đường</span>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let trackingData = @json($tracking);
    const statusUrl  = '{{ route('admin.order.shipment.status', $order->id) }}';
    const csrfToken  = '{{ csrf_token() }}';
    const allStatuses = @json(\App\Models\OrderShipment::STATUSES);

    const map = L.map('map');
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {attribution:'© OpenStreetMap'}).addTo(map);

    function makeIcon(color, emoji) {
        return L.divIcon({
            className:'',
            html:`<div style="width:36px;height:36px;background:${color};border-radius:50%;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;font-size:16px">${emoji}</div>`,
            iconSize:[36,36],iconAnchor:[18,18],popupAnchor:[0,-20],
        });
    }

    L.marker([trackingData.pickup.lat, trackingData.pickup.lng], {icon: makeIcon('#3b82f6','🏪')})
        .addTo(map).bindPopup('<b>Điểm lấy hàng</b>');
    L.marker([trackingData.delivery.lat, trackingData.delivery.lng], {icon: makeIcon('#10b981','🏠')})
        .addTo(map).bindPopup('<b>Điểm giao hàng</b>');
    let shipperM = L.marker([trackingData.shipper.lat, trackingData.shipper.lng], {icon: makeIcon('#f59e0b','🛵')})
        .addTo(map).bindPopup('<b>Shipper</b>');

    let poly = null;
    function drawRoute(pts) {
        if (poly) map.removeLayer(poly);
        if (!pts||!pts.length) return;
        poly = L.polyline(pts.map(p=>[p.lat,p.lng]),{color:'#4f46e5',weight:4,opacity:.6,dashArray:'8,4'}).addTo(map);
    }
    drawRoute(trackingData.route);

    function fitAll() {
        map.fitBounds(L.latLngBounds([
            [trackingData.pickup.lat,trackingData.pickup.lng],
            [trackingData.delivery.lat,trackingData.delivery.lng],
            [trackingData.shipper.lat,trackingData.shipper.lng],
        ]).pad(.3));
    }
    fitAll();

    function animateMarker(m, toLat, toLng) {
        const from = m.getLatLng(); let step=0;
        const t = setInterval(()=>{step++;const r=step/30;m.setLatLng([from.lat+(toLat-from.lat)*r,from.lng+(toLng-from.lng)*r]);if(step>=30)clearInterval(t);},20);
    }

    function updateTimeline(status) {
        const idx = allStatuses.indexOf(status);
        document.querySelectorAll('.timeline-item').forEach((li,i)=>{
            const dot=li.querySelector('.timeline-dot');
            const lbl=li.querySelector('.tl-label');
            dot.className='timeline-dot';
            if(i<idx){dot.className+=' done';dot.innerHTML='<i class="bi bi-check" style="font-size:.6rem"></i>';}
            else if(i===idx){dot.className+=' active';dot.innerHTML='';}
            else{dot.innerHTML='';}
            if(lbl)lbl.classList.toggle('muted',i>idx);
        });
    }

    // Admin update button
    const btn = document.getElementById('admin-update-btn');
    const msg = document.getElementById('update-msg');
    btn.addEventListener('click', function() {
        const status = document.getElementById('admin-status-select').value;
        const orig = this.innerHTML;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang cập nhật...';
        this.disabled = true;
        fetch(statusUrl, {
            method: 'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken},
            body: JSON.stringify({status}),
        })
        .then(r=>r.json())
        .then(data=>{
            trackingData=data;
            // badge
            const badge=document.getElementById('status-badge');
            badge.className='status-badge '+data.status;
            document.getElementById('status-text').textContent=data.status_label;
            // timeline
            updateTimeline(data.status);
            // marker
            animateMarker(shipperM, data.shipper.lat, data.shipper.lng);
            if(data.route&&data.route.length) drawRoute(data.route);
            setTimeout(fitAll,700);
            msg.style.display='block';
            msg.innerHTML='<div class="alert alert-success py-2 mb-0" style="font-size:.82rem"><i class="bi bi-check-circle me-1"></i>Cập nhật thành công!</div>';
            setTimeout(()=>msg.style.display='none',3000);
        })
        .catch(()=>{
            msg.style.display='block';
            msg.innerHTML='<div class="alert alert-danger py-2 mb-0" style="font-size:.82rem">Có lỗi xảy ra.</div>';
        })
        .finally(()=>{this.innerHTML=orig;this.disabled=false;});
    });
});
</script>

@endsection
