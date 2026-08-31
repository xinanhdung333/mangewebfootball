<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShippingMethodController extends Controller
{
    public function index()
    {
        $shippingMethods = ShippingMethod::query()
            ->orderBy('id')
            ->get();

        if ($shippingMethods->isEmpty()) {
            $defaults = [
                [
                    'name' => 'Standard',
                    'code' => 'standard',
                    'description' => '2-3 ngày',
                    'extra_fee' => 0,
                    'is_active' => true,
                ],
                [
                    'name' => 'Fast',
                    'code' => 'fast',
                    'description' => '1-2 ngày',
                    'extra_fee' => 20000,
                    'is_active' => false,
                ],
                [
                    'name' => 'Express',
                    'code' => 'express',
                    'description' => 'Trong ngày',
                    'extra_fee' => 50000,
                    'is_active' => false,
                ],
            ];

            foreach ($defaults as $default) {
                ShippingMethod::firstOrCreate(
                    ['code' => $default['code']],
                    $default
                );
            }

            $shippingMethods = ShippingMethod::query()
                ->orderBy('id')
                ->get();
        }

        return view('admin.settings.shipping-methods', compact('shippingMethods'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:50', 'unique:shipping_methods,code'],
            'description' => ['nullable', 'string', 'max:255'],
            'extra_fee' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['code'] = $this->resolveCode($data['code'] ?? $data['name'], null);
        $data['extra_fee'] = (float) ($data['extra_fee'] ?? 0);
        $data['is_active'] = $request->boolean('is_active', true);

        ShippingMethod::create($data);

        return back()->with('success', 'Thêm phương thức vận chuyển thành công.');
    }

    public function update(Request $request, ShippingMethod $shippingMethod)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:50', 'unique:shipping_methods,code,' . $shippingMethod->id],
            'description' => ['nullable', 'string', 'max:255'],
            'extra_fee' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['code'] = $this->resolveCode($data['code'] ?? $data['name'], $shippingMethod->id);
        $data['extra_fee'] = (float) ($data['extra_fee'] ?? 0);
        $data['is_active'] = $request->boolean('is_active', true);

        $shippingMethod->fill($data);
        $shippingMethod->save();

        return back()->with('success', 'Cập nhật phương thức vận chuyển thành công.');
    }

    public function destroy(ShippingMethod $shippingMethod)
    {
        $shippingMethod->delete();

        return back()->with('success', 'Xóa phương thức vận chuyển thành công.');
    }

    protected function resolveCode(?string $code, ?int $ignoreId): string
    {
        $normalized = trim((string) ($code ?? ''));
        if ($normalized === '') {
            $normalized = 'shipping-method';
        }

        $slug = Str::slug($normalized);
        $base = $slug !== '' ? $slug : 'shipping-method';
        $value = $base;
        $counter = 1;

        while (ShippingMethod::query()
            ->where('code', $value)
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $value = $base . '-' . $counter;
            $counter++;
        }

        return $value;
    }
}
