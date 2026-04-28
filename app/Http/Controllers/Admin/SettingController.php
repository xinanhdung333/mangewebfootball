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