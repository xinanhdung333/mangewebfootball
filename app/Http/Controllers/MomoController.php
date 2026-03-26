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

    $partnerCode = "MOMOBKUN20180529";
    $accessKey = "klm05TvNBzhg7h7j";
    $secretKey = "at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa";

$amount = (string) intval($order->total_amount);

$orderId = $order->id . "_" . uniqid();
$orderInfo = "Thanh_toan_don_hang_" . $order->id;
$redirectUrl = route('user.momo.return');
$ipnUrl = route('user.momo.ipn'); 

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
        $status = $request->resultCode == 0 ? 'success' : 'failed';

        // Lấy orderId để show user (có thể lấy thông tin Order từ DB)
        $orderId = explode('_', $request->orderId)[0];
        $order = Order::find($orderId);

        if (!$order) {
            return redirect()->route('user.myServices')
                ->with('error', 'Đơn hàng không tồn tại');
        }

        return redirect()->route('user.myServices')
            ->with('success', $status === 'success' ? 'Thanh toán thành công' : 'Thanh toán thất bại');
    }

    /**
     * MoMo IPN POST
     * Cập nhật DB, verify signature
     */
    public function ipnUrl(Request $request)
    {
        // 1. verify signature từ MoMo
        if (!$this->verifyMoMoSignature($request)) {
            return response()->json(['status' => 'invalid signature'], 400);
        }

        $orderId = explode('_', $request->orderId)[0];

        if ($request->resultCode == 0) {
            DB::transaction(function () use ($orderId, $request) {

                // Cập nhật Payment
                Payment::updateOrCreate(
                    ['order_id' => $orderId],
                    [
                        'momo_trans_id' => $request->transId,
                        'amount' => $request->amount,
                        'status' => 'success',
                        'paid_at' => now()
                    ]
                );

                // Cập nhật Order
                Order::where('id', $orderId)
                    ->update(['status' => 'confirmed']);

                // Xóa CartItem của user
                $userId = Order::find($orderId)->user_id;

                CartItem::whereHas('cart', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                })->delete();
            });
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Hàm verify chữ ký từ MoMo
     */
    private function verifyMoMoSignature(Request $request)
    {
        // Lấy các thông số MoMo gửi về
        $data = [
            'partnerCode' => $request->partnerCode,
            'accessKey'   => config('momo.accessKey'),
            'requestId'   => $request->requestId,
            'amount'      => $request->amount,
            'orderId'     => $request->orderId,
            'orderInfo'   => $request->orderInfo,
            'orderType'   => $request->orderType,
            'transId'     => $request->transId,
            'message'     => $request->message,
            'resultCode'  => $request->resultCode,
            'payType'     => $request->payType,
            'responseTime'=> $request->responseTime
        ];

        $rawHash = http_build_query($data, '', '&');
        $computedSignature = hash_hmac('sha256', $rawHash, config('momo.secretKey'));

        return $computedSignature === $request->signature;
    }
}
