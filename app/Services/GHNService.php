<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\ShippingStatus;
use App\Models\OrderShippingStatusHistory;

class GHNService
{
    public const DEMO_STATUS_LABELS = [
        'ready_to_pick' => 'Chờ lấy hàng',
        'picking' => 'Đang lấy hàng',
        'picked_up' => 'Đã lấy hàng',
        'storing' => 'Đang lưu kho',
        'transporting' => 'Đang trung chuyển',
        'sorting' => 'Đang phân loại',
        'delivering' => 'Đang giao hàng',
        'delivered' => 'Đã giao thành công',
        'returning' => 'Đang hoàn hàng',
        'returned' => 'Đã hoàn hàng',
        'cancelled' => 'Đã hủy',
    ];

    public const DEMO_STATUSES = [
        'ready_to_pick', 'picking', 'picked_up', 'storing', 'transporting',
        'sorting', 'delivering', 'delivered', 'returning', 'returned', 'cancelled',
    ];

    public static function demoStatusLabels(): array
    {
        return ShippingStatus::query()->orderBy('sort_order')->pluck('label', 'code')->all()
            ?: self::DEMO_STATUS_LABELS;
    }

    public static function demoStatuses(): array
    {
        return array_keys(self::demoStatusLabels());
    }

    public static function shipmentStatusForDemo(string $status): string
    {
        $stored = ShippingStatus::where('code', $status)->value('shipment_status');
        if ($stored) {
            return $stored;
        }

        return match ($status) {
            'ready_to_pick', 'picking' => \App\Models\OrderShipment::STATUS_CREATED,
            'picked_up' => \App\Models\OrderShipment::STATUS_PICKED_UP,
            'storing', 'transporting', 'sorting' => \App\Models\OrderShipment::STATUS_TRANSPORTING,
            'delivering', 'returning' => \App\Models\OrderShipment::STATUS_DELIVERING,
            'delivered', 'returned', 'cancelled' => \App\Models\OrderShipment::STATUS_DELIVERED,
            default => \App\Models\OrderShipment::STATUS_CREATED,
        };
    }

    private function client()
    {
        return Http::baseUrl(rtrim((string) config('services.ghn.endpoint'), '/'))
            ->timeout(15)
            ->acceptJson()
            ->withHeaders([
                'Token' => (string) config('services.ghn.token'),
                'ShopId' => (string) config('services.ghn.shop_id'),
            ]);
    }

    public function provinces(): array
    {
        return $this->masterData('/master-data/province');
    }

    public function districts(int $provinceId): array
    {
        return $this->masterData('/master-data/district', ['province_id' => $provinceId]);
    }

    public function wards(int $districtId): array
    {
        return $this->masterData('/master-data/ward', ['district_id' => $districtId]);
    }

    private function masterData(string $path, array $query = []): array
    {
        $response = $this->client()->get($path, $query);
        if ($response->failed()) {
            throw new \RuntimeException('Không tải được dữ liệu địa chỉ GHN.');
        }

        return (array) data_get($response->json(), 'data', []);
    }

    public function createDemoOrder(Order $order): string
    {
        if (config('services.ghn.mode', 'demo') !== 'demo') {
            throw new \RuntimeException('GHN chỉ được phép chạy chế độ DEMO trong môi trường này.');
        }

        $order->loadMissing(['user', 'items.service']);
        $phone = $this->recipientPhone($order);
        $payload = $this->payload($order, $phone);
        $response = $this->client()->post('/v2/shipping-order/create', $payload);

        if ($this->isInvalidAddressResponse($response)) {
            $payload['to_ward_code'] = '11008';
            $payload['to_district_id'] = 1482;
            $response = $this->client()->post('/v2/shipping-order/create', $payload);
        }

        if ($response->failed() || !data_get($response->json(), 'data.order_code')) {
            Log::warning('GHN demo order creation failed', [
                'order_id' => $order->id,
                'response' => $response->json(),
            ]);
            throw new \RuntimeException((string) data_get($response->json(), 'message', 'GHN không tạo được đơn demo.'));
        }

        $code = (string) data_get($response->json(), 'data.order_code');
        $order->update([
            'ghn_code' => $code,
            'ghn_status' => 'ready_to_pick',
        ]);
        self::recordStatus($order, 'ready_to_pick');

        return $code;
    }

    public function fakeStatus(Order $order): string
    {
        $statuses = self::demoStatuses();
        $currentIndex = array_search($order->ghn_status, $statuses, true);
        $next = $statuses[min(($currentIndex === false ? -1 : $currentIndex) + 1, count($statuses) - 1)];

        $order->update(['ghn_status' => $next]);
        self::recordStatus($order, $next);

        return $next;
    }

    public static function recordStatus(Order $order, string $status): void
    {
        $statusId = ShippingStatus::where('code', $status)->value('id');
        if ($statusId) {
            OrderShippingStatusHistory::create([
                'order_id' => $order->id,
                'shipping_status_id' => $statusId,
                'updated_by' => auth()->id(),
            ]);
        }
    }

    private function payload(Order $order, string $phone): array
    {
        $provinceId = (int) ($order->to_province_id ?: 201);
        $districtId = (int) ($order->to_district_id ?: 1482);
        $wardCode = (string) ($order->to_ward_code ?: '12011');

        return [
            'payment_type_id' => 2,
            'required_note' => 'KHONGCHOXEMHANG',
            'client_order_code' => 'DEMO-' . $order->id . '-' . now()->format('YmdHis'),
            'from_name' => config('services.ghn.from_name', 'SportsHub'),
            'from_phone' => config('services.ghn.from_phone', '0900000000'),
            'from_address' => config('services.ghn.from_address', 'SportsHub'),
            'from_ward_code' => (string) config('services.ghn.from_ward_code', '12011'),
            'from_district_id' => (int) config('services.ghn.from_district_id', 1482),
            'from_province_id' => (int) config('services.ghn.from_province_id', 201),
            'to_name' => $order->user->name,
            'to_phone' => $phone,
            'to_address' => $order->detail_address ?: 'Phuc Ly, Ha Noi',
            'to_ward_code' => $wardCode,
            'to_district_id' => $districtId,
            'to_province_id' => $provinceId,
            'cod_amount' => 0,
            'content' => 'SportsHub order #' . $order->id,
            'weight' => 1000,
            'length' => 20,
            'width' => 15,
            'height' => 10,
            'service_type_id' => 2,
            'items' => $order->items->map(fn ($item) => [
                'name' => mb_substr((string) ($item->service->name ?? 'San pham'), 0, 80),
                'quantity' => (int) $item->quantity,
                'price' => (int) $item->price,
            ])->values()->all(),
        ];
    }

    private function recipientPhone(Order $order): string
    {
        $phone = preg_replace('/\D+/', '', (string) ($order->user->phone ?? ''));
        if (strlen($phone) !== 10 || !preg_match('/^(03|05|07|08|09)/', $phone)) {
            throw new \RuntimeException('Số điện thoại người nhận phải đủ 10 số và bắt đầu bằng 03, 05, 07, 08 hoặc 09.');
        }
        if ($phone === (string) config('services.ghn.from_phone')) {
            throw new \RuntimeException('Số điện thoại người nhận không được trùng số cửa hàng.');
        }
        return $phone;
    }

    private function isInvalidAddressResponse(Response $response): bool
    {
        $body = $response->json();
        return !$response->successful()
            || !data_get($body, 'data.order_code')
            || (data_get($body, 'code') !== null && (int) data_get($body, 'code') !== 200);
    }
}
