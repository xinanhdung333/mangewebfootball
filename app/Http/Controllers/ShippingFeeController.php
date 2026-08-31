<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use App\Services\OpenRouteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingFeeController extends Controller
{
    public function __construct(private OpenRouteService $ors) {}

    /**
     * Tính phí ship từ cửa hàng đến địa chỉ người dùng.
     * POST /api/shipping-fee
     * Body: { address_id: 5, order_total: 150000 }
     *   hoặc { lat: 21.03, lng: 105.84, order_total: 150000 }
     */
    public function calculate(Request $request): JsonResponse
    {
        $request->validate([
            'address_id'  => 'nullable|integer|exists:user_addresses,id',
            'lat'         => 'nullable|numeric|between:-90,90',
            'lng'         => 'nullable|numeric|between:-180,180',
            'order_total' => 'nullable|integer|min:0',
        ]);

        $orderTotal = (int) ($request->order_total ?? 0);

        // Lấy tọa độ từ địa chỉ hoặc từ request
        if ($request->filled('address_id')) {
            $address = UserAddress::where('id', $request->address_id)
                ->where('user_id', auth()->id())
                ->first();

            if (!$address) {
                return response()->json(['error' => 'Địa chỉ không hợp lệ'], 422);
            }

            if (!$address->lat || !$address->lng) {
                // Địa chỉ chưa có tọa độ → fallback phí cố định
                return response()->json([
                    'fee'         => config('services.ors.base_fee', 15000),
                    'distance_km' => null,
                    'is_free'     => false,
                    'reason'      => 'Địa chỉ chưa có tọa độ GPS, áp phí cố định',
                ]);
            }

            $lat = (float) $address->lat;
            $lng = (float) $address->lng;
        } elseif ($request->filled('lat') && $request->filled('lng')) {
            $lat = (float) $request->lat;
            $lng = (float) $request->lng;
        } else {
            return response()->json(['error' => 'Cần cung cấp address_id hoặc lat/lng'], 422);
        }

        $result = $this->ors->calculateShippingFee($lat, $lng, $orderTotal);

        return response()->json($result);
    }

    /**
     * Geocode địa chỉ text → tọa độ rồi lưu vào user_addresses.
     * POST /api/geocode-address
     * Body: { address_id: 5 }  (hoặc address_text để geocode từ chuỗi)
     * Sử dụng Nominatim (OpenStreetMap) - miễn phí.
     */
    public function geocodeAddress(Request $request): JsonResponse
    {
        $request->validate([
            'address_id' => 'required|integer|exists:user_addresses,id',
        ]);

        $address = UserAddress::where('id', $request->address_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $fullAddress = trim(implode(', ', array_filter([
            $address->street_address,
            $address->ward,
            $address->district,
            $address->city,
            'Việt Nam',
        ])));

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(6)
                ->withHeaders(['User-Agent' => 'FootballHub/1.0'])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q'              => $fullAddress,
                    'format'         => 'json',
                    'limit'          => 1,
                    'addressdetails' => 0,
                ]);

            $results = $response->json();

            if (empty($results)) {
                return response()->json(['error' => 'Không tìm thấy tọa độ cho địa chỉ này'], 422);
            }

            $lat = (float) $results[0]['lat'];
            $lng = (float) $results[0]['lon'];

            // Lưu tọa độ vào DB
            $address->update(['lat' => $lat, 'lng' => $lng]);

            return response()->json(['lat' => $lat, 'lng' => $lng, 'display_name' => $results[0]['display_name'] ?? '']);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Lỗi geocode: ' . $e->getMessage()], 500);
        }
    }
}
