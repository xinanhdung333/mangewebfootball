<?php
$page_title = 'Chi tiết đặt sân';
require_once '../includes/header.php';

if (!isLoggedIn()) {
    redirect(SITE_URL . '/pages/login.php');
}

if (!isset($_GET['id'])) {
    redirect(SITE_URL . '/pages/my-bookings.php');
}

$booking_id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT b.*, f.name as field_name, f.price_per_hour, f.image 
                        FROM bookings b
                        JOIN fields f ON b.field_id = f.id
                        WHERE b.id = ? AND b.user_id = ?");
$stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    redirect(SITE_URL . '/pages/my-bookings.php');
}

$error = '';
$success = '';

/* XỬ LÝ HỦY CHỈ CHO PENDING */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'cancel') {

    if ($booking['status'] == 'pending') {

        $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param("i", $booking_id);

        if ($stmt->execute()) {
            $success = 'Đặt sân đã được hủy!';
            $booking['status'] = 'cancelled';
        } else {
            $error = 'Có lỗi xảy ra!';
        }

    } else {
        $error = 'Không thể hủy vì sân đã được xử lý (đã xác nhận / đang diễn ra / hoàn thành).';
    }
}

if (isset($_GET['msg']) && $_GET['msg'] == 'success') {
    echo showSuccess('Đặt sân thành công! Vui lòng chờ xác nhận từ quản lý.');
}
?>

<div class="row mb-4">
    <div class="col-md-12">
        <a href="my-bookings.php" class="btn btn-secondary mb-3">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
        <h1>Chi tiết đặt sân #<?php echo $booking_id; ?></h1>
    </div>
</div>

<?php if ($error) echo showError($error); ?>
<?php if ($success) echo showSuccess($success); ?>

<div class="row">
    <div class="col-md-8">

        <!-- ẢNH SÂN -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Hình ảnh sân</h5>

                <?php if (!empty($booking['image'])): ?>
                    <img src="<?php echo SITE_URL . '/uploads/fields/' . $booking['image']; ?>" 
                         class="img-fluid rounded" alt="Field image">
                <?php else: ?>
                    <p class="text-muted">Không có ảnh sân</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- THÔNG TIN SÂN -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Thông tin sân</h5>
                <p><strong>Tên sân:</strong> <?php echo htmlspecialchars($booking['field_name']); ?></p>
            </div>
        </div>
        
        <!-- THÔNG TIN ĐẶT SÂN -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Thông tin đặt sân</h5>
                <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y', strtotime($booking['booking_date'])); ?></p>
                <p><strong>Thời gian:</strong> <?php echo $booking['start_time'] . ' - ' . $booking['end_time']; ?></p>
                <p><strong>Giá/giờ:</strong> <?php echo formatCurrency($booking['price_per_hour']); ?></p>
                <p><strong>Tổng giá:</strong> 
                    <span class="text-success fw-bold">
                        <?php echo formatCurrency($booking['total_price']); ?>
                    </span>
                </p>
            </div>
        </div>

    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Trạng thái</h5>
                <p>
                    <span class="badge bg-<?php 
                        echo $booking['status'] == 'confirmed' ? 'success' : 
                             ($booking['status'] == 'pending' ? 'warning' : 
                             ($booking['status'] == 'in_progress' ? 'primary' :
                             ($booking['status'] == 'completed' ? 'info' : 'danger')));
                    ?> fs-6">
                        <?php 
                        echo $booking['status'] == 'confirmed' ? 'Đã xác nhận' :
                             ($booking['status'] == 'pending' ? 'Chờ xác nhận' :
                             ($booking['status'] == 'in_progress' ? 'Đang diễn ra' :
                             ($booking['status'] == 'completed' ? 'Hoàn thành' : 'Đã hủy')));
                        ?>
                    </span>
                </p>

                <hr>
                <p><strong>Tạo lúc:</strong> <?php echo formatDateTime($booking['created_at']); ?></p>

                <!-- NÚT HỦY: CHỈ CHO PHÉP KHI PENDING -->
                <?php if ($booking['status'] == 'pending'): ?>
                    <form method="POST" action="" onsubmit="return confirm('Bạn chắc chắn muốn hủy đặt sân này?');">
                        <input type="hidden" name="action" value="cancel">
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-x-circle"></i> Hủy đặt sân
                        </button>
                    </form>
                <?php else: ?>
                    <p class="text-muted mt-2">
                        Không thể hủy vì sân đã được xử lý.
                    </p>
                <?php endif; ?>

<?php if ($booking['status'] === 'completed'): ?>
       <td>
                            <a target="_blank" class="btn btn-sm btn-primary"
                               href="<?= SITE_URL; ?>../includes/export_invoice.php?type=booking&id=<?= $booking['id'] ?>">
                               Xuất hóa đơn
                            </a>
                        </td>
<?php endif; ?>
            </div>
        </div>
    </div>
</div>

<br><br><br><br><br><br>
<?php require_once '../includes/footer.php'; ?>

