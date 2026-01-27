<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isLoggedIn()) {
    redirect(SITE_URL . '/pages/login.php');
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['selected_items'])) {
    $selected = json_decode($_POST['selected_items'], true);

    if (!empty($selected)) {
        // Lấy cart_id từ 1 item bất kỳ
        $stmt = $conn->prepare("SELECT cart_id FROM cart_items WHERE id=? LIMIT 1");
        $stmt->bind_param("i", $selected[0]);
        $stmt->execute();
        $cart_row = $stmt->get_result()->fetch_assoc();
        $cart_id = $cart_row['cart_id'] ?? 0;

        if ($cart_id) {
            // Tính tổng tiền từ các item đã chọn
            $placeholders = implode(',', array_fill(0, count($selected), '?'));
            $types = str_repeat('i', count($selected));
            $stmt_total = $conn->prepare("SELECT SUM(quantity * price) as total FROM cart_items WHERE id IN ($placeholders)");
            $stmt_total->bind_param($types, ...$selected);
            $stmt_total->execute();
            $total = $stmt_total->get_result()->fetch_assoc()['total'] ?? 0;
foreach ($selected as $cart_item_id) {

    // Lấy thông tin item
    $stmt_item = $conn->prepare("SELECT service_id, quantity, price, cart_id 
                                 FROM cart_items WHERE id=?");
    $stmt_item->bind_param("i", $cart_item_id);
    $stmt_item->execute();
    $item = $stmt_item->get_result()->fetch_assoc();

    if ($item) {

        // Tạo order riêng cho từng item
        $stmt_order = $conn->prepare("
            INSERT INTO orders (user_id, cart_id, total_amount, created_at, status)
            VALUES (?, ?, ?, NOW(), 'pending')
        ");
        $total = $item['quantity'] * $item['price'];
        $stmt_order->bind_param("iid", $user_id, $item['cart_id'], $total);
        $stmt_order->execute();
        $order_id = $conn->insert_id;

        // Thêm item vào order_items
        $stmt_item_insert = $conn->prepare("
            INSERT INTO order_items (order_id, service_id, quantity, price, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt_item_insert->bind_param("iiid", 
            $order_id, $item['service_id'], $item['quantity'], $item['price']
        );
        $stmt_item_insert->execute();
    }

    // Xóa item khỏi cart
    $stmt_delete = $conn->prepare("DELETE FROM cart_items WHERE id=?");
    $stmt_delete->bind_param("i", $cart_item_id);
    $stmt_delete->execute();
}

        }
    }
    header("Location: cart.php");
    exit;
} else {
    redirect('cart.php');
}
?>
