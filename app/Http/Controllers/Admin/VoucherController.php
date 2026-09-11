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
        $data = $this->validatedVoucherData($request, 'unique:vouchers,code');

        Voucher::create($data);

        return back()->with('success', 'Thêm Voucher thành công!');
    }

    public function update(Request $request, Voucher $voucher)
    {
        $data = $this->validatedVoucherData($request, 'unique:vouchers,code,' . $voucher->id);

        $voucher->update($data);

        return back()->with('success', 'Cập nhật Voucher thành công!');
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();
        return back()->with('success', 'Đã xoá Voucher!');
    }

    private function validatedVoucherData(Request $request, string $uniqueRule): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', $uniqueRule],
            'discount_type' => 'required|in:fixed,percentage,free_shipping',
            'discount_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'min_order_amount' => 'required|numeric|min:0',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        if ($data['discount_type'] === 'free_shipping') {
            $data['discount_amount'] = 0;
            $data['max_discount_amount'] = null;
        } elseif ($data['discount_type'] === 'percentage') {
            $request->validate([
                'discount_amount' => 'required|numeric|min:1|max:100',
            ]);
        } else {
            $request->validate([
                'discount_amount' => 'required|numeric|min:1',
            ]);
            $data['max_discount_amount'] = null;
        }

        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        return $data;
    }
}
