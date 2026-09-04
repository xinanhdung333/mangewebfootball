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
use App\Models\ServiceDiscount;
use Carbon\Carbon;
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
                    'price' => $service->price,
                ]);
            }

            return response()->json(['success' => true]);
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

        // 🔥 dùng giá đã giảm
        $total += $item->price * $item->quantity;
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

        $originalPrice = $service->price;

        $discountPercent = 0;

        if ($item->price < $originalPrice) {
            $discountPercent = round((1 - ($item->price / $originalPrice)) * 100);
        }

        OrderItem::create([
            'order_id' => $order->id,
            'service_id' => $item->service_id,

            // 🔥 giá sau giảm
            'price' => $item->price,

            // 🔥 lưu thêm
            'original_price' => $originalPrice,
            'discount_percent' => $discountPercent,

            'quantity' => $item->quantity,
        ]);

        $service->decrement('quantity', $item->quantity);
    }

    return $order;
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


$now = Carbon::now();
$currentMin = $now->hour * 60 + $now->minute;

$finalPrice = $service->price;

// lấy rule
$rules = ServiceDiscount::where('is_active', true)->where(function($q) use ($service) {
    $q->where('service_id', $service->id)
      ->orWhereNull('service_id');
})
->orderByRaw('service_id IS NULL') // ưu tiên riêng
->get();
foreach ($rules as $rule) {

    $start = explode(':', $rule->start_time);
    $end   = explode(':', $rule->end_time);

    $startMin = $start[0] * 60 + $start[1];
    $endMin   = $end[0] * 60 + $end[1];

    $matchService =
        $rule->service_id == null || $rule->service_id == $service->id;

    $inTime =
        ($startMin <= $endMin && $currentMin >= $startMin && $currentMin < $endMin)
        ||
        ($startMin > $endMin && ($currentMin >= $startMin || $currentMin < $endMin));

    if ($matchService && $inTime) {
        $finalPrice = $service->price * $rule->multiplier;
        break;
    }
}

$item->price = $finalPrice;

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

    $now = Carbon::now();
    $currentMin = $now->hour * 60 + $now->minute;

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

            $service = $item->service;

            $finalPrice = $service->price;

            $rules = ServiceDiscount::where('is_active', true)->where(function ($q) use ($service) {
                $q->where('service_id', $service->id)
                  ->orWhereNull('service_id');
            })
            ->orderByRaw('service_id IS NULL')
            ->get();

            foreach ($rules as $rule) {

                [$sh, $sm] = explode(':', $rule->start_time);
                [$eh, $em] = explode(':', $rule->end_time);

                $startMin = $sh * 60 + $sm;
                $endMin   = $eh * 60 + $em;

                $inTime =
                    ($startMin <= $endMin && $currentMin >= $startMin && $currentMin < $endMin)
                    ||
                    ($startMin > $endMin && ($currentMin >= $startMin || $currentMin < $endMin));

                if ($inTime) {
                    $finalPrice = $service->price * $rule->multiplier;
                    break;
                }
            }

            // ✔ FIX QUAN TRỌNG
            $item->price = $finalPrice;
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
