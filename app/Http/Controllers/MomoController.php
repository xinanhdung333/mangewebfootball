<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use Illuminate\Support\Facades\Http;

class MomoController extends Controller
{
    public function createPayment()
    {
        $endpoint = env('MOMO_ENDPOINT');
        $partnerCode = env('MOMO_PARTNER_CODE');
        $accessKey = env('MOMO_ACCESS_KEY');
        $secretKey = env('MOMO_SECRET_KEY');
        $redirectUrl = env('MOMO_REDIRECT_URL');
        $ipnUrl = env('MOMO_IPN_URL');

        $amount = 5000000;
        $orderId = time();
        $requestId = time();
        $orderInfo = "TRẢ TIỀN THẰNG PHONG NGUU";
        $extraData = "";
        $requestType = "payWithMethod";

        $rawHash =
            "accessKey=".$accessKey.
            "&amount=".$amount.
            "&extraData=".$extraData.
            "&ipnUrl=".$ipnUrl.
            "&orderId=".$orderId.
            "&orderInfo=".$orderInfo.
            "&partnerCode=".$partnerCode.
            "&redirectUrl=".$redirectUrl.
            "&requestId=".$requestId.
            "&requestType=".$requestType;

        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        $data = [
            'partnerCode' => $partnerCode,
            'partnerName' => "MoMo Payment",
            'storeId' => "TestStore",
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature
        ];

        $response = Http::post($endpoint, $data)->json();

        if (isset($response['payUrl'])) {
            return redirect()->away($response['payUrl']);
        }

        return $response;
    }

    public function returnUrl(Request $request)
    {
        return response()->json($request->all());
    }

    public function ipnUrl(Request $request)
    {
        // TODO: verify signature
        // TODO: cập nhật đơn hàng DB nếu bạn muốn

        return response()->json(['status' => 'ok', 'data' => $request->all()]);
    }
}
  
