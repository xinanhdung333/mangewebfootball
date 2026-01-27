<?php
$page_title = 'Quản lý đặt sân';
require_once '../../includes/header.php';
//autoUpdateBookingStatus($conn);
if (!isLoggedIn() || $_SESSION['role'] != 'admin') {
    redirect(SITE_URL . '/pages/login.php');
}

$error = '';
$success = '';

/* ============================
   Xử lý cập nhật trạng thái
==/* ============================
   Xử lý cập nhật trạng thái
============================ */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $booking_id = intval($_POST['booking_id']);
    $new_status = trim($_POST['status']);

    // Lấy thông tin booking hiện tại (ĐỦ dữ liệu cần thiết)
    $stmt = $conn->prepare("
        SELECT user_id, total_price, status, booking_date, start_time, end_time
        FROM bookings
        WHERE id = ?
    ");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $booking_data = $stmt->get_result()->fetch_assoc();

    if (!$booking_data) {
        $error = 'Không tìm thấy đơn đặt sân!';
    } else {
        $current_status = $booking_data['status'];

        // thứ tự hợp lệ
        $status_order = [
            'pending'     => 1,
            'confirmed'   => 2,
            'in_progress' => 3,
            'completed'   => 4,
            'cancelled'   => 5,
            'expired'     => 6

        ];

        $allowed = false;

        // Thời gian thực tế của booking
        $has_time = !empty($booking_data['booking_date'])
                    && !empty($booking_data['start_time'])
                    && !empty($booking_data['end_time']);

        if ($has_time) {
           $start = new DateTime(
    $booking_data['booking_date'] . ' ' . $booking_data['start_time'],
    new DateTimeZone('Asia/Ho_Chi_Minh')
);

$end = new DateTime(
    $booking_data['booking_date'] . ' ' . $booking_data['end_time'],
    new DateTimeZone('Asia/Ho_Chi_Minh')
);


        }$now = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));



        // ==============================
        //  QUY TẮC CHUYỂN TRẠNG THÁI
        // ==============================

        if ($current_status === 'completed') {
            $error = "Đơn đã hoàn thành, không thể thay đổi!";
        }
        elseif ($current_status === 'cancelled') {
            $error = "Đơn đã hủy, không thể thay đổi!";
        }
        elseif ($new_status === 'cancelled') {
            // Hủy luôn được phép
            $allowed = true;
        }
        elseif ($current_status === 'pending' && $new_status === 'confirmed') {
            $allowed = true;
        }
        elseif ($new_status === 'in_progress') {
            if ($has_time && $now >= $start) {
                $allowed = true;
            } else {
                $error = "Chưa đến giờ bắt đầu trận!";
            }
        }
        elseif ($new_status === 'completed') {
            if ($has_time && $now >= $end) {
                $allowed = true;
            } else {
                $error = "Trận đấu chưa kết thúc!";
            }
        }
        else {
            // Cấm quay lùi trạng thái
            if ($status_order[$new_status] < $status_order[$current_status]) {
                $error = "Không thể quay ngược trạng thái!";
            } else {
                $allowed = true;
            }
        }

        if ($allowed) {
            // Cập nhật trạng thái
            $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $new_status, $booking_id);

            if ($stmt->execute()) {

                // Admin xác nhận thì cộng tiền
                if ($new_status === 'completed' && $current_status !== 'completed') {
                    $user_id = $booking_data['user_id'];
                    $amount  = $booking_data['total_price'];

                    $conn->query("
                        INSERT INTO user_spending (user_id, total_booking)
                        VALUES ($user_id, $amount)
                        ON DUPLICATE KEY UPDATE total_booking = total_booking + $amount
                    ");
                }

                $success = 'Cập nhật trạng thái thành công!';
            } else {
                $error = 'Có lỗi xảy ra!';
            }
        }
    }
}



/* ============================
    Lọc theo trạng thái
============================ */
$filter_status = $_GET['status'] ?? '';

$where = "";
if ($filter_status !== "") {
    $where = "WHERE b.status = '" . $conn->real_escape_string($filter_status) . "'";
}

