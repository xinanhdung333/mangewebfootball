<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\PriceRule;
use App\Models\ServiceDiscount;
use App\Models\Field;
use App\Models\Service;

class SettingController extends Controller
{
    public function index(){
        $settings = [
            'shop_address' => \App\Models\Setting::get('shop_address'),
            'shop_lat' => \App\Models\Setting::get('shop_lat', '21.0285'),
            'shop_lng' => \App\Models\Setting::get('shop_lng', '105.8542'),
            'shipping_fee_per_km' => \App\Models\Setting::get('shipping_fee_per_km', 15000),
        ];
        return view('admin.settings.setting', compact('settings'));
    }

    public function store(Request $request){
        $request->validate([
            'shop_address' => 'required|string',
            'shop_lat' => 'required|numeric',
            'shop_lng' => 'required|numeric',
            'shipping_fee_per_km' => 'required|numeric|min:0',
        ]);

        \App\Models\Setting::set('shop_address', $request->shop_address);
        \App\Models\Setting::set('shipping_fee_per_km', $request->shipping_fee_per_km);
        
        $lat = $request->shop_lat;
        $lng = $request->shop_lng;
        $geocodeMsg = '';

        // Geocode shop address only if it's different or if we want to auto-update
        // To be safe, we always try to geocode and if successful, overwrite.
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(6)
                ->withHeaders(['User-Agent' => 'FootballHub/1.0'])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $request->shop_address, 
                    'format' => 'json', 
                    'limit' => 1,
                ]);

            $results = $response->json();
            if (!empty($results)) {
                $lat = $results[0]['lat'];
                $lng = $results[0]['lon'];
            } else {
                $geocodeMsg = ' (Hệ thống không tự tìm được toạ độ cho địa chỉ này, đang sử dụng toạ độ bạn đã nhập thủ công)';
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Geocode shop address failed: ' . $e->getMessage());
            $geocodeMsg = ' (Lỗi gọi API bản đồ, đang sử dụng toạ độ nhập thủ công)';
        }

        \App\Models\Setting::set('shop_lat', $lat);
        \App\Models\Setting::set('shop_lng', $lng);

        return back()->with('success', 'Cập nhật cài đặt thành công!' . $geocodeMsg);
    }
    // ================== VIEW ==================
    public function pricing()
    {
        $rules = PriceRule::with('field')->get();
        $fields = Field::all();

        // 🔥 thêm service
        $services = Service::all();
        $serviceDiscounts = ServiceDiscount::with('service')->get();

        return view('admin.settings.pricing', compact(
            'rules',
            'fields',
            'services',
            'serviceDiscounts'
        ));
    }

    // ================== FIELD PRICING ==================
    public function storePricing(Request $request)
    {
        $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
            'multiplier' => 'required|numeric|min:0.1',
        ]);

        PriceRule::create([
            'field_id'   => $request->field_id ?: null,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
            'multiplier' => $request->multiplier,
        ]);

        return back()->with('success', 'Thêm giá sân thành công');
    }

    public function deletePricing($id)
    {
        PriceRule::findOrFail($id)->delete();
        return back()->with('success', 'Đã xoá giá sân');
    }

    // ================== SERVICE DISCOUNT ==================
    public function storeServiceDiscount(Request $request)
    {
        $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
            'multiplier' => 'required|numeric|min:0.1',
        ]);

        ServiceDiscount::create([
            'service_id' => $request->service_id ?: null,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
            'multiplier' => $request->multiplier,
            'note' => $request->note,
            'is_active'  => 1
        ]);

        return back()->with('success', 'Thêm giảm giá dịch vụ thành công');
    }

    public function deleteServiceDiscount($id)
    {
        ServiceDiscount::findOrFail($id)->delete();
        return back()->with('success', 'Đã xoá giảm giá dịch vụ');
    }
}