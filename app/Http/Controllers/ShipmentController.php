<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderShipment;
use App\Services\ShippingService;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function show(Order $order, ShippingService $shipping)
    {
        abort_unless($order->user_id === auth()->id(), 403);

        $shipment = $shipping->ensureShipmentForOrder($order);

        return view('user.order-tracking', [
            'order' => $order->loadMissing(['items.service', 'userAddress']),
            'shipment' => $shipment,
            'tracking' => $shipping->trackingPayload($shipment),
        ]);
    }

    public function data(Order $order, ShippingService $shipping)
    {
        abort_unless($order->user_id === auth()->id(), 403);

        $shipment = $shipping->ensureShipmentForOrder($order);

        return response()->json($shipping->trackingPayload($shipment));
    }

    public function updateStatus(Request $request, Order $order, ShippingService $shipping)
    {
        abort_unless($order->user_id === auth()->id(), 403);

        $data = $request->validate([
            'status' => 'required|in:' . implode(',', OrderShipment::STATUSES),
        ]);

        $shipment = $shipping->ensureShipmentForOrder($order);
        $shipment = $shipping->updateStatus($shipment, $data['status']);

        return response()->json($shipping->trackingPayload($shipment));
    }
}
