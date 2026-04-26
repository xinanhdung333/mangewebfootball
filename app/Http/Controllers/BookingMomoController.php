<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingMomoController extends Controller
{
    private function execPostRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data),
        ]);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    private function buildSignaturePayload(Request $request): string
    {
        return "accessKey=" . config('momo.accessKey')
            . "&amount=" . $request->input('amount')
            . "&extraData=" . $request->input('extraData', '')
            . "&message=" . $request->input('message', '')
            . "&orderId=" . $request->input('orderId')
            . "&orderInfo=" . $request->input('orderInfo', '')
            . "&orderType=" . $request->input('orderType', '')
            . "&partnerCode=" . $request->input('partnerCode')
            . "&payType=" . $request->input('payType', '')
            . "&requestId=" . $request->input('requestId')
            . "&responseTime=" . $request->input('responseTime')
            . "&resultCode=" . $request->input('resultCode')
            . "&transId=" . $request->input('transId');
    }

    private function hasValidSignature(Request $request): bool
    {
        $providedSignature = (string) $request->input('signature', '');

        if ($providedSignature === '') {
            return false;
        }

        $expectedSignature = hash_hmac(
            'sha256',
            $this->buildSignaturePayload($request),
            config('momo.secretKey')
        );

        return hash_equals($expectedSignature, $providedSignature);
    }

    private function markPaymentFailed(BookingPayment $payment): void
    {
        $payment->update([
            'status' => 'failed',
        ]);
    }

    private function markPaymentSuccess(BookingPayment $payment, Request $request): void
    {
        DB::transaction(function () use ($payment, $request) {
            $payment->update([
                'status' => 'success',
                'paid_at' => now(),
                'momo_trans_id' => $request->input('transId'),
                'payment_method' => 'momo',
            ]);

            Booking::where('id', $payment->booking_id)
                ->update([
                    'status' => 'confirmed',
                ]);
        });
    }

    public function createPayment($booking_id)
    {
        $booking = Booking::where('id', $booking_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        $bookingPayment = BookingPayment::where('booking_id', $booking->id)->first();
        $endpoint = 'https://test-payment.momo.vn/v2/gateway/api/create';
        $partnerCode = config('momo.partnerCode');
        $accessKey = config('momo.accessKey');
        $secretKey = config('momo.secretKey');
        $amount = (string) intval($bookingPayment->amount);
        $orderId = 'booking_' . $booking->id . '_' . uniqid();
        $orderInfo = 'Thanh_toan_dat_san_' . $booking->id;
        $redirectUrl = route('booking.momo.return');
        $ipnUrl = route('booking.momo.ipn');
        $extraData = base64_encode(json_encode([
            'booking_id' => $booking->id,
        ]));
        $requestId = (string) time();
        $requestType = 'payWithATM';

       $d =$bookingPayment->update([
    'momo_order_id' => $orderId,
]);
        $rawHash = "accessKey=" . $accessKey
            . "&amount=" . $amount
            . "&extraData=" . $extraData
            . "&ipnUrl=" . $ipnUrl
            . "&orderId=" . $orderId
            . "&orderInfo=" . $orderInfo
            . "&partnerCode=" . $partnerCode
            . "&redirectUrl=" . $redirectUrl
            . "&requestId=" . $requestId
            . "&requestType=" . $requestType;

        $signature = hash_hmac('sha256', $rawHash, $secretKey);

        $data = [
            'partnerCode' => $partnerCode,
            'partnerName' => 'Test',
            'storeId' => 'MomoTestStore',
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'requestType' => $requestType,
            'extraData' => $extraData,
            'signature' => $signature,
        ];

        $result = $this->execPostRequest($endpoint, json_encode($data));
        $jsonResult = json_decode($result, true);

        if (! isset($jsonResult['payUrl'])) {
            Log::warning('Booking MoMo createPayment failed', [
                'booking_id' => $booking->id,
                'payload' => $data,
                'response' => $jsonResult,
                'raw_response' => $result,
            ]);
            return back()->with('error', 'Khong tao duoc thanh toan MoMo');
        }
        return redirect()->to($jsonResult['payUrl']);
    }

    public function returnUrl(Request $request)
    {
        $payment = BookingPayment::where('momo_order_id', $request->input('orderId'))->first();
        $signatureValid = $payment ? $this->hasValidSignature($request) : false;
        $amountMatches = $payment
            ? ((int) $payment->amount === (int) $request->input('amount'))
            : false;

        $debugPayload = [
            'request' => $request->all(),
            'payment_found' => (bool) $payment,
            'payment_id' => $payment?->id,
            'payment_status' => $payment?->status,
            'payment_amount' => $payment?->amount,
            'signature_valid' => $signatureValid,
            'amount_matches' => $amountMatches,
            'result_code' => $request->input('resultCode'),
        ];

        Log::info('Booking MoMo return debug', $debugPayload);

        if ($request->boolean('debug')) {
            dd($debugPayload);
        }

        if (! $payment) {
            Log::warning('Booking MoMo early redirect: payment not found', $debugPayload);

            return redirect()->route('user.myBookings')
                ->with('error', 'Khong tim thay giao dich thanh toan booking');
        }

        if (! $signatureValid) {
            $this->markPaymentFailed($payment);
            Log::warning('Booking MoMo early redirect: invalid signature', $debugPayload);

            return redirect()->route('user.myBookings')
                ->with('error', 'Chu ky MoMo booking khong hop le');
        }

        if ((int) $request->input('resultCode') !== 0) {
            $this->markPaymentFailed($payment);
            Log::warning('Booking MoMo early redirect: failed resultCode', $debugPayload);

            return redirect()->route('user.myBookings')
                ->with('error', 'Thanh toan booking that bai');
        }

        if (! $amountMatches) {
            $this->markPaymentFailed($payment);
            Log::warning('Booking MoMo early redirect: amount mismatch', $debugPayload);

            return redirect()->route('user.myBookings')
                ->with('error', 'So tien booking thanh toan khong khop');
        }

        if ($payment->status !== 'success') {
            $this->markPaymentSuccess($payment, $request);
        }

        Log::info('Booking MoMo return success', $debugPayload);

        return redirect()->route('user.myBookings')
            ->with('success', 'Thanh toan booking thanh cong');
    }

    public function ipnUrl(Request $request)
    {
        $payment = BookingPayment::where('momo_order_id', $request->input('orderId'))->first();

        if (! $payment) {
            return response()->json([
                'resultCode' => 1,
                'message' => 'Booking payment not found',
            ], 404);
        }

        if (! $this->hasValidSignature($request)) {
            $this->markPaymentFailed($payment);

            return response()->json([
                'resultCode' => 1,
                'message' => 'Invalid signature',
            ], 400);
        }

        if ((int) $request->input('resultCode') !== 0) {
            $this->markPaymentFailed($payment);

            return response()->json([
                'resultCode' => 0,
                'message' => 'Booking payment failed recorded',
            ]);
        }

        if ((int) $payment->amount !== (int) $request->input('amount')) {
            $this->markPaymentFailed($payment);

            return response()->json([
                'resultCode' => 1,
                'message' => 'Amount mismatch',
            ], 400);
        }

        if ($payment->status !== 'success') {
            $this->markPaymentSuccess($payment, $request);
        }

        return response()->json([
            'resultCode' => 0,
            'message' => 'Success',
        ]);
    }
}