/* ============================
    Lấy danh sách booking
============================ */
$bookings = $conn->query("
    SELECT b.*, f.name as field_name, u.name as user_name, u.phone as user_phone
    FROM bookings b
    JOIN fields f ON b.field_id = f.id
    JOIN users u ON b.user_id = u.id
    $where
    ORDER BY b.created_at DESC
");
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-calendar"></i> Quản lý đặt sân</h1>
    </div>
</div>

<?php if ($error) echo showError($error); ?>
<?php if ($success) echo showSuccess($success); ?>

<!-- ============================
        Bộ lọc trạng thái
============================ -->
<form method="GET" class="mb-3" style="max-width: 300px;">
    <select name="status" class="form-select" onchange="this.form.submit()">
        <option value="">-- Tất cả --</option>
        <option value="pending" <?= $filter_status=='pending'?'selected':'' ?>>Chờ xác nhận</option>
        <option value="confirmed" <?= $filter_status=='confirmed'?'selected':'' ?>>Xác nhận</option>
        <option value="in_progress" <?= $filter_status=='in_progress'?'selected':'' ?>>Đang diễn ra</option>
        <option value="completed" <?= $filter_status=='completed'?'selected':'' ?>>Hoàn thành</option>
        <option value="cancelled" <?= $filter_status=='cancelled'?'selected':'' ?>>Hủy</option>
    </select>
</form>

<div class="row">
    <div class="col-md-12">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Khách hàng</th>
                        <th>Sân</th>
                        <th>Ngày</th>
                        <th>Thời gian</th>
                        <th>Giá</th>
                        <th>Trạng thái</th>
                        <th>Dịch vụ</th>

                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>

                <?php if ($bookings->num_rows > 0): ?>
                    <?php while ($booking = $bookings->fetch_assoc()): ?>

                        <tr>
                            <td>#<?php echo $booking['id']; ?></td>

                            <td>
                                <strong><?php echo htmlspecialchars($booking['user_name']); ?></strong><br>
                                <small><?php echo $booking['user_phone']; ?></small>
                            </td>

                            <td><?php echo htmlspecialchars($booking['field_name']); ?></td>

                            <td><?php echo date('d/m/Y', strtotime($booking['booking_date'])); ?></td>

                            <td><?php echo $booking['start_time'] . ' - ' . $booking['end_time']; ?></td>

                            <td><?php echo formatCurrency($booking['total_price']); ?></td>
                            
                            <td>
                                <?php 
                                    $statusMap = [
                                        'pending'        => ['warning', 'Chờ xác nhận'],
                                        'confirmed'      => ['primary', 'Đã xác nhận'],
                                        'in_progress'    => ['secondary', 'Đang diễn ra'],
                                        'completed'      => ['success', 'Hoàn thành'],
                                        'cancelled'      => ['dark', 'hủy'],
                                        'expired'       => ['danger', 'Hết hạn'],

                                    ];

                                    $color = $statusMap[$booking['status']][0];
                                    $label = $statusMap[$booking['status']][1];
                                ?>

                                <span class="badge bg-<?php echo $color; ?>">
                                    <?php echo $label; ?>
                                </span>
                            </td>
<td>
  <button class="btn btn-info btn-sm" 
        data-bs-toggle="modal" 
        data-bs-target="#serviceModal<?php echo $booking['id']; ?>">
    Xem
</button>

</td>

                            <td>
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">

                                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">

                                        <?php foreach ($statusMap as $key => $value): ?>
                                            <option value="<?= $key ?>" <?= $booking['status']==$key?'selected':'' ?>>
                                       <?= $value[1] ?>
                                            </option>
                                        <?php endforeach; ?>

                                    </select>
                                </form>
                            </td>
                        </tr>
<!-- Modal xem dịch vụ -->
<div class="modal fade" id="serviceModal<?php echo $booking['id']; ?>" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title">Dịch vụ đã chọn</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <?php
            $bid = $booking['id'];
            $sql_sv = "
                SELECT s.name, s.price, s.image, bs.quantity
                FROM booking_services bs
                JOIN services s ON bs.service_id = s.id
                WHERE bs.booking_id = $bid
            ";
            $sv = $conn->query($sql_sv);

            if ($sv->num_rows > 0):
                while ($row = $sv->fetch_assoc()):
        ?>

        <div class="d-flex align-items-center border-bottom py-2">

            <!-- Ảnh dịch vụ -->
<img src="<?= SITE_URL . '/uploads/services/' . $row['image']; ?>" 
                 alt="<?= $row['name']; ?>" 
                 style="width: 70px; height: 70px; object-fit: cover; border-radius:5px; margin-right: 15px;">

            <div>
                <strong><?= $row['name']; ?></strong> x <?= $row['quantity']; ?><br>
                <small><?= number_format($row['price'], 0, ',', '.'); ?>đ</small>
            </div>

        </div>

        <?php
                endwhile;
            else:
                echo "<p class='text-muted'>Không có dịch vụ nào.</p>";
            endif;
        ?>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
      </div>

    </div>
  </div>
</div>


                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="8" class="text-center">Không có đặt sân nào</td>
                    </tr>

                <?php endif; ?>

                </tbody>
            </table>
        </div>
    </div>
</div>
 

 