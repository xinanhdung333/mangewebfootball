<?php
require_once '../includes/header.php';

if (!isLoggedIn()) {
    redirect(SITE_URL . '/pages/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_item_id'])) {
    $cart_item_id = intval($_POST['cart_item_id']);

    // Lấy cart_id trước khi xóa item
    $stmt = $conn->prepare("SELECT cart_id FROM cart_items WHERE id=?");
    $stmt->bind_param("i", $cart_item_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $cart_id = $result['cart_id'] ?? 0;

    if ($cart_id) {
        // Xóa item
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE id=?");
        $stmt->bind_param("i", $cart_item_id);
        $stmt->execute();

        // Cập nhật tổng tiền cart
    $stmt = $conn->prepare("SELECT SUM(quantity * price) AS total FROM cart_items WHERE cart_id=?");
$stmt->bind_param("i", $cart_id);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

    }

    redirect('cart.php');
} else {
    redirect('cart.php');
}
?>
