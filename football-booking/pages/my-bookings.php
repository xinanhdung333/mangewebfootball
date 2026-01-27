<?php
$page_title = 'Đặt sân của tôi';
require_once '../includes/header.php';

if (!isLoggedIn()) {
    redirect(SITE_URL . '/pages/login.php');
}

$user_id = $_SESSION['user_id'];
$bookings = getUserBookings($conn, $user_id);

// Lọc theo trạng thái
$status_filter = $_GET['status'] ?? 'all';
if ($status_filter != 'all') {
    $bookings = array_filter($bookings, function($b) use ($status_filter) {
        return $b['status'] == $status_filter;
    });
}

// Lấy dịch vụ cho từng booking
$booking_services_map = [];
if ($bookings) {
    $booking_ids = array_column($bookings, 'id');
    $ids_placeholder = implode(',', array_fill(0, count($booking_ids), '?'));
    $types = str_repeat('i', count($booking_ids));

    $stmt = $conn->prepare("SELECT bs.booking_id, s.name, s.image, bs.quantity 
                            FROM booking_services bs
                            JOIN services s ON bs.service_id = s.id
                            WHERE bs.booking_id IN ($ids_placeholder)");
    $stmt->bind_param($types, ...$booking_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $booking_services_map[$row['booking_id']][] = $row;
    }
}
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-calendar"></i> Đặt sân của tôi</h1>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="btn-group" role="group">
            <a href="?status=all" class="btn btn-<?php echo $status_filter == 'all' ? 'primary' : 'outline-primary'; ?>">Tất cả</a>
            <a href="?status=pending" class="btn btn-<?php echo $status_filter == 'pending' ? 'warning' : 'outline-warning'; ?>">Chờ xác nhận</a>
            <a href="?status=confirmed" class="btn btn-<?php echo $status_filter == 'confirmed' ? 'success' : 'outline-success'; ?>">Xác nhận</a>
            <a href="?status=cancelled" class="btn btn-<?php echo $status_filter == 'cancelled' ? 'danger' : 'outline-danger'; ?>">Hủy</a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <?php if ($bookings): ?>
  <div class="table-responsive rounded-3 overflow-hidden">
    <table class="table table-striped table-hover mb-0">

                    <thead>
                        <tr style="background-color: #061625ff;">
                            <th>ID</th>
                            <th>Sân</th>
                            <th>Ngày</th>
                            <th>Thời gian</th>
                            <th>Giá</th>
                            <th>Dịch vụ đã mua</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td>#<?= $booking['id']; ?></td>
                                <td><?= htmlspecialchars($booking['field_name']); ?></td>
                                <td><?= date('d/m/Y', strtotime($booking['booking_date'])); ?></td>
                                <td><?= $booking['start_time'].' - '.$booking['end_time']; ?></td>
                                <td><?= formatCurrency($booking['total_price']); ?></td>
                                <td>
                                    <?php if (!empty($booking_services_map[$booking['id']])): ?>
                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#servicesModal<?= $booking['id']; ?>">
                                            Xem dịch vụ
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">Không có</span>
                                    <?php endif; ?>
                                </td>
                             <td>
                                <?php 
                                    $statusMap = [
                                        'pending'        => ['warning', 'Chờ xác nhận'],
                                        'confirmed'      => ['primary', 'Đã xác nhận'],
                                        'in_progress'     => ['secondary', 'Đang diễn ra'],
                                        'completed'      => ['success', 'Hoàn thành'],
                                        'cancelled'      => ['dark', 'hủy'],
                                        'expired'        => ['danger', 'Hết hạn'],
                                    ];

                                    $color = $statusMap[$booking['status']][0];
                                    $label = $statusMap[$booking['status']][1];
                                ?>

                                <span class="badge bg-<?php echo $color; ?>">
                                    <?php echo $label; ?>
                                </span>
                            </td>

                                <td>
                                    <a href="booking-detail.php?id=<?= $booking['id']; ?>" class="btn btn-sm btn-info">Chi tiết</a>
                                </td>
                            </tr>

                            <!-- Modal dịch vụ -->
                            <?php if (!empty($booking_services_map[$booking['id']])): ?>
                            <div class="modal fade" id="servicesModal<?= $booking['id']; ?>" tabindex="-1" aria-labelledby="servicesModalLabel<?= $booking['id']; ?>" aria-hidden="true">
                              <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="servicesModalLabel<?= $booking['id']; ?>">Dịch vụ đã đặt - Booking #<?= $booking['id']; ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                                  </div>
                                  <div class="modal-body">
                                    <?php foreach ($booking_services_map[$booking['id']] as $svc): ?>
                                        <div class="d-flex align-items-center mb-2">
                                            <img src="<?= !empty($svc['image']) ? SITE_URL.'/uploads/services/'.$svc['image'] : SITE_URL.'/assets/images/default.png'; ?>" 
                                                 alt="<?= htmlspecialchars($svc['name']); ?>" style="width:50px;height:50px;object-fit:cover;border-radius:6px;margin-right:10px;">
                                            <div>
                                                <strong><?= htmlspecialchars($svc['name']); ?></strong><br>
                                                Số lượng: <?= $svc['quantity']; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                  </div>
                                  <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                Bạn chưa có đặt sân nào. <a href="fields.php">Đặt sân ngay</a>
            </div>
        <?php endif; ?>
    </div>
</div>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br><br>
<br>
<br>
><br>
<br>


<?php require_once '../includes/footer.php'; ?>
