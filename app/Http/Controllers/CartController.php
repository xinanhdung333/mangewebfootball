<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CartController extends Controller
{
    // Show cart stored in session
    public function index(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        return view('cart.index', ['cart' => $cart]);
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

    public function checkout(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        // For simplicity, clear cart and redirect to order detail
        $request->session()->forget('cart');
        return redirect()->route('order.detail')->with('success', 'Thanh toán thành công (mô phỏng)');
    }

    public function checkoutMultiple(Request $request)
    {
        return $this->checkout($request);
    }
}
