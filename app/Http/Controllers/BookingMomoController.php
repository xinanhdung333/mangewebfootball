<?php
namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingMomoController extends Controller
{
    private function execPostRequest($url, $data)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

  public function createPayment($booking_id)
{
    $booking = Booking::findOrFail($booking_id);

        $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";


        $partnerCode = config('momo.partnerCode');
        $accessKey   = config('momo.accessKey');
        $secretKey   = config('momo.secretKey');

        $amount = (string) intval($booking->total_price);

        $orderId = "booking_" . $booking->id . "_" . uniqid();
        $orderInfo = "Thanh_toan_dat_san_" . $booking->id;

        $redirectUrl = route('booking.momo.return');
        $ipnUrl = route('booking.momo.return');

        BookingPayment::updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'momo_order_id' => $orderId,
                'amount' => $amount,
                'status' => 'pending'
            ]
        );

        $requestId = time();
$extraData = base64_encode(json_encode([
    'booking_id' => $booking->id
]));
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
    "requestId" => $requestId,
    "amount" => $amount,
    "orderId" => $orderId,
    "orderInfo" => $orderInfo,
    "redirectUrl" => $redirectUrl,
    "ipnUrl" => $ipnUrl,
    "requestType" => $requestType,
    "extraData" => $extraData, // 👈 THÊM DÒNG NÀY
    "signature" => $signature
];

      $result = $this->execPostRequest($endpoint, json_encode($data));
        $jsonResult = json_decode($result, true);



        if (!isset($jsonResult['payUrl'])) {
            return back()->with('error', 'Không tạo được thanh toán MoMo');
        }

        return redirect()->to($jsonResult['payUrl']);
    }

    public function returnUrl(Request $request)
    {
$bookingId = explode('_', $request->orderId ?? '')[1] ?? null;
        if (!$bookingId) {
            return redirect()->route('user.myBookings');
        }

        if ($request->resultCode != 0) {

            BookingPayment::where('booking_id', $bookingId)
                ->update(['status' => 'failed']);

            return redirect()->route('user.myBookings')
                ->with('error', 'Thanh toán thất bại');
        }

        DB::transaction(function () use ($bookingId, $request) {

            BookingPayment::where('booking_id', $bookingId)
                ->update([
                    'status' => 'success',
                    'paid_at' => now(),
                    'momo_trans_id' => $request->transId
                ]);

            Booking::where('id', $bookingId)
                ->update([
                    'status' => 'confirmed'
                ]);
        });
        return redirect()->route('user.myBookings')
            ->with('success', 'Thanh toán thành công');
    }
}