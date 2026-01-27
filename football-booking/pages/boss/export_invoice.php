<?php

session_start();
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
require_once '../../libs/fpdf.php';

if (!isLoggedIn()) {
    die(iconv('UTF-8','CP1258','Bạn chưa đăng nhập'));
}

$type = $_GET['type'] ?? 'booking'; // booking | service
$id   = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    die(iconv('UTF-8','CP1258','ID không hợp lệ'));
}

// ================= PDF CLASS =================
class PDF extends FPDF {
    function Header() {
        $this->AddFont('DejaVu','','DejaVuSans.php');
        $this->AddFont('DejaVu','B','DejaVuSans-Bold.php');
        
        $this->SetFont('DejaVu','B',16);
        $this->Cell(0,10,iconv('UTF-8','CP1258','HÓA ĐƠN FOOTBALL BOOKING'),0,1,'C');
        $this->Ln(5);
    }
}

// ================= CREATE PDF =================
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('DejaVu','',11);


// ================= BOOKING INVOICE =================
if ($type === 'booking') {

    $stmt = $conn->prepare("SELECT b.*, u.name AS user_name, u.email, f.name AS field_name, f.location
                            FROM bookings b
                            JOIN users u ON b.user_id = u.id
                            JOIN fields f ON b.field_id = f.id
                            WHERE b.id = ? AND b.status IN ('confirmed','completed')");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();

    if (!$booking) die(iconv('UTF-8','CP1258','Không tìm thấy booking hoặc chưa đủ điều kiện xuất hóa đơn'));

    if ($_SESSION['role'] === 'user' && $_SESSION['user_id'] != $booking['user_id']) {
        die(iconv('UTF-8','CP1258','Không có quyền'));
    }

    $pdf->Cell(0,8,iconv('UTF-8','CP1258','Loại hóa đơn: BOOKING'),0,1);
    $pdf->Cell(0,8,iconv('UTF-8','CP1258','Khách hàng: '.$booking['user_name'].' ('.$booking['email'].')'),0,1);
    $pdf->Cell(0,8,iconv('UTF-8','CP1258','Sân: '.$booking['field_name'].' - '.$booking['location']),0,1);
    $pdf->Cell(0,8,iconv('UTF-8','CP1258','Thời gian: '.$booking['booking_date'].' '.$booking['start_time'].' - '.$booking['end_time']),0,1);
    $pdf->Ln(5);

    $pdf->SetFont('DejaVu','B',11);
    $pdf->Cell(120,8,iconv('UTF-8','CP1258','Nội dung'),1);
    $pdf->Cell(40,8,iconv('UTF-8','CP1258','Thành tiền'),1,1,'R');

    $pdf->SetFont('DejaVu','',11);
    $pdf->Cell(120,8,iconv('UTF-8','CP1258','Tiền thuê sân'),1);
    $pdf->Cell(40,8,number_format($booking['total_price']),1,1,'R');

    $serviceTotal = 0;
    $sv = $conn->prepare("SELECT s.name, s.price, bs.quantity, (s.price*bs.quantity) AS total
                          FROM booking_services bs
                          JOIN services s ON bs.service_id = s.id
                          WHERE bs.booking_id = ?");
    $sv->bind_param('i', $id);
    $sv->execute();
    $services = $sv->get_result();

    while ($row = $services->fetch_assoc()) {
        $pdf->Cell(120,8,iconv('UTF-8','CP1258',$row['name'].' x'.$row['quantity']),1);
        $pdf->Cell(40,8,number_format($row['total']),1,1,'R');
        $serviceTotal += $row['total'];
    }

    $pdf->SetFont('DejaVu','B',11);
    $pdf->Cell(120,8,iconv('UTF-8','CP1258','Tổng cộng'),1);
    $pdf->Cell(40,8,number_format($booking['total_price'] + $serviceTotal),1,1,'R');
}

// ================= SERVICE ORDER INVOICE =================
if ($type === 'service') {

    $stmt = $conn->prepare("SELECT o.*, u.name AS user_name, u.email
                            FROM orders o
                            JOIN users u ON o.user_id = u.id
                            WHERE o.id = ? AND o.status IN ('confirmed','completed')");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if (!$order) die(iconv('UTF-8','CP1258','Không tìm thấy đơn dịch vụ hoặc chưa đủ điều kiện xuất hóa đơn'));

    if ($_SESSION['role'] === 'user' && $_SESSION['user_id'] != $order['user_id']) {
        die(iconv('UTF-8','CP1258','Không có quyền'));
    }

    $pdf->Cell(0,8,iconv('UTF-8','CP1258','Loại hóa đơn: DỊCH VỤ'),0,1);
    $pdf->Cell(0,8,iconv('UTF-8','CP1258','Khách hàng: '.$order['user_name'].' ('.$order['email'].')'),0,1);
    if (isset($order['created_at'])) $pdf->Cell(0,8,iconv('UTF-8','CP1258','Ngày mua: '.$order['created_at']),0,1);
    $pdf->Ln(5);

    $pdf->SetFont('DejaVu','B',11);
    $pdf->Cell(80,8,iconv('UTF-8','CP1258','Dịch vụ'),1);
    $pdf->Cell(30,8,iconv('UTF-8','CP1258','Giá'),1);
    $pdf->Cell(20,8,iconv('UTF-8','CP1258','SL'),1);
    $pdf->Cell(30,8,iconv('UTF-8','CP1258','Thành tiền'),1,1,'R');

    $pdf->SetFont('DejaVu','',11);
    $items = $conn->prepare("SELECT s.name, oi.price, oi.quantity, (oi.price*oi.quantity) AS total
                             FROM order_items oi
                             JOIN services s ON oi.service_id = s.id
                             WHERE oi.order_id = ?");
    $items->bind_param('i', $id);
    $items->execute();
    $list = $items->get_result();

    while ($row = $list->fetch_assoc()) {
        $pdf->Cell(80,8,iconv('UTF-8','CP1258',$row['name']),1);
        $pdf->Cell(30,8,number_format($row['price']),1);
        $pdf->Cell(20,8,$row['quantity'],1);
        $pdf->Cell(30,8,number_format($row['total']),1,1,'R');
    }

    $pdf->SetFont('DejaVu','B',11);
    $pdf->Cell(130,8,iconv('UTF-8','CP1258','Tổng cộng'),1);
    $pdf->Cell(30,8,number_format($order['total_amount']),1,1,'R');
}

$pdf->Output('I','invoice.pdf');
exit;
