<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderShipment;
use App\Services\ShippingService;
use App\Services\GHNService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class ShipperController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'userAddress', 'shipment'])
            ->where(function (Builder $query) {
                $query->whereHas('shipment')->orWhereNotNull('ghn_code');
            })
            ->latest()
            ->paginate(20);

        return view('shipper.dashboard', compact('orders'));
    }

    public function updateStatus(Request $request, Order $order, ShippingService $shipping)
    {
        abort_unless(
            $order->shipment || $order->ghn_code,
            404,
            'Đơn hàng chưa được phân công giao.'
        );

        $shipment = $order->shipment;
        if ($order->ghn_code) {
            $data = $request->validate([
                'status' => 'required|in:' . implode(',', GHNService::demoStatuses()),
            ]);
            $order->update(['ghn_status' => $data['status']]);
            GHNService::recordStatus($order, $data['status']);
            $shipment = $shipment ?: $shipping->ensureShipmentForOrder($order->fresh());
            $shipmentStatus = GHNService::shipmentStatusForDemo($data['status']);
            $shipment = $shipping->updateStatus($shipment, $shipmentStatus);

            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật trạng thái GHN.',
                'shipment' => [
                    'status' => $shipment->status,
                    'status_label' => GHNService::demoStatusLabels()[$data['status']],
                    'ghn_status' => $data['status'],
                ],
            ]);
        }

        abort_unless($shipment, 404, 'Đơn hàng chưa có vận chuyển.');

        $data = $request->validate([
            'status' => 'required|in:' . implode(',', OrderShipment::STATUSES),
        ]);
        $shipment = $shipping->updateStatus($shipment, $data['status']);

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật trạng thái giao hàng.',
            'shipment' => $shipping->trackingPayload($shipment),
        ]);
    }
}
