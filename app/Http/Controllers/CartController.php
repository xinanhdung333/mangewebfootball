<?php

namespace App\Http\Controllers;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;
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
    try {

        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'error' => 'Chưa đăng nhập'
            ], 401);
        }

        $request->validate([
            'service_id' => 'required|integer'
        ]);

        $userId = auth()->id();

        $cart = Cart::firstOrCreate([
            'user_id' => $userId
        ]);

        $service = Service::findOrFail($request->service_id);

        $item = CartItem::where('cart_id', $cart->id)
            ->where('service_id', $service->id)
            ->first();

        if ($item) {
            $item->quantity += 1;
            $item->save();
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'service_id' => $service->id,
                'quantity' => 1,
                'price' => $service->price 
            ]);
        }

        return response()->json(['success' => true]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
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
    $id = $request->input('cart_item_id'); // sửa lại cho khớp JS
    $qty = (int) $request->input('quantity', 1);

    $cart = $request->session()->get('cart', []);

    if (isset($cart[$id])) {
        $cart[$id]['qty'] = max(1, $qty);

        $request->session()->put('cart', $cart);

        // tính lại
        $itemTotal = $cart[$id]['price'] * $cart[$id]['qty'];

        $cartTotal = 0;
        foreach ($cart as $item) {
            $cartTotal += $item['price'] * $item['qty'];
        }

        return response()->json([
            'success' => true,
            'new_quantity' => $cart[$id]['qty'],
            'item_total' => $itemTotal,
            'cart_total' => $cartTotal
        ]);
    }

    return response()->json(['success' => false], 404);
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
private function createOrderFromItems($items, $user)
{
    $total = 0;

    foreach ($items as $item)
    {
        $service = Service::find($item->service_id);

        $total +=
            $service->price * $item->quantity;
    }

    $order = Order::create([
        'user_id' => $user->id,
        'cart_id' => $user->cart->id ?? null,
        'status' => 'pending',
        'total_amount' => $total
    ]);

    foreach ($items as $item)
    {
        $service = Service::find($item->service_id);

        OrderItem::create([
            'order_id' => $order->id,
            'service_id' => $item->service_id,
            'price' => $service->price,
            'quantity' => $item->quantity
        ]);

        $service->decrement(
            'quantity',
            $item->quantity
        );
    }

    return $order;
}
public function checkoutAll(Request $request)
{
    $user = $request->user();

    $cartItems = CartItem::where(
        'cart_id',
        $user->cart->id ?? null
    )->get();
    if ($cartItems->isEmpty()) {
        return redirect()
            ->route('user.cart')
            ->with('error', 'Giỏ hàng trống');
    }

    DB::beginTransaction();

    try {

        $order = $this->createOrderFromItems(
            $cartItems,
            $user
        );
        $createdOrders = [];

foreach ($cartItems as $item){
        $serviceId = $item->service_id;

    $service = Service::find($serviceId);

    $createdOrders[] = [
        'order_id' => $order->id,
        'name' => $service->name,
        'total' => $service->price * $item->quantity
    ];
}

        CartItem::whereIn(
            'id',
            $cartItems->pluck('id')
        )->delete();

        DB::commit();

       return view('user.checkout', [
    'createdOrders' => $createdOrders
]);

  }catch (\Exception $e) {

    DB::rollback();

    dd($e->getMessage());

}
}public function checkoutSelected(Request $request)
{
    $request->validate([
        'selected_items' => 'required'
    ]);

    $user = $request->user();

    $ids = $request->selected_items;

    if (!is_array($ids)) {
        $ids = explode(',', $ids);
    }

    $items = CartItem::whereIn('id', $ids)
        ->whereHas('cart', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->get();

    if ($items->isEmpty()) {
        return back()->with('error', 'Chưa chọn sản phẩm');
    }

    DB::beginTransaction();

    try {
    $order = $this->createOrderFromItems($items, $user);

            // tạo pending mới
            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $order->total_amount,
                'status' => 'pending'
            ]);
     
DB::commit();

        return redirect()->route('user.momo.pay', [
            'order_id' => $order->id
        ]);

    } catch (\Exception $e) {

        DB::rollback();

        return back()->with('error', 'Thanh toán thất bại');

    }
}
public function checkoutBuyNow(Request $request)
{
    $request->validate([
        'service_id' => 'required|exists:services,id',
        'quantity' => 'required|integer|min:1'
    ]);

    $user = $request->user();

    $service = Service::findOrFail($request->service_id);

    $item = new \stdClass();
    $item->service_id = $service->id;
    $item->quantity = $request->quantity;
    $item->price = $service->price;

    DB::beginTransaction();

    try {

        $order = $this->createOrderFromItems(
            collect([$item]),
            $user
        );

        Payment::create([
            'order_id' => $order->id,
            'amount' => $order->total_amount,
            'status' => 'pending'
        ]);

        DB::commit();

        return redirect()->route('user.momo.pay', [
            'order_id' => $order->id
        ]);

    } catch (\Exception $e) {

        DB::rollback();

        return back()->with('error', 'Thanh toán thất bại');

    }
}
public function createPayment(Request $request)
{
    
    return view('momo.redirect', compact('amount', 'orderId', 'orderInfo'));
}
}

