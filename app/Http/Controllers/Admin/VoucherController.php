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
            'discount_type' => 'required|in:fixed,percentage,free_shipping',
            'fixed_discount_amount' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:1|max:100',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'min_order_amount' => 'required|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean',
            'first_order_only' => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active') ? 1 : 0;
        $data['first_order_only'] = $request->has('first_order_only') ? 1 : 0;
        if ($data['discount_type'] === 'fixed') $request->validate(['fixed_discount_amount' => 'required|numeric|min:0']);
        if ($data['discount_type'] === 'percentage') $request->validate(['discount_percent' => 'required|numeric|min:1|max:100']);
        $data['discount_amount'] = match ($data['discount_type']) {
            'percentage' => $request->input('discount_percent'),
            'fixed' => $request->input('fixed_discount_amount'),
            default => 0,
        };
        if ($data['discount_type'] !== 'percentage') $data['max_discount_amount'] = null;

        Voucher::create($data);

        return back()->with('success', 'Thêm Voucher thành công!');
    }

    public function update(Request $request, Voucher $voucher)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:vouchers,code,'.$voucher->id,
            'discount_type' => 'required|in:fixed,percentage,free_shipping',
            'fixed_discount_amount' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:1|max:100',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'min_order_amount' => 'required|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean',
            'first_order_only' => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active') ? 1 : 0;
        $data['first_order_only'] = $request->has('first_order_only') ? 1 : 0;
        if ($data['discount_type'] === 'fixed') $request->validate(['fixed_discount_amount' => 'required|numeric|min:0']);
        if ($data['discount_type'] === 'percentage') $request->validate(['discount_percent' => 'required|numeric|min:1|max:100']);
        $data['discount_amount'] = match ($data['discount_type']) {
            'percentage' => $request->input('discount_percent'),
            'fixed' => $request->input('fixed_discount_amount'),
            default => 0,
        };
        if ($data['discount_type'] !== 'percentage') $data['max_discount_amount'] = null;

        $voucher->update($data);

        return back()->with('success', 'Cập nhật Voucher thành công!');
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();
        return back()->with('success', 'Đã xoá Voucher!');
    }
}
