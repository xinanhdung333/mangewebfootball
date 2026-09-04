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
            'about_hero_title' => \App\Models\Setting::get('about_hero_title', 'Về chúng tôi'),
            'about_hero_subtitle' => \App\Models\Setting::get('about_hero_subtitle', 'Sứ mệnh – Tầm nhìn – Giá trị của SportsHub'),
            'about_intro_title' => \App\Models\Setting::get('about_intro_title', 'Về SportsHub'),
            'about_intro_lead' => \App\Models\Setting::get('about_intro_lead', 'SportsHub là nền tảng đặt sân bóng và dịch vụ đi kèm, kết nối cộng đồng yêu bóng đá với các sân chất lượng.'),
            'about_intro_paragraph_1' => \App\Models\Setting::get('about_intro_paragraph_1', 'SportsHub là nền tảng đặt sân bóng và các dịch vụ đi kèm được xây dựng nhằm kết nối cộng đồng yêu bóng đá với những sân bóng chất lượng, uy tín và phù hợp với nhu cầu đa dạng của người chơi.'),
            'about_intro_paragraph_2' => \App\Models\Setting::get('about_intro_paragraph_2', 'Xuất phát từ những khó khăn thực tế của người chơi bóng phong trào như việc tìm sân trống, thiếu thông tin minh bạch về giá cả, chất lượng sân và các dịch vụ liên quan, SportsHub ra đời với mục tiêu giải quyết triệt để những bất cập đó.'),
            'about_history_title' => \App\Models\Setting::get('about_history_title', 'Lịch sử ra đời'),
            'about_history_paragraph_1' => \App\Models\Setting::get('about_history_paragraph_1', 'SportsHub được hình thành từ một dự án nhỏ vào năm 2025, trong bối cảnh nhu cầu đặt sân bóng của người chơi phong trào ngày càng tăng cao nhưng các hình thức đặt sân vẫn chủ yếu mang tính thủ công, thiếu tính đồng bộ và minh bạch.'),
            'about_history_paragraph_2' => \App\Models\Setting::get('about_history_paragraph_2', 'Nhận thấy những hạn chế đó, nhóm phát triển đã từng bước xây dựng SportsHub như một giải pháp ứng dụng công nghệ vào quản lý và đặt sân bóng.'),
            'about_role_title' => \App\Models\Setting::get('about_role_title', 'Vai trò trong cuộc sống'),
            'about_mission_title' => \App\Models\Setting::get('about_mission_title', 'Sứ mệnh'),
            'about_mission_text' => \App\Models\Setting::get('about_mission_text', 'Mang đến một nền tảng đặt sân bóng hiện đại, thân thiện và dễ sử dụng, giúp mọi người tiếp cận sân bóng tốt hơn.'),
            'about_vision_title' => \App\Models\Setting::get('about_vision_title', 'Tầm nhìn'),
            'about_vision_text' => \App\Models\Setting::get('about_vision_text', 'Trở thành nền tảng đặt sân bóng hàng đầu, kết nối cộng đồng và nâng cao trải nghiệm thể thao cộng đồng.'),
        ];
        return view('admin.settings.setting', compact('settings'));
    }

    public function aboutEditor()
    {
        $settings = [
            'about_hero_title' => \App\Models\Setting::get('about_hero_title', 'Về chúng tôi'),
            'about_hero_subtitle' => \App\Models\Setting::get('about_hero_subtitle', 'Sứ mệnh – Tầm nhìn – Giá trị của SportsHub'),
            'about_intro_title' => \App\Models\Setting::get('about_intro_title', 'Về SportsHub'),
            'about_intro_lead' => \App\Models\Setting::get('about_intro_lead', 'SportsHub là nền tảng đặt sân bóng và dịch vụ đi kèm, kết nối cộng đồng yêu bóng đá với các sân chất lượng.'),
            'about_intro_paragraph_1' => \App\Models\Setting::get('about_intro_paragraph_1', 'SportsHub là nền tảng đặt sân bóng và các dịch vụ đi kèm được xây dựng nhằm kết nối cộng đồng yêu bóng đá với những sân bóng chất lượng, uy tín và phù hợp với nhu cầu đa dạng của người chơi.'),
            'about_intro_paragraph_2' => \App\Models\Setting::get('about_intro_paragraph_2', 'Xuất phát từ những khó khăn thực tế của người chơi bóng phong trào như việc tìm sân trống, thiếu thông tin minh bạch về giá cả, chất lượng sân và các dịch vụ liên quan, SportsHub ra đời với mục tiêu giải quyết triệt để những bất cập đó.'),
            'about_history_title' => \App\Models\Setting::get('about_history_title', 'Lịch sử ra đời'),
            'about_history_paragraph_1' => \App\Models\Setting::get('about_history_paragraph_1', 'SportsHub được hình thành từ một dự án nhỏ vào năm 2025, trong bối cảnh nhu cầu đặt sân bóng của người chơi phong trào ngày càng tăng cao nhưng các hình thức đặt sân vẫn chủ yếu mang tính thủ công, thiếu tính đồng bộ và minh bạch.'),
            'about_history_paragraph_2' => \App\Models\Setting::get('about_history_paragraph_2', 'Nhận thấy những hạn chế đó, nhóm phát triển đã từng bước xây dựng SportsHub như một giải pháp ứng dụng công nghệ vào quản lý và đặt sân bóng.'),
            'about_role_title' => \App\Models\Setting::get('about_role_title', 'Vai trò trong cuộc sống'),
            'about_mission_title' => \App\Models\Setting::get('about_mission_title', 'Sứ mệnh'),
            'about_mission_text' => \App\Models\Setting::get('about_mission_text', 'Mang đến một nền tảng đặt sân bóng hiện đại, thân thiện và dễ sử dụng, giúp mọi người tiếp cận sân bóng tốt hơn.'),
            'about_vision_title' => \App\Models\Setting::get('about_vision_title', 'Tầm nhìn'),
            'about_vision_text' => \App\Models\Setting::get('about_vision_text', 'Trở thành nền tảng đặt sân bóng hàng đầu, kết nối cộng đồng và nâng cao trải nghiệm thể thao cộng đồng.'),
        ];

        return view('admin.settings.about', compact('settings'));
    }

    public function storeAbout(Request $request)
    {
        $fields = [
            'about_hero_title',
            'about_hero_subtitle',
            'about_intro_title',
            'about_intro_lead',
            'about_intro_paragraph_1',
            'about_intro_paragraph_2',
            'about_history_title',
            'about_history_paragraph_1',
            'about_history_paragraph_2',
            'about_role_title',
            'about_mission_title',
            'about_mission_text',
            'about_vision_title',
            'about_vision_text',
        ];

        foreach ($fields as $field) {
            \App\Models\Setting::set($field, $request->input($field, ''));
        }

        return redirect()->route('admin.settings.about')->with('success', 'Cập nhật trang giới thiệu thành công!');
    }

    public function store(Request $request){
        $request->validate([
            'shop_address' => 'nullable|string',
            'shop_lat' => 'nullable|numeric',
            'shop_lng' => 'nullable|numeric',
            'shipping_fee_per_km' => 'nullable|numeric|min:0',
        ]);

        $aboutFields = [
            'about_hero_title',
            'about_hero_subtitle',
            'about_intro_title',
            'about_intro_lead',
            'about_intro_paragraph_1',
            'about_intro_paragraph_2',
            'about_history_title',
            'about_history_paragraph_1',
            'about_history_paragraph_2',
            'about_role_title',
            'about_mission_title',
            'about_mission_text',
            'about_vision_title',
            'about_vision_text',
        ];

        foreach ($aboutFields as $field) {
            if ($request->has($field) && $request->input($field) !== null) {
                \App\Models\Setting::set($field, $request->input($field));
            }
        }

        if ($request->filled('shop_address')) {
            \App\Models\Setting::set('shop_address', $request->shop_address);
        }

        if ($request->filled('shipping_fee_per_km')) {
            \App\Models\Setting::set('shipping_fee_per_km', $request->shipping_fee_per_km);
        }

        $lat = $request->input('shop_lat');
        $lng = $request->input('shop_lng');
        $geocodeMsg = '';

        if ($request->filled('shop_address') && $request->filled('shop_lat') && $request->filled('shop_lng')) {
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

            if ($request->filled('shop_address')) {
                \App\Models\Setting::set('shop_lat', $lat);
                \App\Models\Setting::set('shop_lng', $lng);
            }
        }

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
            'is_active'  => true,
        ]);

        return back()->with('success', 'Thêm giá sân thành công');
    }

    public function deletePricing($id)
    {
        PriceRule::findOrFail($id)->delete();
        return back()->with('success', 'Đã xoá giá sân');
    }

    public function togglePricing($id)
    {
        $rule = PriceRule::findOrFail($id);
        $rule->update(['is_active' => !$rule->is_active]);

        return back()->with('success', $rule->is_active
            ? 'Đã áp dụng lại giá sân'
            : 'Đã tạm dừng giá sân');
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

    public function toggleServiceDiscount($id)
    {
        $discount = ServiceDiscount::findOrFail($id);
        $discount->update(['is_active' => !$discount->is_active]);

        return back()->with('success', $discount->is_active
            ? 'Đã áp dụng lại giảm giá dịch vụ'
            : 'Đã tạm dừng giảm giá dịch vụ');
    }
}
