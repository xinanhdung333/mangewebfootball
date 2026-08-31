<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MomoController extends Controller
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            Log::error('MoMo cURL error', [
                'url' => $url,
                'curl_errno' => $errno,
                'curl_error' => $error,
            ]);
            curl_close($ch);
            return json_encode([
                'resultCode' => -1,
                'message' => 'Lỗi kết nối MoMo: ' . $error . ' (cURL #' . $errno . ')',
            ]);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        Log::info('MoMo cURL response', [
            'http_code' => $httpCode,
            'response_length' => strlen($result),
        ]);

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

    private function markPaymentFailed(Payment $payment): void
    {
        $payment->update([
            'status' => 'failed',
        ]);
    }

    private function markPaymentSuccess(Payment $payment, Request $request): void
    {
        DB::transaction(function () use ($payment, $request) {
            $payment->update([
                'momo_trans_id' => $request->input('transId'),
                'amount' => $request->input('amount'),
                'status' => 'success',
                'paid_at' => now(),
                'payment_method' => 'momo',
            ]);

            Order::where('id', $payment->order_id)
                ->update(['status' => 'confirmed']);

            OrderItem::where('order_id', $payment->order_id)
                ->update(['status' => 'confirmed']);

            $order = Order::find($payment->order_id);

            if (! $order) {
                return;
            }

            $serviceIds = OrderItem::where('order_id', $payment->order_id)
                ->pluck('service_id');

            CartItem::where('cart_id', $order->cart_id)
                ->whereIn('service_id', $serviceIds)
                ->delete();
        });
    }

    public function createPayment(Request $request)
    {
        $order = Order::where('id', $request->order_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $endpoint = 'https://test-payment.momo.vn/v2/gateway/api/create';
        $partnerCode = config('momo.partnerCode');
        $accessKey = config('momo.accessKey');
        $secretKey = config('momo.secretKey');
        $payment = Payment::where('order_id', $order->id)->first();
        $amount = (string) intval($payment?->amount ?? $order->total_amount);
        $orderId = $order->id . '_' . uniqid();
        $orderInfo = 'Thanh_toan_don_hang_' . $order->id;
        $redirectUrl = config('momo.redirectUrl');
        $ipnUrl = config('momo.ipnUrl');
        $extraData = '';
        $requestId = (string) time();
        $requestType = 'payWithATM';

        Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'momo_order_id' => $orderId,
                'amount' => $amount,
                'status' => 'pending',
            ]
        );

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

        $payload = [
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
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature,
        ];

        Log::info('MoMo create payment request', [
            'endpoint' => $endpoint,
            'payload' => $payload,
        ]);

        $result = $this->execPostRequest($endpoint, json_encode($payload));
        $jsonResult = json_decode($result, true);

        Log::info('MoMo create payment response', [
            'raw_response' => $result,
            'parsed_response' => $jsonResult,
        ]);

        if (! isset($jsonResult['payUrl'])) {
            $momoMessage = $jsonResult['message'] ?? 'Không rõ lỗi';
            $momoCode = $jsonResult['resultCode'] ?? 'N/A';

            Log::error('MoMo payment link creation failed', [
                'order_id' => $order->id,
                'amount' => $amount,
                'momo_result_code' => $momoCode,
                'momo_message' => $momoMessage,
                'full_response' => $jsonResult,
            ]);

            return back()->with('error', 'Không tạo được link thanh toán MoMo. Lỗi: ' . $momoMessage . ' (Code: ' . $momoCode . ')');
        }

        return redirect()->to($jsonResult['payUrl']);
    }

    public function returnUrl(Request $request)
    {
        $payment = Payment::where('momo_order_id', $request->input('orderId'))->first();
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

        Log::info('MoMo return debug', $debugPayload);

        if ($request->boolean('debug')) {
            dd($debugPayload);
        }

        if (! $payment) {
            Log::warning('MoMo return early redirect: payment not found', $debugPayload);

            return redirect()->route('user.myServices')
                ->with('error', 'Khong tim thay giao dich thanh toan');
        }

        if (! $signatureValid) {
            $this->markPaymentFailed($payment);
            Log::warning('MoMo return early redirect: invalid signature', $debugPayload);

            return redirect()->route('user.myServices')
                ->with('error', 'Chu ky MoMo khong hop le');
        }

        if ((int) $request->input('resultCode') !== 0) {
            $this->markPaymentFailed($payment);
            Log::warning('MoMo return early redirect: failed resultCode', $debugPayload);

            return redirect()->route('user.myServices')
                ->with('error', 'Thanh toan that bai');
        }

        if (! $amountMatches) {
            $this->markPaymentFailed($payment);
            Log::warning('MoMo return early redirect: amount mismatch', $debugPayload);

            return redirect()->route('user.myServices')
                ->with('error', 'So tien thanh toan khong khop');
        }

        if ($payment->status !== 'success') {
            $this->markPaymentSuccess($payment, $request);
        }

        Log::info('MoMo return success', $debugPayload);

        return redirect()->route('user.myServices')
            ->with('success', 'Thanh toan thanh cong');
    }

    public function ipnUrl(Request $request)
    {
        $payment = Payment::where('momo_order_id', $request->input('orderId'))->first();

        if (! $payment) {
            return response()->json([
                'resultCode' => 1,
                'message' => 'Order not found',
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
                'message' => 'Payment failed recorded',
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
