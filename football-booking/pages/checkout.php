<?php
require_once '../includes/header.php';

if (!isLoggedIn()) {
    redirect(SITE_URL.'/pages/login.php');
}

$user_id = $_SESSION['user_id'];

// Lấy cart mới nhất của user
$stmt = $conn->prepare("SELECT id FROM cart WHERE user_id=? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart = $stmt->get_result()->fetch_assoc();

if (!$cart) {
    echo "Giỏ hàng trống.";
    exit;
}

$cart_id = $cart['id'];

// Lấy tất cả item trong cart
$stmt = $conn->prepare("
    SELECT ci.id, ci.service_id, ci.quantity, ci.price, s.name
    FROM cart_items ci
    JOIN services s ON ci.service_id = s.id
    WHERE ci.cart_id = ?
");
$stmt->bind_param("i", $cart_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (!$items) {
    echo "Giỏ hàng trống.";
    exit;
}

// Chuẩn bị câu lệnh insert order
$stmt_order = $conn->prepare("
    INSERT INTO orders (user_id, cart_id, total_amount, status, created_at)
    VALUES (?, ?, ?, 'pending', NOW())
");

// Chuẩn bị câu lệnh insert order_item
$stmt_item = $conn->prepare("
    INSERT INTO order_items (order_id, service_id, quantity, price, created_at)
    VALUES (?, ?, ?, ?, NOW())
");

// Danh sách order ID đã tạo
$created_orders = [];

foreach ($items as $i) {

    // Tổng tiền của đơn = 1 sản phẩm × số lượng
    $total = $i['quantity'] * $i['price'];

    // Tạo đơn hàng mới
    $stmt_order->bind_param("iid", $user_id, $cart_id, $total);
    $stmt_order->execute();
    $order_id = $conn->insert_id;

    // Lưu ID để hiển thị kết quả
    $created_orders[] = [
        'order_id' => $order_id,
        'name' => $i['name'],
        'total' => $total
    ];

    // Copy item sang order_items
    $stmt_item->bind_param("iiid", $order_id, $i['service_id'], $i['quantity'], $i['price']);
    $stmt_item->execute();
}

// Xóa cart + items
$stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_id=?");
$stmt->bind_param("i", $cart_id);
$stmt->execute();

$stmt = $conn->prepare("DELETE FROM cart WHERE id=?");
$stmt->bind_param("i", $cart_id);
$stmt->execute();
?>

<div style="max-width:600px; margin:50px auto; text-align:center; padding:30px; border-radius:10px; background:#e6ffed; border:1px solid #b3f0c4;">
    <h3 style="color:#2e7d32;">✅ Thanh toán thành công!</h3>

    <p style="font-size:1.2rem;">Bạn đã tạo <strong><?= count($created_orders) ?></strong> đơn hàng riêng:</p>

    <ul style="text-align:left; font-size:1.1rem;">
        <?php foreach ($created_orders as $o): ?>
            <li>
                Đơn #<?= $o['order_id'] ?> — <?= $o['name'] ?> — 
                <strong><?= number_format($o['total']) ?> VND</strong>
            </li>
        <?php endforeach; ?>
    </ul>

    <a href="orders.php" style="margin-top:15px; display:inline-block; padding:10px 20px; background:#007bff; color:white; border-radius:5px;">Xem đơn hàng</a>
</div>
