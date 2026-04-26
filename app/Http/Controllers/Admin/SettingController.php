<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PriceRule;
use App\Models\Field;

class SettingController extends Controller
{
    public function pricing()
    {
        $rules = PriceRule::with('field')->get();
        $fields = Field::all();

        return view('admin.settings.pricing', compact('rules', 'fields'));
    }

  public function storePricing(Request $request)
{
    $request->validate([
        'start_time' => 'required|date_format:H:i',
        'end_time'   => 'required|date_format:H:i|after:start_time',
        'multiplier' => 'required|numeric|min:0.1',
    ]);

   PriceRule::create([
        'field_id'   => $request->field_id,
        'start_time' => $request->start_time,
        'end_time'   => $request->end_time,
        'multiplier' => $request->multiplier,
    ]);
    return back()->with('success', 'Thêm thành công');
}

    public function deletePricing($id)
    {
        PriceRule::findOrFail($id)->delete();
        return back()->with('success', 'Đã xoá');
    }
}