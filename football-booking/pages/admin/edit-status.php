<?php
require_once '../../includes/header.php';

if (!isLoggedIn() || $_SESSION['role'] != 'admin') {
    redirect(SITE_URL . '/pages/login.php');
}

$id = intval($_GET['id']);

// LẤY THÔNG TIN ĐƠN HÀNG
$sql = "SELECT * FROM orders WHERE id = $id";
$order = $conn->query($sql)->fetch_assoc();

if (!$order) {
    die("Đơn hàng không tồn tại");
}

// BỘ TRẠNG THÁI HỢP LỆ
$status_order = [
    'pending'     => 1,
    'confirmed'   => 2,
    'processing'  => 3,
    'completed'   => 4,
    'cancelled'   => 5
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Lấy lại trạng thái mới nhất
    $order = $conn->query("SELECT * FROM orders WHERE id=$id")->fetch_assoc();

    $current_status = $order['status'];
    $new_status = $_POST['status'];

    $current_step = $status_order[$current_status];
    $new_step = $status_order[$new_status];

    $allowed = false;

    // Chỉ pending mới được hủy
    if ($new_status === 'cancelled' && $current_status === 'pending') {
        $allowed = true;
    }
    // Chuyển đúng 1 bước: pending→confirmed →processing→completed
    elseif ($new_step === $current_step + 1) {
        $allowed = true;
    }

    if (!$allowed) {
        die("Không thể chuyển từ '$current_status' sang '$new_status'");
    }

    // UPDATE STATUS
    if (!$conn->query("UPDATE orders SET status='$new_status' WHERE id=$id")) {
        die("Lỗi SQL: " . $conn->error);
    }

    // CỘNG POINT KHI HOÀN THÀNH
    if ($new_status === 'completed' && $current_status !== 'completed') {

        $sqlOrder = "
            SELECT o.user_id, SUM(oi.price * oi.quantity) AS total_price
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            WHERE o.id = $id
            GROUP BY o.user_id
        ";
        $orderInfo = $conn->query($sqlOrder)->fetch_assoc();

        $user_id = $orderInfo['user_id'];
        $amount = $orderInfo['total_price'];

        $check = $conn->query("SELECT * FROM user_spending WHERE user_id=$user_id");

        if ($check->num_rows > 0) {
            $conn->query("
                UPDATE user_spending
                SET 
                    total_services = total_services + $amount,
                    total_spent = total_spent + $amount,
                    last_update = NOW()
                WHERE user_id = $user_id
            ");
        } else {
            $conn->query("
                INSERT INTO user_spending (user_id, total_booking, total_services, total_spent, last_update)
                VALUES ($user_id, 0, $amount, $amount, NOW())
            ");
        }
    }

    redirect(SITE_URL . '/pages/admin/user_service_history.php');
}
?>

<h2>Sửa trạng thái đơn hàng #<?php echo $id; ?></h2>

<form method="post">
    <label>Trạng thái mới</label>
    <select name="status" class="form-control" required>
        <?php
        $current = $order['status'];
        $currentStep = $status_order[$current];

        // Tạo danh sách trạng thái hợp lệ
        foreach ($status_order as $key => $step) {

            // Chỉ pending mới được hủy
            if ($key === 'cancelled' && $current !== 'pending') continue;

            // Chỉ cho phép sang đúng bước tiếp theo
            if ($step === $currentStep + 1 || ($key === 'cancelled' && $current === 'pending') || $key === $current) {

                $selected = ($key === $current) ? "selected" : "";
                echo "<option value='$key' $selected>$key</option>";
            }
        }
        ?>
    </select>

    <button type="submit" class="btn btn-primary mt-3">Lưu</button>
</form>

<?php require_once '../../includes/footerADMIN.php'; ?>
