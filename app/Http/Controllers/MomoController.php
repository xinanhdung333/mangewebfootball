<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use App\Models\Payment;
class MomoController extends Controller
{

    
function execPostRequest($url, $data)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data))
    );
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    //execute post
    $result = curl_exec($ch);
    //close connection
    curl_close($ch);
    return $result;
}
 public function createPayment(Request $request)
{
    $order = Order::findOrFail($request->order_id);

    $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";

 $partnerCode = config('momo.partnerCode');
$accessKey   = config('momo.accessKey');
$secretKey   = config('momo.secretKey');

$amount = (string) intval($order->total_amount);

$orderId = $order->id . "_" . uniqid();
$orderInfo = "Thanh_toan_don_hang_" . $order->id;
$redirectUrl = config('momo.redirectUrl');
$ipnUrl = config('momo.redirectUrl');

  Payment::updateOrCreate(
    ['order_id' => $order->id],
    [
        'momo_order_id' => $orderId,
        'amount' => $amount,
        'status' => 'pending'
    ]
);
    $extraData = "";

    $requestId = (string) time();

$requestType = "payWithATM";
    $rawHash =
        "accessKey=" . $accessKey .
        "&amount=" . $amount .
        "&extraData=" . $extraData .
        "&ipnUrl=" . $ipnUrl .
        "&orderId=" . $orderId .
        "&orderInfo=" . $orderInfo .
        "&partnerCode=" . $partnerCode .
        "&redirectUrl=" . $redirectUrl .
        "&requestId=" . $requestId .
        "&requestType=" . $requestType;

    $signature = hash_hmac("sha256", $rawHash, $secretKey);

    $data = [
        "partnerCode" => $partnerCode,
        "partnerName" => "Test",
        "storeId" => "MomoTestStore",
        "requestId" => $requestId,
        "amount" => $amount,
        "orderId" => $orderId,
        "orderInfo" => $orderInfo,
        "redirectUrl" => $redirectUrl,
        "ipnUrl" => $ipnUrl,
        "lang" => "vi",
        "extraData" => $extraData,
        "requestType" => $requestType,
        "signature" => $signature
    ];
    $result = $this->execPostRequest($endpoint, json_encode($data));

    $jsonResult = json_decode($result, true);
   if (!isset($jsonResult['payUrl'])) {
    return back()->with('error', 'Không tạo được link thanh toán MoMo');
}
    return redirect()->to($jsonResult['payUrl']);
}



    /**
     * MoMo redirect GET (user trở về website)
     * Chỉ dùng để hiển thị kết quả, KHÔNG update DB
     */
public function returnUrl(Request $request)
{
    $orderId = explode('_', $request->orderId)[0];

    if ($request->resultCode != 0) {

        Payment::updateOrCreate(
            ['order_id' => $orderId],
            [
                'status' => 'failed',
            ]
        );

        return redirect()->route('user.myServices')
            ->with('error', 'Thanh toán thất bại');
    }

    DB::transaction(function () use ($orderId, $request) {

        Payment::updateOrCreate(
            ['order_id' => $orderId],
            [
                'momo_trans_id' => $request->transId,
                'amount' => $request->amount,
                'status' => 'success',
                'paid_at' => now()
            ]
        );

        Order::where('id', $orderId)
            ->update(['status' => 'confirmed']);

  $order = Order::find($orderId);
 
$serviceIds = OrderItem::where('order_id', $orderId)
    ->pluck('service_id');

CartItem::where('cart_id', $order->cart_id)
    ->whereIn('service_id', $serviceIds)
    ->delete();

    });
    return redirect()->route('user.myServices')
        ->with('success', 'Thanh toán thành công');
}
  
    public function ipnUrl(Request $request)
{

}

  
}
    