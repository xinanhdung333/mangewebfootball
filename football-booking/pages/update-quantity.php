<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

if(!isLoggedIn()){
    echo json_encode(['success'=>false,'message'=>'Chưa đăng nhập']);
    exit;
}

$user_id = $_SESSION['user_id'];

$cart_item_id = intval($_POST['cart_item_id'] ?? 0);
$quantity     = intval($_POST['quantity'] ?? 1);

if ($cart_item_id <= 0) {
    echo json_encode(['error' => 'invalid_id']);
    exit;
}

if ($quantity < 1) $quantity = 1;


// ==============================
// LẤY ITEM + KIỂM TRA THUỘC USER
// ==============================
$stmt = $conn->prepare("
    SELECT ci.cart_id, ci.price, c.user_id
    FROM cart_items ci
    JOIN cart c ON ci.cart_id = c.id
    WHERE ci.id=? LIMIT 1
");
$stmt->bind_param("i", $cart_item_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    echo json_encode(['error' => 'item_not_found']);
    exit;
}

if ($item['user_id'] != $user_id) {
    echo json_encode(['error' => 'no_permission']);
    exit;
}

$cart_id = $item['cart_id'];
$price   = $item['price'];


// ==============================
// CẬP NHẬT SỐ LƯỢNG
// ==============================
$stmt = $conn->prepare("
    UPDATE cart_items 
    SET quantity=?, updated_at=NOW() 
    WHERE id=?
");
$stmt->bind_param("ii", $quantity, $cart_item_id);
$stmt->execute();


// ==============================
// TÍNH LẠI TỔNG ITEM + TỔNG GIỎ HÀNG
// ==============================
$item_total = $price * $quantity;

$stmt = $conn->prepare("
    SELECT SUM(quantity * price) AS total
    FROM cart_items 
    WHERE cart_id=?
");
$stmt->bind_param("i", $cart_id);
$stmt->execute();
$cart_total = $stmt->get_result()->fetch_assoc()['total'] ?? 0;


// ==============================
// TRẢ DỮ LIỆU JSON CHO AJAX
// ==============================
echo json_encode([
    'success'     => true,
    'new_quantity'=> $quantity,
    'item_total'  => $item_total,
    'cart_total'  => $cart_total
]);
exit;
?>
