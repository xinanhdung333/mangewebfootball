<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderShipment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ShippingService
{
    public function ensureShipmentForOrder(Order $order): OrderShipment
    {
        $order->loadMissing(['user', 'userAddress', 'items.service', 'shipment']);

        if ($order->shipment) {
            return $order->shipment;
        }

        $baseData = $this->demoShipmentData($order);
        $ghnResult = $this->tryCreateGhnOrder($order, $baseData['client_order_code']);

        if ($ghnResult['ok']) {
            $baseData['provider'] = 'ghn_test';
            $baseData['tracking_code'] = $ghnResult['tracking_code'];
            $baseData['provider_response'] = $ghnResult['response'];
        } else {
            $baseData['provider'] = 'demo';
            $baseData['provider_error'] = $ghnResult['error'];
        }

        return OrderShipment::create($baseData);
    }

    public function updateStatus(OrderShipment $shipment, string $status): OrderShipment
    {
        if (! in_array($status, OrderShipment::STATUSES, true)) {
            throw new \InvalidArgumentException('Invalid shipment status.');
        }

        $points = $shipment->route_points ?: [];
        $index = array_search($status, OrderShipment::STATUSES, true);
        $lastIndex = max(count(OrderShipment::STATUSES) - 1, 1);
        $routeIndex = min(count($points) - 1, (int) round(($index / $lastIndex) * max(count($points) - 1, 0)));
        $shipper = $points[$routeIndex] ?? [
            'lat' => $shipment->shipper_lat,
            'lng' => $shipment->shipper_lng,
        ];

        $shipment->update([
            'status' => $status,
            'shipper_lat' => $shipper['lat'],
            'shipper_lng' => $shipper['lng'],
            'last_status_at' => now(),
        ]);

        return $shipment->fresh();
    }

    public function trackingPayload(OrderShipment $shipment): array
    {
        return [
            'id' => $shipment->id,
            'tracking_code' => $shipment->tracking_code,
            'provider' => $shipment->provider,
            'status' => $shipment->status,
            'status_label' => $shipment->statusLabel(),
            'labels' => OrderShipment::labels(),
            'statuses' => OrderShipment::STATUSES,
            'pickup' => [
                'lat' => $shipment->pickup_lat,
                'lng' => $shipment->pickup_lng,
            ],
            'delivery' => [
                'lat' => $shipment->delivery_lat,
                'lng' => $shipment->delivery_lng,
            ],
            'shipper' => [
                'lat' => $shipment->shipper_lat,
                'lng' => $shipment->shipper_lng,
            ],
            'route' => $shipment->route_points ?: [],
        ];
    }

    private function demoShipmentData(Order $order): array
    {
        /** @var OpenRouteService $ors */
        $ors = app(OpenRouteService::class);
        $shop = $ors->shopCoords();

        $pickup = [
            'lat' => $shop['lat'],
            'lng' => $shop['lng'],
        ];

        $delivery = $this->deliveryPointForOrder($order);
        $clientOrderCode = 'ORDER-' . $order->id . '-' . now()->format('YmdHis');

        // Lấy tuyến đường thực từ ORS; nếu lỗi thì dùng đường thẳng
        $routePoints = $ors->getRoutePoints($delivery['lat'], $delivery['lng'])
            ?? $this->buildStraightRoute($pickup, $delivery);

        return [
            'order_id'          => $order->id,
            'provider'          => 'demo',
            'tracking_code'     => 'DEMO' . now()->format('ymd') . str_pad((string) $order->id, 5, '0', STR_PAD_LEFT),
            'client_order_code' => $clientOrderCode,
            'status'            => OrderShipment::STATUS_CREATED,
            'pickup_lat'        => $pickup['lat'],
            'pickup_lng'        => $pickup['lng'],
            'delivery_lat'      => $delivery['lat'],
            'delivery_lng'      => $delivery['lng'],
            'shipper_lat'       => $pickup['lat'],
            'shipper_lng'       => $pickup['lng'],
            'route_points'      => $routePoints,
            'last_status_at'    => now(),
        ];
    }

    private function tryCreateGhnOrder(Order $order, string $clientOrderCode): array
    {
        if (config('services.ghn.mode', 'demo') !== 'ghn') {
            return ['ok' => false, 'error' => 'GHN mode is disabled.'];
        }

        $token = config('services.ghn.token');
        $shopId = config('services.ghn.shop_id');

        if (! $token || ! $shopId) {
            return ['ok' => false, 'error' => 'Missing GHN_TOKEN or GHN_SHOP_ID.'];
        }

        try {
            $response = Http::timeout(8)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Token' => $token,
                    'ShopId' => (string) $shopId,
                ])
                ->post(rtrim(config('services.ghn.endpoint'), '/') . '/v2/shipping-order/create', $this->ghnPayload($order, $clientOrderCode));

            $json = $response->json();
            $trackingCode = data_get($json, 'data.order_code');

            if ($response->successful() && $trackingCode) {
                return [
                    'ok' => true,
                    'tracking_code' => $trackingCode,
                    'response' => $json,
                    'error' => null,
                ];
            }

            return [
                'ok' => false,
                'error' => data_get($json, 'message', 'GHN create order failed.'),
            ];
        } catch (\Throwable $e) {
            Log::warning('GHN test API unavailable, using demo shipment.', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    private function ghnPayload(Order $order, string $clientOrderCode): array
    {
        $address = $order->userAddress;
        $toAddress = $address
            ? trim(implode(', ', array_filter([
                $address->street_address,
                $address->ward,
                $address->district,
                $address->city,
            ])))
            : config('services.ghn_demo.delivery_address', '72 Thanh Thai, Quan 10, Ho Chi Minh');

        return [
            'payment_type_id' => 2,
            'required_note' => 'KHONGCHOXEMHANG',
            'client_order_code' => $clientOrderCode,
            'from_name' => config('services.ghn.from_name', config('app.name')),
            'from_phone' => config('services.ghn.from_phone', '0900000000'),
            'from_address' => config('services.ghn.from_address', '72 Thanh Thai, Quan 10, Ho Chi Minh'),
            'from_ward_name' => config('services.ghn.from_ward_name', 'Phuong 14'),
            'from_district_name' => config('services.ghn.from_district_name', 'Quan 10'),
            'from_province_name' => config('services.ghn.from_province_name', 'Ho Chi Minh'),
            'to_name' => $address->name ?? $order->user->name ?? 'Khach hang',
            'to_phone' => $address->phone ?? $order->user->phone ?? '0900000000',
            'to_address' => $toAddress,
            'to_ward_name' => $address->ward ?? config('services.ghn_demo.delivery_ward', 'Phuong 14'),
            'to_district_name' => $address->district ?? config('services.ghn_demo.delivery_district', 'Quan 10'),
            'to_province_name' => $address->city ?? config('services.ghn_demo.delivery_province', 'Ho Chi Minh'),
            'cod_amount' => 0,
            'content' => 'Order #' . $order->id,
            'length' => 20,
            'width' => 15,
            'height' => 10,
            'weight' => 1000,
            'insurance_value' => (int) min($order->total_amount, 5000000),
            'service_type_id' => 2,
            'items' => $order->items->map(function ($item) {
                return [
                    'name' => Str::limit($item->service->name ?? 'San pham', 80, ''),
                    'quantity' => (int) $item->quantity,
                    'price' => (int) $item->price,
                    'length' => 20,
                    'width' => 15,
                    'height' => 10,
                    'weight' => 1000,
                ];
            })->values()->all(),
        ];
    }

private function geocode(\App\Models\UserAddress $address): ?array
{
    $attempts = [
        trim(implode(', ', array_filter([
            $address->street_address, $address->ward, $address->district, $address->city, 'Việt Nam',
        ]))),
        trim(implode(', ', array_filter([
            $address->ward, $address->district, $address->city, 'Việt Nam',
        ]))),
        trim(implode(', ', array_filter([
            $address->district, $address->city, 'Việt Nam',
        ]))),
    ];

    foreach (array_unique(array_filter($attempts)) as $query) {
        try {
            $response = Http::timeout(6)
                ->withHeaders(['User-Agent' => 'FootballHub/1.0'])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $query, 'format' => 'json', 'limit' => 1,
                ]);

            $results = $response->json();

            if (!empty($results)) {
                return [
                    'lat' => (float) $results[0]['lat'],
                    'lng' => (float) $results[0]['lon'],
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('Geocode attempt failed.', ['query' => $query, 'error' => $e->getMessage()]);
        }
    }

    return null;
}
    private function deliveryPointForOrder(Order $order): array
{
    $address = $order->userAddress;

    if ($address && $address->lat && $address->lng) {
        return [
            'lat' => (float) $address->lat,
            'lng' => (float) $address->lng,
        ];
    }

    if ($address) {
        $geo = $this->geocode($address);

        if ($geo) {
            $address->update(['lat' => $geo['lat'], 'lng' => $geo['lng']]);
            return $geo;
        }

        Log::warning('Không geocode được địa chỉ, dùng tọa độ giả để test.', [
            'order_id' => $order->id,
            'address_id' => $address->id,
        ]);
    }

    // Fallback: tọa độ giả dựa theo ID để test (chỉ khi geocode thất bại hoặc không có address)
    $seed = $order->id % 9;
    $shopLat = (float) \App\Models\Setting::get('shop_lat', config('services.ors.shop_lat', 21.0285));
    $shopLng = (float) \App\Models\Setting::get('shop_lng', config('services.ors.shop_lng', 105.8542));

    return [
        'lat' => $shopLat + (0.010 * (($seed % 3) + 1)),
        'lng' => $shopLng + (0.012 * ((int) floor($seed / 3) + 1)),
    ];
}

    private function buildStraightRoute(array $pickup, array $delivery): array
    {
        $points = [];
        $steps = 24;

        for ($i = 0; $i <= $steps; $i++) {
            $ratio = $i / $steps;
            $curve = sin($ratio * pi()) * 0.003;
            $points[] = [
                'lat' => $pickup['lat'] + (($delivery['lat'] - $pickup['lat']) * $ratio) + $curve,
                'lng' => $pickup['lng'] + (($delivery['lng'] - $pickup['lng']) * $ratio) - ($curve / 2),
            ];
        }

        return $points;
    }
}
