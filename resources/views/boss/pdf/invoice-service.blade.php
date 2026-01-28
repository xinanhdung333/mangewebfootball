<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; margin: 20px; }
        h1 { text-align: center; font-size: 18px; margin-bottom: 20px; }
        .header { margin-bottom: 20px; line-height: 1.8; }
        .header p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { font-weight: bold; }
        .footer { margin-top: 20px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <h1>HÓA ĐƠN FOOTBALL BOOKING</h1>
    
    <div class="header">
        <p><strong>Loại hóa đơn:</strong> DỊCH VỤ</p>
        <p><strong>Khách hàng:</strong> {{ $order->user_name }} ({{ $order->email }})</p>
        <p><strong>Ngày mua:</strong> {{ date('d/m/Y H:i:s', strtotime($order->created_at)) }}</p>
        <p><strong>Ngày hóa đơn:</strong> {{ date('d/m/Y H:i:s') }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 40%;">Dịch vụ</th>
                <th style="width: 20%;" class="text-right">Giá</th>
                <th style="width: 15%;" class="text-center">SL</th>
                <th style="width: 25%;" class="text-right">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td class="text-right">{{ number_format($item->price, 0, ',', '.') }} VNĐ</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->price * $item->quantity, 0, ',', '.') }} VNĐ</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3" class="text-right">Tổng cộng</td>
                <td class="text-right">{{ number_format($order->total_amount, 0, ',', '.') }} VNĐ</td>
            </tr>
        </tbody>
    </table>
    
    <div class="footer">
        <p>Cảm ơn quý khách đã sử dụng dịch vụ của chúng tôi</p>
        <p>Football Booking - {{ date('Y') }}</p>
    </div>
</body>
</html>
