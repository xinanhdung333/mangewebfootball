<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OpenRouteService
{
    private string $apiKey;
    private string $endpoint;

    // Tọa độ cửa hàng
    private float $shopLat;
    private float $shopLng;

    // Cấu hình phí ship
    private int $baseFee;      // Phí cố định (VNĐ)
    private int $feePerKm;     // Phí mỗi km (VNĐ)
    private int $freeThreshold; // Miễn phí nếu đơn hàng >= ngưỡng này

    public function __construct()
    {
        $this->apiKey    = config('services.ors.api_key', '');
        $this->endpoint  = config('services.ors.endpoint', 'https://api.heigit.org/openrouteservice/v2/directions/driving-car/json');
        
        $this->shopLat   = (float) \App\Models\Setting::get('shop_lat', config('services.ors.shop_lat', 21.0285));
        $this->shopLng   = (float) \App\Models\Setting::get('shop_lng', config('services.ors.shop_lng', 105.8542));
        
        $this->baseFee       = (int) \App\Models\Setting::get('base_shipping_fee', config('services.ors.base_fee', 15000));
        $this->feePerKm      = (int) \App\Models\Setting::get('shipping_fee_per_km', config('services.ors.fee_per_km', 5000));
        $this->freeThreshold = (int) \App\Models\Setting::get('free_shipping_threshold', config('services.ors.free_threshold', 200000));
    }

    /**
     * Tính khoảng cách (km) từ cửa hàng tới tọa độ đích.
     * Kết quả được cache 24h để tránh gọi API nhiều lần.
     */
    public function distanceKm(float $destLat, float $destLng): ?float
    {
        // Nếu cùng một điểm => 0 km
        if (abs($destLat - $this->shopLat) < 0.0001 && abs($destLng - $this->shopLng) < 0.0001) {
            return 0.0;
        }

        $cacheKey = "ors_dist_{$this->shopLat}_{$this->shopLng}_{$destLat}_{$destLng}";

        return Cache::remember($cacheKey, 86400, function () use ($destLat, $destLng) {
            return $this->fetchDistanceFromApi($destLat, $destLng);
        });
    }

    /**
     * Tính phí giao hàng dựa trên khoảng cách.
     * Trả về mảng ['fee' => int, 'distance_km' => float|null, 'is_free' => bool]
     */
    public function calculateShippingFee(float $destLat, float $destLng, int $orderTotal = 0): array
    {
        // Miễn phí ship nếu đơn hàng đủ lớn
        if ($this->freeThreshold > 0 && $orderTotal >= $this->freeThreshold) {
            return [
                'fee'         => 0,
                'distance_km' => null,
                'is_free'     => true,
                'reason'      => 'Miễn phí ship cho đơn hàng từ ' . number_format($this->freeThreshold, 0, ',', '.') . 'đ',
            ];
        }

        $distKm = $this->distanceKm($destLat, $destLng);

        if ($distKm === null) {
            // Không lấy được khoảng cách - áp phí cố định
            return [
                'fee'         => $this->baseFee,
                'distance_km' => null,
                'is_free'     => false,
                'reason'      => 'Phí cố định (không xác định được khoảng cách)',
            ];
        }

        $fee = $this->baseFee + (int) ceil($distKm * $this->feePerKm);

        return [
            'fee'         => $fee,
            'distance_km' => round($distKm, 2),
            'is_free'     => false,
            'reason'      => number_format(round($distKm, 1), 1, ',', '.') . ' km × ' . number_format($this->feePerKm, 0, ',', '.') . 'đ/km + phí cố định ' . number_format($this->baseFee, 0, ',', '.') . 'đ',
        ];
    }

    /**
     * Lấy tuyến đường thực tế từ ORS để hiển thị trên bản đồ tracking.
     * Trả về mảng các điểm [{lat, lng}] hoặc null nếu lỗi.
     */
    public function getRoutePoints(float $destLat, float $destLng): ?array
    {
        $cacheKey = "ors_route_{$this->shopLat}_{$this->shopLng}_{$destLat}_{$destLng}";

        return Cache::remember($cacheKey, 86400, function () use ($destLat, $destLng) {
            return $this->fetchRoutePointsFromApi($destLat, $destLng);
        });
    }

    /**
     * Lấy tọa độ cửa hàng.
     */
    public function shopCoords(): array
    {
        return ['lat' => $this->shopLat, 'lng' => $this->shopLng];
    }

    // ──────────────────────────────────────────────────────────
    // Private methods
    // ──────────────────────────────────────────────────────────

    private function fetchDistanceFromApi(float $destLat, float $destLng): ?float
    {
        try {
            $response = Http::timeout(8)
                ->withHeaders([
                    'Authorization' => $this->apiKey,
                    'Content-Type'  => 'application/json',
                ])
                ->post($this->endpoint, [
                    'coordinates' => [
                        [$this->shopLng, $this->shopLat],  // ORS dùng [lng, lat]
                        [$destLng, $destLat],
                    ],
                ]);

            if (!$response->successful()) {
                Log::warning('ORS API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();
            $distanceMeters = data_get($data, 'routes.0.summary.distance');

            if ($distanceMeters === null) {
                Log::warning('ORS: không tìm thấy khoảng cách trong response', ['data' => $data]);
                return null;
            }

            return (float) $distanceMeters / 1000.0; // Chuyển sang km
        } catch (\Throwable $e) {
            Log::warning('ORS API exception: ' . $e->getMessage());
            return null;
        }
    }

    private function fetchRoutePointsFromApi(float $destLat, float $destLng): ?array
    {
        try {
            $response = Http::timeout(8)
                ->withHeaders([
                    'Authorization' => $this->apiKey,
                    'Content-Type'  => 'application/json',
                ])
                ->post($this->endpoint, [
                    'coordinates' => [
                        [$this->shopLng, $this->shopLat],
                        [$destLng, $destLat],
                    ],
                ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();

            // ORS trả geometry dạng encoded polyline hoặc GeoJSON
            $geometry = data_get($data, 'routes.0.geometry');
            if (!$geometry) {
                return null;
            }

            // Nếu là string → encoded polyline
            if (is_string($geometry)) {
                return $this->decodePolyline($geometry);
            }

            // Nếu là GeoJSON
            $coords = data_get($geometry, 'coordinates', []);
            return array_map(fn($c) => ['lat' => $c[1], 'lng' => $c[0]], $coords);
        } catch (\Throwable $e) {
            Log::warning('ORS route exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Decode Google/ORS encoded polyline.
     */
    private function decodePolyline(string $encoded): array
    {
        $points = [];
        $index = 0;
        $len = strlen($encoded);
        $lat = 0;
        $lng = 0;

        while ($index < $len) {
            $b = 0;
            $shift = 0;
            $result = 0;
            do {
                $b = ord($encoded[$index++]) - 63;
                $result |= ($b & 0x1f) << $shift;
                $shift += 5;
            } while ($b >= 0x20);
            $dlat = (($result & 1) ? ~($result >> 1) : ($result >> 1));
            $lat += $dlat;

            $shift = 0;
            $result = 0;
            do {
                $b = ord($encoded[$index++]) - 63;
                $result |= ($b & 0x1f) << $shift;
                $shift += 5;
            } while ($b >= 0x20);
            $dlng = (($result & 1) ? ~($result >> 1) : ($result >> 1));
            $lng += $dlng;

            $points[] = [
                'lat' => $lat / 1e5,
                'lng' => $lng / 1e5,
            ];
        }

        return $points;
    }
}
