<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hàm kiểm tra đăng nhập
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

function isAdmin() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}
// Hàm chuyển hướng
function redirect($url) {
    header("Location: $url");
    exit();
}

// Hàm hiển thị thông báo lỗi
function showError($message) {
    return '<div class="alert alert-danger alert-dismissible fade show" role="alert">
        ' . htmlspecialchars($message) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
}

// Hàm hiển thị thông báo thành công
function showSuccess($message) {
    return '<div class="alert alert-success alert-dismissible fade show" role="alert">
        ' . htmlspecialchars($message) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
}

// Hàm mã hóa password
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Hàm kiểm tra password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Hàm lấy thông tin user
function getUserInfo($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Hàm escape string
function escape($conn, $string) {
    return $conn->real_escape_string($string);
}

// Hàm kiểm tra email tồn tại
function emailExists($conn, $email) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

// Hàm định dạng tiền tệ
function formatCurrency($amount) {
    return number_format($amount, 0, ',', '.') . ' VNĐ';
}

// Hàm định dạng ngày giờ
function formatDateTime($datetime) {
    return date('d/m/Y H:i', strtotime($datetime));
}



// Hàm lấy thông tin sân
function getFieldInfo($conn, $field_id) {
    $stmt = $conn->prepare("SELECT * FROM fields WHERE id = ?");
    $stmt->bind_param("i", $field_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Hàm lấy danh sách đặt sân của user
function getUserBookings($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT b.*, f.name as field_name, f.price_per_hour
        FROM bookings b
        JOIN fields f ON b.field_id = f.id
        WHERE b.user_id = ?
        ORDER BY b.booking_date DESC, b.start_time DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
// Hàm kiểm tra sân còn trống
function isFieldAvailable($conn, $field_id, $date, $start_time, $end_time, $exclude_booking_id = null) {

    $query = "SELECT COUNT(*) as count 
              FROM bookings 
              WHERE field_id = ?
                AND booking_date = ?
                AND status != 'cancelled'
                AND (
                    start_time < ? 
                    AND end_time > ?
                )";

    if ($exclude_booking_id) {
        $query .= " AND id != ?";
    }

    $stmt = $conn->prepare($query);

    if ($exclude_booking_id) {
        $stmt->bind_param("isssi", 
            $field_id, 
            $date, 
            $end_time,  // end_time của booking mới
            $start_time, // start_time của booking mới
            $exclude_booking_id
        );
    } else {
        $stmt->bind_param("isss", 
            $field_id, 
            $date, 
            $end_time, 
            $start_time
        );
    }

    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['count'] == 0;
}

// Hàm tính giá tiền
function calculatePrice($price_per_hour, $start_time, $end_time) {
    $start = strtotime($start_time);
    $end = strtotime($end_time);
    $hours = ($end - $start) / 3600;
    return $hours * $price_per_hour;
}
// Hàm lấy danh sách sân + rating
function getAllFields($conn) {
    $sql = "
        SELECT  
            f.*,
            COALESCE(AVG(fe.rating), 0) AS avg_rating,
            COALESCE(COUNT(fe.id), 0) AS total_reviews
        FROM fields f
        LEFT JOIN bookings b ON b.field_id = f.id
        LEFT JOIN feedback fe ON fe.booking_id = b.id
        WHERE f.status = 'active'
        GROUP BY f.id
        ORDER BY f.name ASC
    ";

    $result = $conn->query($sql);

    $fields = [];
    while ($row = $result->fetch_assoc()) {
        $fields[] = $row;
    }

    return $fields;
}
// function lấy list services với rating + số đánh giá
function getAllServices($conn) {
    $sql = "
      SELECT
        s.*,
        COALESCE(AVG(fe.rating), 0) AS avg_rating,
        COALESCE(COUNT(fe.id), 0) AS total_reviews
      FROM services s
      LEFT JOIN feedback fe ON fe.service_id = s.id
      WHERE s.status = 'active' AND s.quantity > 0
      GROUP BY s.id
      ORDER BY s.name ASC
    ";
    $result = $conn->query($sql);
    $services = [];
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
    return $services;
}
// Hàm kiểm tra sân có đang mở cửa không

/** 
function isFieldOpen($field) {
    $now = new DateTime();
    $open  = new DateTime($field['open_time']);
    $close = new DateTime($field['close_time']);

    // Nếu giờ đóng cửa qua nửa đêm
    if ($close <= $open) {
        return ($now >= $open || $now <= $close);
    }
    return ($now >= $open && $now <= $close);
}

// Ví dụ sử dụng:
$field = ['open_time'=>'08:00', 'close_time'=>'22:00'];
if (isFieldOpen($field)) {
    echo "Sân đang mở, bạn có thể đặt.";
} else {
    echo "Sân hiện đang đóng cửa.";
}
*/
// Hàm tự động cập nhật trạng thái booking
/** 
function autoUpdateBookingStatus($conn) {
    $conn->query("
        UPDATE bookings
        SET status = 'in_progress'
        WHERE status = 'confirmed' 
          AND CONCAT(booking_date, ' ', start_time) <= NOW()
          AND CONCAT(booking_date, ' ', end_time) >= NOW()
    ");
    $conn->query("
        UPDATE bookings
        SET status = 'completed'
        WHERE status = 'in_progress' 
          AND CONCAT(booking_date, ' ', end_time) < NOW()
    ");
}
*/
?>
