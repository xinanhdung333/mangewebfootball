<?php
$page_title = 'Lịch sử dịch vụ đã mua';
require_once '../../includes/header.php';

if (!isLoggedIn()) {
    redirect(SITE_URL . '/pages/login.php');
}

$user_id = $_SESSION['user_id']; 
$is_admin = ($_SESSION['role'] == 'admin');

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

/* SQL for admin */
if ($is_admin) {
    $sql = "
        SELECT 
            o.id AS order_id, 
            o.created_at, 
            o.status,
            oi.quantity,
            oi.price,
            s.name,
            s.image,
            u.name AS user_name
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN services s ON oi.service_id = s.id
        JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
    ";
}
/* SQL for user */
else {
    $sql = "
        SELECT 
            o.id AS order_id, 
            o.created_at, 
            o.status,
            oi.quantity,
            oi.price,
            s.name,
            s.image
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN services s ON oi.service_id = s.id
        WHERE o.user_id = $user_id
        ORDER BY o.created_at DESC
    ";
}

$data = $conn->query($sql);
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-bag-check"></i> Lịch sử dịch vụ đã mua</h1>
    </div>
</div>

<div class="table-responsive">
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <?php if ($is_admin): ?>
                <th>Khách hàng</th>
            <?php endif; ?>

            <th>Ảnh</th>
            <th>Dịch vụ</th>
            <th>Số lượng</th>
            <th>Thành tiền</th>
            <th>Ngày mua</th>
            <th>Trạng thái</th>

            <?php if ($is_admin): ?>
                <th>Sửa</th>
            <?php endif; ?>
        </tr>
    </thead>

    <tbody>
        <?php while ($row = $data->fetch_assoc()): ?>
            <tr>

                <?php if ($is_admin): ?>
                    <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                <?php endif; ?>

                <td>
                    <img src="<?php echo SITE_URL; ?>/uploads/services/<?php echo $row['image']; ?>" width="60">
                </td>

                <td><?php echo htmlspecialchars($row['name']); ?></td>

                <td><?php echo $row['quantity']; ?></td>

                <td><?php echo formatCurrency($row['quantity'] * $row['price']); ?></td>

                <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>

                <td>
                    <span class="badge bg-<?php echo getStatusColor($row['status']); ?>">
                        <?php echo ucfirst($row['status']); ?>
                    </span>
                </td>

                <?php if ($is_admin): ?>
                <td>
                    <a href="edit-status.php?id=<?php echo $row['order_id']; ?>" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil-square"></i> Sửa
                    </a>
                </td>
                <?php endif; ?>

            </tr>
        <?php endwhile; ?>
    </tbody>

</table>
</div>

<?php require_once '../../includes/footerADMIN.php'; ?> 