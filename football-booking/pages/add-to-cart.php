<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Phần code như bạn viết


if (!isLoggedIn()) {
    echo json_encode(['success'=>false, 'error'=>'Vui lòng đăng nhập']);
    exit;
}

$user_id = $_SESSION['user_id'];
$service_id = intval($_POST['service_id'] ?? 0);
$quantity = max(1, intval($_POST['quantity'] ?? 1));
// Lấy thông tin dịch vụ
$stmt = $conn->prepare("SELECT price, quantity FROM services WHERE id=?");
$stmt->bind_param("i", $service_id);
$stmt->execute();
$service = $stmt->get_result()->fetch_assoc();

if (!$service || $service['quantity'] <= 0) {
    echo json_encode(['success'=>false, 'error'=>'Dịch vụ không tồn tại hoặc hết hàng.']);
    exit;
}


if ($quantity > $service['quantity']) $quantity = $service['quantity'];

// Lấy giỏ hàng hiện tại (giả sử giỏ cuối cùng của user)
$stmt = $conn->prepare("SELECT id FROM cart WHERE user_id=? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart = $stmt->get_result()->fetch_assoc();

if (!$cart) {
    $stmt = $conn->prepare("INSERT INTO cart (user_id) VALUES (?)");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cart_id = $conn->insert_id;
} else {
    $cart_id = $cart['id'];
}

// Kiểm tra item có trong giỏ chưa
$stmt = $conn->prepare("SELECT id, quantity FROM cart_items WHERE cart_id=? AND service_id=?");
$stmt->bind_param("ii", $cart_id, $service_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if ($item) {
    $new_qty = $item['quantity'] + $quantity;
    $stmt = $conn->prepare("UPDATE cart_items SET quantity=? WHERE id=?");
    $stmt->bind_param("ii", $new_qty, $item['id']);
    $stmt->execute();
} else {
    $stmt = $conn->prepare("INSERT INTO cart_items (cart_id, service_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $cart_id, $service_id, $quantity, $service['price']);
    $stmt->execute();
}


// Lấy tổng số item trong giỏ
$stmt = $conn->prepare("SELECT SUM(quantity) AS total_items FROM cart_items WHERE cart_id=?");
$stmt->bind_param("i", $cart_id);
$stmt->execute();
$total_items = $stmt->get_result()->fetch_assoc()['total_items'] ?? 0;

echo json_encode(['success'=>true,'total_items'=>$total_items]);
