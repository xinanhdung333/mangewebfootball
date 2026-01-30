<?php
$page_title = 'Chi tiết đơn hàng';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';

if (!isLoggedIn()) {
    redirect(SITE_URL . '/pages/login.php');
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id <= 0) {
    die('ID đơn hàng không hợp lệ');
}

/* ===============================================
   LẤY THÔNG TIN ĐƠN HÀNG
================================================ */
$stmt = $conn->prepare("
    SELECT *
    FROM orders
    WHERE id = ? AND user_id = ?
");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die('Đơn hàng không tồn tại hoặc bạn không có quyền xem');
}

/* ===============================================
   LẤY CHI TIẾT SẢN PHẨM TRONG ĐƠN
================================================ */
$stmt = $conn->prepare("
    SELECT 
        oi.quantity,
        oi.price,
        s.name,
        s.image
    FROM order_items oi
    JOIN services s ON oi.service_id = s.id
    WHERE oi.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
function statusText($status){
    return [
        'pending'    => 'Chờ xử lý',
        'confirmed'  => 'Đã xác nhận',
        'processing' => 'Đang xử lý',
        'completed'  => 'Hoàn tất',
        'cancelled'  => 'Đã hủy'
    ][$status] ?? $status;
}

?>

<div class="order-container">

    <a href="cart.php" class="back-link">← Quay lại</a>

    <h2>Chi tiết đơn hàng #<?= $order_id; ?></h2>

    <!-- ==== THÔNG TIN ĐƠN ==== -->
    <div class="order-info">
        <p><strong>Ngày đặt:</strong> <?= date("d/m/Y H:i", strtotime($order['created_at'])); ?></p>
        <p><strong>Tổng tiền:</strong> <?= formatCurrency($order['total_amount']); ?></p>
       <p>
    <strong>Trạng thái:</strong>
   <?= statusText($order['status']); ?>

</p>

<?php if ($order['status'] === 'completed'): ?>
    <a target="_blank"
       href="<?= SITE_URL; ?>../includes/export_invoice.php?type=order&id=<?= $order_id; ?>"
       class="btn btn-danger mt-2">
        <i class="bi bi-file-earmark-pdf"></i> Xuất hóa đơn
    </a>
<?php endif; ?>
    </div>

    <!-- ==== DANH SÁCH SẢN PHẨM ==== -->
    <h3>Dịch vụ đã mua</h3>

    <div class="order-items">
        <?php foreach ($items as $item): ?>
            <div class="order-item">

                <img src="<?= !empty($item['image']) 
                    ? SITE_URL.'/uploads/services/'.$item['image'] 
                    : SITE_URL.'/assets/images/default.png'; ?>">

                <div class="order-item-info">
                    <h4><?= htmlspecialchars($item['name']); ?></h4>
                    <p>Số lượng: <?= $item['quantity']; ?></p>
                    <p>Đơn giá: <?= formatCurrency($item['price']); ?></p>
                    <p>
                        <strong>
                            Thành tiền: 
                            <?= formatCurrency($item['price'] * $item['quantity']); ?>
                        </strong>
                    </p>
                </div>

            </div>
        <?php endforeach; ?>
    </div>

</div>

<style>
.order-container {
    max-width: 900px;
    margin: 30px auto;
    padding: 0 15px;
}

.back-link {
    display: inline-block;
    margin-bottom: 15px;
    text-decoration: none;
    color: #007bff;
}

.order-info {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.status.success {
    background: #28a745;
    color: #fff;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 14px;
}

.order-items {
    display: grid;
    gap: 15px;
}

.order-item {
    display: flex;
    gap: 15px;
    background: #fff;
    padding: 12px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.order-item img {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 10px;
}

.order-item-info h4 {
    margin: 0 0 5px;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
