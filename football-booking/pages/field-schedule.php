<?php
$page_title = 'Lịch đặt sân';
require_once '../includes/header.php';

if (!isLoggedIn()) {
    redirect(SITE_URL . '/pages/login.php');
}

// Lấy tất cả sân
$fields = getAllFields($conn);

// Lấy tất cả booking đã xác nhận (hoặc chờ) cho các sân
$booking_map = []; // [field_id => [booking1, booking2,...]]
$stmt = $conn->prepare("SELECT b.id, b.field_id, b.booking_date, b.start_time, b.end_time, b.status, u.name AS user_name 
                        FROM bookings b
                        JOIN users u ON b.user_id = u.id
                        WHERE b.status IN ('pending','confirmed')
                        ORDER BY b.booking_date ASC, b.start_time ASC");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $booking_map[$row['field_id']][] = $row;
}
?>
<style>
.glass-header {
    background: rgba(255, 255, 255, 0.15) !important; /* bắt buộc ghi đè bootstrap */
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 4px 10px rgba(0,0,0,0.25);
    color: #fff !important;
}
.card {
    overflow: visible !important;
    background: rgba(255, 255, 255, 0.05); /* nhẹ nhẹ cho hợp glass */
    border: 1px solid rgba(255,255,255,0.1);
}


</style>
<div class="row mb-4">
         <div class="col-12">
        <h1><i class="bi bi-calendar-check"></i> Lịch đặt sân</h1>
        <p>Danh sách toàn bộ khung giờ đã được đặt của các sân.</p>
    </div>
</div>

<?php if ($fields): ?>
    <?php foreach ($fields as $field): ?>
        <div class="card mb-3">
    <div class="card-header glass-header">
    <h5 class="mb-0"><?= htmlspecialchars($field['name']); ?> - <?= htmlspecialchars($field['location']); ?></h5>
</div>


            <div class="card-body">
                <?php if (!empty($booking_map[$field['id']])): ?>
                    <div class="table-responsive">
                        <table class="table table-striped card-header glass-header">
                            <thead>
                                <tr>
                                    <th>Ngày</th>
                                    <th>Bắt đầu</th>
                                    <th>Kết thúc</th>
                                    <th>Người đặt</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($booking_map[$field['id']] as $booking): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($booking['booking_date'])); ?></td>
                                        <td><?= $booking['start_time']; ?></td>
                                        <td><?= $booking['end_time']; ?></td>
                                        <td><?= htmlspecialchars($booking['user_name']); ?></td>
                                        <td>
                                            <span class="badge bg-<?= $booking['status']=='confirmed'?'success':'warning'; ?>">
                                                <?= $booking['status']=='confirmed'?'Xác nhận':'Chờ xác nhận'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">Hiện chưa có khung giờ nào được đặt cho sân này.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="alert alert-info">Hiện tại không có sân nào.</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
