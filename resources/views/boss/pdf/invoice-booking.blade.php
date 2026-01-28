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
        .total-row { font-weight: bold; }
        .footer { margin-top: 20px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <h1>HÓA ĐƠN FOOTBALL BOOKING</h1>
    
    <div class="header">
        <p><strong>Loại hóa đơn:</strong> BOOKING SÂN</p>
        <p><strong>Khách hàng:</strong> {{ $booking->user_name }} ({{ $booking->email }})</p>
        <p><strong>Sân:</strong> {{ $booking->field_name }} - {{ $booking->location }}</p>
        <p><strong>Thời gian:</strong> {{ $booking->booking_date }} {{ $booking->start_time }} - {{ $booking->end_time }}</p>
        <p><strong>Ngày hóa đơn:</strong> {{ date('d/m/Y H:i:s') }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 70%;">Nội dung</th>
                <th style="width: 30%;" class="text-right">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Tiền thuê sân</td>
                <td class="text-right">{{ number_format($booking->total_price, 0, ',', '.') }} VNĐ</td>
            </tr>
            @php
                $serviceTotal = 0;
            @endphp
            @foreach($services as $service)
                @php
                    $serviceTotal += ($service->price * $service->quantity);
                @endphp
                <tr>
                    <td>{{ $service->name }} x{{ $service->quantity }}</td>
                    <td class="text-right">{{ number_format($service->price * $service->quantity, 0, ',', '.') }} VNĐ</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td>Tổng cộng</td>
                <td class="text-right">{{ number_format($booking->total_price + $serviceTotal, 0, ',', '.') }} VNĐ</td>
            </tr>
        </tbody>
    </table>
    
    <div class="footer">
        <p>Cảm ơn quý khách đã sử dụng dịch vụ của chúng tôi</p>
        <p>Football Booking - {{ date('Y') }}</p>
    </div>
</body>
</html>
