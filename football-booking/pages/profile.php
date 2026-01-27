<?php
$page_title = 'Hồ sơ cá nhân';
require_once '../includes/header.php';

if (!isLoggedIn()) redirect(SITE_URL . '/pages/login.php');

$user_id = $_SESSION['user_id'];
$user = getUserInfo($conn, $user_id);
$error = '';
$success = '';


// ==========================
// XỬ LÝ FORM CẬP NHẬT
// ==========================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);

    $password = $_POST['password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Xử lý upload avatar
    if (!empty($_FILES['avatar']['name'])) {
        $avatar_file = $_FILES['avatar'];
        $allowed_types = ['image/jpeg','image/png','image/webp'];

        if (!in_array($avatar_file['type'],$allowed_types)) {
            $error = 'Chỉ chấp nhận file ảnh JPG, PNG, WEBP';
        } else {
            $ext = pathinfo($avatar_file['name'], PATHINFO_EXTENSION);
            $avatar_name = 'avatar_'.$user_id.'_'.time().'.'.$ext;

            $upload_dir = __DIR__.'/../uploads/avatars/';
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

            move_uploaded_file($avatar_file['tmp_name'], $upload_dir.$avatar_name);

            // Lưu avatar mới vào DB
            $stmt = $conn->prepare("UPDATE users SET avt=? WHERE id=?");
            $stmt->bind_param("si", $avatar_name, $user_id);
            $stmt->execute();

            $user['avt'] = $avatar_name;
        }
    }

    // Nếu không có lỗi upload
    if (!$error) {
        if (empty($name) || empty($phone)) {
            $error = 'Vui lòng điền đầy đủ các trường';
        }
        // Đổi mật khẩu
        elseif (!empty($new_password)) {
            if (!verifyPassword($password, $user['password'])) {
                $error = 'Mật khẩu hiện tại không chính xác';
            } elseif (strlen($new_password) < 6) {
                $error = 'Mật khẩu mới phải ít nhất 6 ký tự';
            } elseif ($new_password !== $confirm_password) {
                $error = 'Mật khẩu mới không trùng khớp';
            } else {
                $hashed = hashPassword($new_password);
                $stmt = $conn->prepare("UPDATE users SET name=?, phone=?, password=? WHERE id=?");
                $stmt->bind_param("sssi", $name, $phone, $hashed, $user_id);
                $stmt->execute();
                $success = 'Cập nhật thành công!';
            }
        }
        // Chỉ đổi thông tin
        else {
            $stmt = $conn->prepare("UPDATE users SET name=?, phone=? WHERE id=?");
            $stmt->bind_param("ssi", $name, $phone, $user_id);
            $stmt->execute();
            $success = 'Cập nhật thành công!';
        }

        // Reload user
        $user = getUserInfo($conn, $user_id);
    }
}


// ======================================
// LẤY LỊCH SỬ ĐẶT SÂN
// ======================================
$stmt1 = $conn->prepare("
    SELECT 
        b.id AS trans_id,
        f.name AS item_name,
        b.booking_date,
        b.start_time,
        b.end_time,
        b.total_price
    FROM bookings b
    JOIN fields f ON b.field_id = f.id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC, b.start_time DESC
");
$stmt1->bind_param("i", $user_id);
$stmt1->execute();
$booking_history = $stmt1->get_result()->fetch_all(MYSQLI_ASSOC);


// ======================================
// LẤY LỊCH SỬ MUA DỊCH VỤ
// ======================================
$stmt2 = $conn->prepare("
    SELECT 
        oi.id AS trans_id,
        s.name AS item_name,
        o.created_at AS date,
        (oi.price * oi.quantity) AS total
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN services s ON oi.service_id = s.id
    WHERE o.user_id = ? AND o.status = 'paid'
    ORDER BY o.created_at DESC
");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$service_history = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<!-- Giao diện -->
<div class="row mb-4">
    <div class="col-12">
        <h1><i class="bi bi-person"></i> Hồ sơ cá nhân</h1>
    </div>
</div>

<div class="row">
    <!-- Cột trái -->
    <div class="col-md-4">

        <!-- Avatar + thông tin -->
        <div class="card text-center mb-3">
            <div class="card-body">
                <img src="<?= !empty($user['avt']) ? SITE_URL.'/uploads/avatars/'.$user['avt'] : SITE_URL.'/assets/images/default.png'; ?>" 
                     alt="Avatar" class="rounded-circle mb-2" style="width:120px;height:120px;object-fit:cover;">

                <h5><?= htmlspecialchars($user['name']); ?></h5>
                <p><?= htmlspecialchars($user['email']); ?></p>
                <p><strong>Vai trò:</strong> <?= $user['role']=='admin'?'Quản lý':'Người dùng'; ?></p>
                <p><strong>Ngày tạo:</strong> <?= formatDateTime($user['created_at']); ?></p>
            </div>
        </div>

        <!-- Lịch sử giao dịch -->
        <div class="card" style="max-height:500px; overflow-y:auto;">
            <div class="card-body">
                <h5 class="mb-3">Lịch sử giao dịch</h5>

                <!-- ĐẶT SÂN -->
                <h6 class="text-primary"><i class="bi bi-calendar-check"></i> Đặt sân</h6>
                <?php if($booking_history): ?>
                    <ul class="list-group list-group-flush mb-3">
                        <?php foreach($booking_history as $b): ?>
                            <li class="list-group-item">
                                <strong><?= htmlspecialchars($b['item_name']); ?></strong><br>
                                <small>
                                    <?= date('d/m/Y', strtotime($b['booking_date'])); ?> |
                                    <?= $b['start_time']; ?> - <?= $b['end_time']; ?>
                                </small><br>
                                <span class="fw-bold text-success"><?= formatCurrency($b['total_price']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">Không có lịch sử đặt sân.</p>
                <?php endif; ?>


                <!-- MUA DỊCH VỤ -->
                <h6 class="text-primary"><i class="bi bi-bag-check"></i> Mua dịch vụ</h6>
                <?php if($service_history): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach($service_history as $s): ?>
                            <li class="list-group-item">
                                <strong><?= htmlspecialchars($s['item_name']); ?></strong><br>
                                <small><?= date('d/m/Y H:i', strtotime($s['date'])); ?></small><br>
                                <span class="fw-bold text-success"><?= formatCurrency($s['total']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">Không có lịch sử dịch vụ.</p>
                <?php endif; ?>

            </div>
        </div>

    </div>

    <!-- Cột phải - form cập nhật -->
    <div class="col-md-8">
        <?php if ($error) echo showError($error); ?>
        <?php if ($success) echo showSuccess($success); ?>

        <div class="card">
            <div class="card-body">
                <h5>Cập nhật thông tin</h5>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label>Họ tên</label>
                        <input type="text" name="name" class="form-control" 
                               value="<?= htmlspecialchars($user['name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label>Số điện thoại</label>
                        <input type="text" name="phone" class="form-control" 
                               value="<?= htmlspecialchars($user['phone']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label>Upload avatar</label>
                        <input type="file" name="avatar" class="form-control">
                    </div>

                    <hr>
                    <h6>Đổi mật khẩu (tùy chọn)</h6>

                    <div class="mb-3">
                        <label>Mật khẩu hiện tại</label>
                        <input type="password" name="password" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Mật khẩu mới</label>
                        <input type="password" name="new_password" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Xác nhận mật khẩu mới</label>
                        <input type="password" name="confirm_password" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Cập nhật
                    </button>

                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footerADMIN.php'; ?>
