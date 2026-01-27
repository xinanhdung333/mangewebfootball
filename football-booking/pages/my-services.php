<?php
$page_title = 'Danh sách dịch vụ của bạn';
require_once '../includes/header.php';

if (!isLoggedIn()) {
    redirect(SITE_URL . '/pages/login.php');
}

$user_id = $_SESSION['user_id']; 

// Lấy danh sách dịch vụ user đã mua (giả sử bảng order_items liên kết user)
$sql = "
SELECT 
    o.id AS order_id,
    s.id,
    s.name,
    s.image,
    s.price,
    oi.quantity,
    oi.created_at,
    o.status
FROM order_items oi
JOIN orders o ON oi.order_id = o.id
JOIN services s ON oi.service_id = s.id
WHERE o.user_id = $user_id
ORDER BY oi.created_at DESC
";

$data = $conn->query($sql);

/* Badge màu theo trạng thái */
function getStatusColor($status) {
    return [
        'pending'      => 'secondary',
        'confirmed'    => 'info',
        'processing'   => 'primary',
        'completed'    => 'success',
        'cancelled'    => 'danger'
    ][$status] ?? 'dark';
}
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-bag-check"></i> Dịch vụ của bạn</h1>
    </div>
</div>

<div class="table-responsive">
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>Ảnh</th>
            <th>Dịch vụ</th>
            <th>Số lượng</th>
            <th>Thành tiền</th>
            <th>Ngày mua</th>
            <th>Trạng thái</th>
        </tr>
    </thead>

    <tbody>
        <?php while ($row = $data->fetch_assoc()): ?>
            <tr>
                <td>
                    <img src="<?= SITE_URL; ?>/uploads/services/<?= $row['image']; ?>" width="60" style="border-radius:6px; object-fit:cover;">
                </td>
                <td><?= htmlspecialchars($row['name']); ?></td>
                <td><?= $row['quantity']; ?></td>
                <td><?= number_format($row['quantity'] * $row['price'],0,',','.'); ?>₫</td>
                <td><?= date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                <td>
                    <span class="badge bg-<?= getStatusColor($row['status']); ?>">
                        <?= ucfirst($row['status']); ?>
                        </span>
                   <span style="
    background:#0d6efd;
    padding:4px 10px;
    border-radius:6px;
">
    <a href="order-detail.php?id=<?= $row['order_id']; ?>"
       class="history-btn"
       style="color:#fff; text-decoration:none;">
        Xem chi tiết
    </a>
</span>

                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
</div>

<?php require_once '../includes/footer.php'; ?>
