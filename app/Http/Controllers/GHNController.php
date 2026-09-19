<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\GHNService;
use Illuminate\Http\Request;

class GHNController extends Controller
{
    public function provinces(GHNService $ghn)
    {
        return response()->json($ghn->provinces());
    }

    public function districts(Request $request, GHNService $ghn)
    {
        $data = $request->validate(['province_id' => 'required|integer']);
        return response()->json($ghn->districts((int) $data['province_id']));
    }

    public function wards(Request $request, GHNService $ghn)
    {
        $data = $request->validate(['district_id' => 'required|integer']);
        return response()->json($ghn->wards((int) $data['district_id']));
    }

    public function createDemoOrder(Order $order, GHNService $ghn)
    {
        try {
            $code = $ghn->createDemoOrder($order);
        } catch (\RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return back()->with('success', "Đã tạo GHN DEMO: {$code}");
    }

    public function fakeStatus(Order $order, GHNService $ghn)
    {
        $status = $ghn->fakeStatus($order);
        return back()->with('success', 'Đã cập nhật trạng thái GHN DEMO: ' . (GHNService::demoStatusLabels()[$status] ?? $status));
    }
}
