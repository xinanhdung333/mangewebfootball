@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-truck me-2"></i>Đơn giao hàng</h2>
            <div class="text-muted">Xin chào, {{ auth()->user()->name }}</div>
        </div>
        <span class="badge bg-dark">Shipper</span>
    </div>

    <div id="shipper-message"></div>
    <div class="table-responsive bg-white rounded shadow-sm">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Đơn</th><th>Khách nhận</th><th>Địa chỉ</th><th>Mã vận đơn</th><th>Trạng thái</th><th>Cập nhật</th>
                </tr>
            </thead>
            <tbody>
            @forelse($orders as $order)
                @php($shipment = $order->shipment)
                @php($isGhn = (bool) $order->ghn_code)
                <tr data-order="{{ $order->id }}">
                    <td>#{{ $order->id }}</td>
                    <td>{{ $order->user->name ?? 'N/A' }}<br><small>{{ $order->user->phone ?? '' }}</small></td>
                    <td>{{ $order->detail_address ?: ($order->userAddress->address ?? 'Chưa có địa chỉ') }}</td>
                    <td><strong>{{ $order->ghn_code ?: $shipment->tracking_code }}</strong></td>
                    <td class="status-label">
                        @if($isGhn)
                            {{ \App\Services\GHNService::demoStatusLabels()[$order->ghn_status] ?? 'Chờ lấy hàng' }}
                        @else
                            {{ $shipment->statusLabel() }}
                        @endif
                    </td>
                    <td>
                        <select class="form-select form-select-sm status-select" data-order="{{ $order->id }}">
                            @php($statusOptions = $isGhn
                                ? \App\Services\GHNService::demoStatusLabels()
                                : \App\Models\OrderShipment::labels())
                            @foreach($statusOptions as $status => $label)
                                <option value="{{ $status }}" @selected(($isGhn ? ($order->ghn_status ?: 'ready_to_pick') : $shipment->status) === $status)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-4 text-muted">Chưa có đơn giao hàng.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $orders->links() }}</div>
</div>
@push('scripts')
<script>
document.querySelectorAll('.status-select').forEach(select => {
    select.addEventListener('change', async () => {
        const row = select.closest('tr');
        const previous = row.querySelector('.status-label').textContent;
        select.disabled = true;
        try {
            const response = await fetch(`{{ url('/shipper/orders') }}/${select.dataset.order}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({status: select.value})
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Không cập nhật được trạng thái.');
            row.querySelector('.status-label').textContent = result.shipment.status_label;
            document.getElementById('shipper-message').innerHTML = '<div class="alert alert-success">Đã cập nhật trạng thái.</div>';
        } catch (error) {
            row.querySelector('.status-label').textContent = previous;
            document.getElementById('shipper-message').innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
        } finally {
            select.disabled = false;
        }
    });
});
</script>
@endpush
@endsection
