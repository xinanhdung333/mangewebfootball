<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CartController extends Controller
{
    // Show cart stored in session
    public function index(Request $request)
    {
        return redirect()->route('user.cart');
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|integer',
            'name' => 'required|string',
            'price' => 'required|numeric',
            'qty' => 'nullable|integer'
        ]);

        $cart = $request->session()->get('cart', []);
        $id = $data['id'];
        $qty = $data['qty'] ?? 1;

        if (isset($cart[$id])) {
            $cart[$id]['qty'] += $qty;
        } else {
            $cart[$id] = ['id' => $id, 'name' => $data['name'], 'price' => $data['price'], 'qty' => $qty];
        }

        $request->session()->put('cart', $cart);
        return redirect()->back()->with('success', 'Đã thêm vào giỏ hàng');
    }

    public function addAjax(Request $request)
    {
        $data = $request->validate([
            'service_id' => 'required|integer|exists:services,id',
            'quantity' => 'nullable|integer|min:1'
        ]);

        // minimal service details for cart
        $service = \App\Models\Service::find($data['service_id']);
        if (!$service) {
            return response()->json(['error' => 'Dịch vụ không tồn tại'], 404);
        }

        $qty = $data['quantity'] ?? 1;
        $cart = $request->session()->get('cart', []);

        if (isset($cart[$service->id])) {
            $cart[$service->id]['qty'] += $qty;
            $cart[$service->id]['quantity'] = $cart[$service->id]['qty'];
        } else {
            $cart[$service->id] = [
                'id' => $service->id,
                'name' => $service->name,
                'price' => $service->price,
                'qty' => $qty,
                'quantity' => $qty,
            ];
        }

        $request->session()->put('cart', $cart);
        return response()->json(['success' => true, 'message' => 'Đã thêm vào giỏ hàng']);
    }

    public function remove(Request $request)
    {
        $id = $request->input('id');
        $cart = $request->session()->get('cart', []);
        if (isset($cart[$id])) unset($cart[$id]);
        $request->session()->put('cart', $cart);
        return redirect()->back();
    }

    public function updateQuantity(Request $request)
    {
        $id = $request->input('id');
        $qty = (int) $request->input('qty', 1);
        $cart = $request->session()->get('cart', []);
        if (isset($cart[$id])) {
            $cart[$id]['qty'] = max(1, $qty);
            $request->session()->put('cart', $cart);
        }
        return redirect()->back();
    }

    public function updateItem(Request $request)
    {
        return $this->updateQuantity($request);
    }

    public function checkoutPage(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        $total = 0;
        foreach ($cart as $item) {
            $total += ($item['price'] ?? 0) * ($item['qty'] ?? $item['quantity'] ?? 1);
        }
        $createdOrders = [];
        return view('user.checkout', compact('cart', 'total', 'createdOrders'));
    }

    public function checkout(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        $createdOrders = [];
        foreach ($cart as $item) {
            $createdOrders[] = [
                'order_id' => rand(1000, 9999),
                'name' => $item['name'] ?? 'Dịch vụ',
                'total' => ($item['price'] ?? 0) * ($item['qty'] ?? $item['quantity'] ?? 1),
            ];
        }

        $request->session()->forget('cart');
        return view('user.checkout', compact('createdOrders'));
    }

    public function checkoutMultiple(Request $request)
    {
        return $this->checkout($request);
    }
}
