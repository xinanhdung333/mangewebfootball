<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function index()
    {
        $vouchers = Voucher::latest()->get();
        return view('admin.vouchers.index', compact('vouchers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:vouchers,code',
            'discount_amount' => 'required|numeric|min:0',
            'min_order_amount' => 'required|numeric|min:0',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean'
        ]);

        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        Voucher::create($data);

        return back()->with('success', 'Thêm Voucher thành công!');
    }

    public function update(Request $request, Voucher $voucher)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:vouchers,code,'.$voucher->id,
            'discount_amount' => 'required|numeric|min:0',
            'min_order_amount' => 'required|numeric|min:0',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean'
        ]);

        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        $voucher->update($data);

        return back()->with('success', 'Cập nhật Voucher thành công!');
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();
        return back()->with('success', 'Đã xoá Voucher!');
    }
}
