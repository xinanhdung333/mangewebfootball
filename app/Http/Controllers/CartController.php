<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\ServiceDiscountHelper;
class CartController extends Controller
{
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
            'qty' => 'nullable|integer',
        ]);

        $cart = $request->session()->get('cart', []);
        $id = $data['id'];
        $qty = $data['qty'] ?? 1;

        if (isset($cart[$id])) {
            $cart[$id]['qty'] += $qty;
        } else {
            $cart[$id] = [
                'id' => $id,
                'name' => $data['name'],
                'price' => $data['price'],
                'qty' => $qty,
            ];
        }

        $request->session()->put('cart', $cart);

        return redirect()->back()->with('success', 'Da them vao gio hang');
    }

    public function addAjax(Request $request)
    {
        try {
            if (!auth()->check()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Chua dang nhap',
                ], 401);
            }

            $request->validate([
                'service_id' => 'required|integer',
            ]);

            $cart = Cart::firstOrCreate([
                'user_id' => auth()->id(),
            ]);

            $service = Service::findOrFail($request->service_id);
            if ($service->quantity < 1) {
                return response()->json([
                    'success' => false,
                    'error' => 'Dich vu da het hang',
                ], 422);
            }

            $item = CartItem::where('cart_id', $cart->id)
                ->where('service_id', $service->id)
                ->first();

            if ($item) {
                if ($item->quantity + 1 > $service->quantity) {
                    return response()->json([
                        'success' => false,
                        'error' => 'So luong vuot qua ton kho',
                    ], 422);
                }

                $item->quantity += 1;
                $item->save();
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'service_id' => $service->id,
                    'quantity' => 1,
                    'price' => ServiceDiscountHelper::applyDiscount($service)['final_price'],
                ]);
            }

            $totalItems = CartItem::where('cart_id', $cart->id)->sum('quantity');

            return response()->json([
                'success' => true,
                'totalItems' => $totalItems,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function remove(Request $request)
    {
        $id = $request->input('id');
        $cart = $request->session()->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
        }

        $request->session()->put('cart', $cart);

        return redirect()->back();
    }

    public function updateQuantity(Request $request)
    {
        $id = $request->input('cart_item_id');
        $qty = (int) $request->input('quantity', 1);

        $cart = $request->session()->get('cart', []);

        if (isset($cart[$id])) {
            $cart[$id]['qty'] = max(1, $qty);
            $request->session()->put('cart', $cart);

            $itemTotal = $cart[$id]['price'] * $cart[$id]['qty'];
            $cartTotal = 0;

            foreach ($cart as $item) {
                $cartTotal += $item['price'] * $item['qty'];
            }

            return response()->json([
                'success' => true,
                'new_quantity' => $cart[$id]['qty'],
                'item_total' => $itemTotal,
                'cart_total' => $cartTotal,
            ]);
        }

        return response()->json(['success' => false], 404);
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

  private function createOrderFromItems($items, $user): Order
  {
      $total = 0;

      foreach ($items as $item) {
          $service = Service::findOrFail($item->service_id);
          [$finalPrice] = $this->getDiscountedPrice($service);
          $total += $finalPrice * $item->quantity;
    }

    $order = Order::create([
        'user_id' => $user->id,
        'cart_id' => $user->cart->id ?? null,
        'status' => 'pending',
        'total_amount' => $total,
    ]);

    foreach ($items as $item) {

        $service = Service::whereKey($item->service_id)->lockForUpdate()->firstOrFail();
        if ($service->quantity < $item->quantity) {
            throw new \RuntimeException("Dich vu {$service->name} chi con {$service->quantity} san pham");
        }

        [$discountedPrice, $discountPercent] = $this->getDiscountedPrice($service);

        OrderItem::create([
            'order_id' => $order->id,
            'service_id' => $item->service_id,

            'price' => $discountedPrice,
            'original_price' => $service->price,
            'discount_percent' => $discountPercent,

            'quantity' => $item->quantity,
        ]);

    }

    return $order;
}

private function getDiscountedPrice(Service $service): array
{
    $discount = ServiceDiscountHelper::applyDiscount($service);

    return [$discount['final_price'], $discount['discount_percent']];
}
    private function createPendingPayment(Order $order): Payment
    {
        return Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'amount' => $order->total_amount,
                'status' => 'pending',
            ]
        );
    }

   

        public function checkoutBuyNow(Request $request)
        {
            $request->validate([
                'service_id' => 'required|exists:services,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $user = $request->user();
            $service = Service::findOrFail($request->service_id);
            if ($service->quantity < $request->quantity) {
                return back()->with('error', 'So luong vuot qua ton kho');
            }

            $item = new \stdClass();
            $item->service_id = $service->id;
            $item->quantity = $request->quantity;


$item->price = ServiceDiscountHelper::applyDiscount($service)['final_price'];

            DB::beginTransaction();

            try {
                $order = $this->createOrderFromItems(collect([$item]), $user);
                $this->createPendingPayment($order);

                DB::commit();

                return redirect()->route('user.payment.order', $order->id);
            } catch (\Exception $e) {
                DB::rollBack();

                return back()->with('error', 'Thanh toan that bai');
            }
        }
public function checkoutSelected(Request $request)
{
    $request->validate([
        'selected_items' => 'required',
    ]);

    $user = $request->user();

    $ids = $request->selected_items;

    if (is_string($ids)) {
        $ids = explode(',', $ids);
    }

    $ids = array_map('intval', $ids);
    $ids = array_filter($ids);

    if (empty($ids)) {
        return back()->with('error', 'Vui lòng chọn sản phẩm');
    }

    $items = CartItem::whereIn('id', $ids)
        ->whereHas('cart', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->with('service')
        ->get();

    if ($items->isEmpty()) {
        return back()->with('error', 'Không tìm thấy sản phẩm hợp lệ');
    }

    DB::beginTransaction();
    try {

        foreach ($items as $item) {
            $item->price = ServiceDiscountHelper::applyDiscount($item->service)['final_price'];
            $item->quantity = $item->quantity ?? 1;
        }

        // ✔ KHÔNG cần truyền total nếu createOrderFromItems tự tính đúng
        $order = $this->createOrderFromItems($items, $user);

        $this->createPendingPayment($order);

        DB::commit();

        return redirect()->route('user.payment.order', $order->id);

    } catch (\Throwable $e) {

        DB::rollBack();


        return back()->with('error', 'Thanh toán thất bại');
    }
}
public function updateItem(Request $request)
{
    $request->validate([
        'cart_item_id' => 'required|integer',
        'quantity' => 'required|integer|min:1',
    ]);

    $user = auth()->user();

    $item = CartItem::where('id', $request->cart_item_id)
        ->whereHas('cart', fn($q) => $q->where('user_id', $user->id))
        ->first();

    if (!$item) {
        return response()->json(['success' => false], 404);
    }

    $item->load('service');
    if ($item->service && $request->quantity > $item->service->quantity) {
        return response()->json([
            'success' => false,
            'message' => 'So luong vuot qua ton kho',
            'max_quantity' => $item->service->quantity,
        ], 422);
    }

    $item->quantity = $request->quantity;
    if ($item->service) {
        $item->price = ServiceDiscountHelper::applyDiscount($item->service)['final_price'];
    }
    $item->save();

    // lấy price đã lưu sẵn
    $itemTotal = $item->price * $item->quantity;

    // tính tổng cart đơn giản
    $cartTotal = CartItem::whereHas('cart', fn($q) => $q->where('user_id', $user->id))
        ->get()
        ->sum(fn($i) => $i->price * $i->quantity);

    return response()->json([
        'success' => true,
        'quantity' => $item->quantity,
        'item_total' => $itemTotal,
        'cart_total' => $cartTotal,
    ]);
}
    public function createPayment(Request $request)
    {
        return view('momo.redirect');
    }
}
