<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MbBankWebhookController extends Controller
{
    /**
     * Handle SePay webhook.
     *
     * SePay HMAC:
     *   timestamp + "." + raw_body
     *
     * Headers:
     *   X-SePay-Signature
     *   X-SePay-Timestamp
     */
    public function handle(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. READ RAW BODY
        |--------------------------------------------------------------------------
        */

        $rawBody = $request->getContent();

        if ($rawBody === '') {
            Log::warning('SePay webhook: empty body');

            return response()->json([
                'success' => false,
                'message' => 'Empty body',
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. VERIFY HMAC-SHA256
        |--------------------------------------------------------------------------
        */

        if (!$this->verifySePaySignature($request, $rawBody)) {
            Log::warning('SePay webhook: invalid signature', [
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid signature',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | 3. PARSE JSON
        |--------------------------------------------------------------------------
        */

        $data = json_decode($rawBody, true);

        if (!is_array($data)) {
            Log::warning('SePay webhook: invalid JSON');

            return response()->json([
                'success' => false,
                'message' => 'Invalid JSON',
            ], 400);
        }

        Log::info('SePay webhook received', [
            'id' => $data['id'] ?? null,
            'code' => $data['code'] ?? null,
            'content' => $data['content'] ?? null,
            'transferAmount' => $data['transferAmount'] ?? null,
            'transferType' => $data['transferType'] ?? null,
            'referenceCode' => $data['referenceCode'] ?? null,
            'accountNumber' => $data['accountNumber'] ?? null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 4. ONLY PROCESS MONEY IN
        |--------------------------------------------------------------------------
        */

        $transferType = strtolower(
            (string) ($data['transferType'] ?? '')
        );

        if ($transferType !== 'in') {
            Log::info('SePay webhook: ignored outgoing transaction', [
                'transferType' => $transferType,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ignored non-incoming transaction',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 5. GET TRANSACTION DATA
        |--------------------------------------------------------------------------
        */

        $transactionId = $data['id'] ?? null;

        $referenceCode = $data['referenceCode']
            ?? null;

        $content = trim(
            (string) ($data['content'] ?? '')
        );

        $amount = (int) (
            $data['transferAmount']
            ?? 0
        );

        $accountNumber = $data['accountNumber']
            ?? null;

        /*
        |--------------------------------------------------------------------------
        | 6. VALIDATE AMOUNT
        |--------------------------------------------------------------------------
        */

        if ($amount <= 0) {
            Log::warning('SePay webhook: invalid amount', [
                'amount' => $amount,
                'transaction_id' => $transactionId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid amount',
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | 7. TEST MODE
        |--------------------------------------------------------------------------
        |
        | SePay Test Mode thường dùng id = 0.
        |
        */

        if ((int) $transactionId === 0) {
            Log::info('SePay webhook: test transaction received', [
                'content' => $content,
                'amount' => $amount,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Test webhook received',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 8. GET PAYMENT CODE
        |--------------------------------------------------------------------------
        |
        | Ưu tiên:
        |
        |   data.code
        |
        | Ví dụ:
        |
        |   ORDER67
        |
        | Nếu không có code thì fallback sang content.
        |
        */

        $paymentCode = trim(
            (string) ($data['code'] ?? '')
        );

        $parsed = null;

        if ($paymentCode !== '') {
            $parsed = $this->parseTransferCode($paymentCode);
        }

        if (!$parsed) {
            $parsed = $this->parseTransferCode($content);
        }

        if (!$parsed) {
            Log::info('SePay webhook: no valid payment code', [
                'transaction_id' => $transactionId,
                'code' => $paymentCode,
                'content' => $content,
                'amount' => $amount,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transaction received but no valid payment code',
            ]);
        }

        $type = $parsed['type'];
        $id = $parsed['id'];

        /*
        |--------------------------------------------------------------------------
        | 9. PROCESS PAYMENT
        |--------------------------------------------------------------------------
        */

        if ($type === 'order') {
            $result = $this->processOrderPayment(
                $id,
                $amount,
                $transactionId,
                $referenceCode,
                $accountNumber
            );
        } elseif ($type === 'booking') {
            $result = $this->processBookingPayment(
                $id,
                $amount,
                $transactionId,
                $referenceCode,
                $accountNumber
            );
        } else {
            $result = [
                'status' => 'error',
                'reason' => 'Unknown payment type',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | 10. LOG RESULT
        |--------------------------------------------------------------------------
        */

        Log::info('SePay webhook: completed', [
            'transaction_id' => $transactionId,
            'reference_code' => $referenceCode,
            'payment_code' => $paymentCode,
            'type' => $type,
            'id' => $id,
            'amount' => $amount,
            'result' => $result,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 11. RETURN 200
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'Webhook processed',
            'result' => $result,
        ]);
    }

    /**
     * Verify SePay HMAC-SHA256 signature.
     */
    private function verifySePaySignature(
        Request $request,
        string $rawBody
    ): bool {
        $secret = config('services.sepay.webhook_secret');

        if (empty($secret)) {
            Log::error(
                'SePay webhook: SEPAY_WEBHOOK_SECRET is not configured'
            );

            return false;
        }

        $signature = $request->header(
            'X-SePay-Signature',
            ''
        );

        $timestampHeader = $request->header(
            'X-SePay-Timestamp',
            ''
        );

        if ($signature === '' || $timestampHeader === '') {
            Log::warning(
                'SePay webhook: missing HMAC headers'
            );

            return false;
        }

        if (!ctype_digit((string) $timestampHeader)) {
            Log::warning(
                'SePay webhook: invalid timestamp'
            );

            return false;
        }

        $timestamp = (int) $timestampHeader;

        /*
        |--------------------------------------------------------------------------
        | Anti replay
        |--------------------------------------------------------------------------
        */

        if (abs(time() - $timestamp) > 300) {
            Log::warning(
                'SePay webhook: request expired',
                [
                    'timestamp' => $timestamp,
                    'server_time' => time(),
                ]
            );

            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Generate expected signature
        |--------------------------------------------------------------------------
        |
        | SePay:
        |
        | timestamp.raw_body
        |
        */

        $signedPayload = $timestamp . '.' . $rawBody;

        $expectedSignature =
            'sha256=' .
            hash_hmac(
                'sha256',
                $signedPayload,
                $secret
            );

        return hash_equals(
            $expectedSignature,
            $signature
        );
    }

    /**
     * Parse the transfer code from description.
     *
     * Supports:
     *
     * ORDER67
     * BOOK25
     * BOOKING25
     * DON67
     *
     * Also supports:
     *
     * TT ORDER67
     * Thanh toan ORDER67
     * ORDER67- Ma GD ACSP/ xxxx
     */
    private function parseTransferCode(
        string $description
    ): ?array {
        $description = strtoupper(
            trim($description)
        );

        if (
            preg_match(
                '/\b(ORDER|DON|BOOKING|BOOK)(\d+)\b/',
                $description,
                $matches
            )
        ) {
            $prefix = $matches[1];
            $type = in_array($prefix, ['ORDER', 'DON']) ? 'order' : 'booking';
            
            return [
                'type' => $type,
                'id' => (int) $matches[2],
            ];
        }

        return null;
    }

    /**
     * Process booking payment.
     */
    private function processBookingPayment(
        int $bookingId,
        int $amount,
        $transactionId,
        $referenceCode = null,
        $accountNumber = null
    ): array {
        $booking = Booking::find($bookingId);

        if (!$booking) {
            return [
                'status' => 'error',
                'reason' => "Booking #{$bookingId} not found",
            ];
        }

        $payment = BookingPayment::where(
            'booking_id',
            $bookingId
        )->first();

        if (!$payment) {
            return [
                'status' => 'error',
                'reason' => "BookingPayment for #{$bookingId} not found",
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Already paid
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $payment->status,
                ['paid', 'success'],
                true
            )
        ) {
            return [
                'status' => 'skipped',
                'reason' => 'Already paid',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Amount validation
        |--------------------------------------------------------------------------
        */

        $expectedAmount = (int) $payment->amount;

        if ($amount < $expectedAmount) {
            Log::warning(
                'SePay webhook: booking amount mismatch',
                [
                    'booking_id' => $bookingId,
                    'expected' => $expectedAmount,
                    'received' => $amount,
                ]
            );

            return [
                'status' => 'error',
                'reason' =>
                    "Amount mismatch: expected {$expectedAmount}, got {$amount}",
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Process
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $payment,
            $booking,
            $transactionId
        ) {
            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => 'bank_transfer',
                'momo_trans_id' => $transactionId,
            ]);

            $booking->update([
                'status' => 'confirmed',
            ]);
        });

        Log::info(
            'SePay webhook: booking payment confirmed',
            [
                'booking_id' => $bookingId,
                'amount' => $amount,
                'transaction_id' => $transactionId,
                'reference_code' => $referenceCode,
                'account_number' => $accountNumber,
            ]
        );

        return [
            'status' => 'success',
            'type' => 'booking',
            'id' => $bookingId,
        ];
    }

    /**
     * Process order payment.
     */
    private function processOrderPayment(
        int $orderId,
        int $amount,
        $transactionId,
        $referenceCode = null,
        $accountNumber = null
    ): array {
        $order = Order::find($orderId);

        if (!$order) {
            return [
                'status' => 'error',
                'reason' => "Order #{$orderId} not found",
            ];
        }

        $payment = Payment::where(
            'order_id',
            $orderId
        )->first();

        if (!$payment) {
            return [
                'status' => 'error',
                'reason' => "Payment for Order #{$orderId} not found",
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Already paid
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $payment->status,
                ['paid', 'success'],
                true
            )
        ) {
            return [
                'status' => 'skipped',
                'reason' => 'Already paid',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Amount validation
        |--------------------------------------------------------------------------
        */

        $expectedAmount = (int) $payment->amount;

        if ($amount < $expectedAmount) {
            Log::warning(
                'SePay webhook: order amount mismatch',
                [
                    'order_id' => $orderId,
                    'expected' => $expectedAmount,
                    'received' => $amount,
                ]
            );

            return [
                'status' => 'error',
                'reason' =>
                    "Amount mismatch: expected {$expectedAmount}, got {$amount}",
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Process order
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $payment,
            $order,
            $transactionId
        ) {
            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => 'bank_transfer',
                'momo_trans_id' => $transactionId,
            ]);

            $order->update([
                'status' => 'confirmed',
                'payment_method' => 'bank_transfer',
            ]);

            OrderItem::where(
                'order_id',
                $order->id
            )->update([
                'status' => 'confirmed',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Clear cart
            |--------------------------------------------------------------------------
            */

            if ($order->cart_id) {
                $serviceIds = OrderItem::where(
                    'order_id',
                    $order->id
                )->pluck('service_id');

                CartItem::where(
                    'cart_id',
                    $order->cart_id
                )
                    ->whereIn(
                        'service_id',
                        $serviceIds
                    )
                    ->delete();
            }
        });

        Log::info(
            'SePay webhook: order payment confirmed',
            [
                'order_id' => $orderId,
                'amount' => $amount,
                'transaction_id' => $transactionId,
                'reference_code' => $referenceCode,
                'account_number' => $accountNumber,
            ]
        );

        return [
            'status' => 'success',
            'type' => 'order',
            'id' => $orderId,
        ];
    }
}
