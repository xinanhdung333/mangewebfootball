<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect(SITE_URL . '/pages/login.php');
}

$user_id = $_SESSION['user_id'];
$success = '';
$error   = '';

/* ================== XỬ LÝ SUBMIT FEEDBACK ================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['feedback_type'])) {

    $message = trim($_POST['message']);
    $rating  = (int)$_POST['rating'];
    $type    = $_POST['feedback_type'];
    $item_id = (int)$_POST['item_id'];

    if ($message === '' || $rating < 1 || $rating > 5) {
        $error = "Vui lòng nhập đầy đủ feedback và rating từ 1-5";
    } else {

        /* ========= FEEDBACK DỊCH VỤ ========= */
        if ($type === 'service') {

            // item_id = order_items.id → lấy service_id
            $stmt = $conn->prepare("
                SELECT s.id AS service_id
                FROM order_items oi
                JOIN services s ON oi.service_id = s.id
                JOIN orders o ON o.id = oi.order_id
                WHERE oi.id = ? 
                  AND o.user_id = ?
                  AND o.status = 'completed'
            ");
            $stmt->bind_param("ii", $item_id, $user_id);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();

            if (!$res) {
                $error = "Dịch vụ không tồn tại!";
            } else {
                $service_id = $res['service_id'];

                $stmt = $conn->prepare("
                    INSERT INTO feedback (user_id, service_id, message, rating)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->bind_param("iisi", $user_id, $service_id, $message, $rating);
            }

        /* ========= FEEDBACK BOOKING ========= */
        } elseif ($type === 'booking') {

            $stmt = $conn->prepare("
                SELECT id 
                FROM bookings 
                WHERE id = ? 
                  AND user_id = ? 
                  AND status = 'completed'
            ");
            $stmt->bind_param("ii", $item_id, $user_id);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 0) {
                $error = "Booking không tồn tại!";
            } else {
                $stmt = $conn->prepare("
                    INSERT INTO feedback (user_id, booking_id, message, rating)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->bind_param("iisi", $user_id, $item_id, $message, $rating);
            }
        }
    }

    if (!$error && isset($stmt)) {
        if ($stmt->execute()) {
            $success = "Gửi feedback thành công!";
        } else {
            $error = "Có lỗi xảy ra: " . $stmt->error;
        }
    }
}

/* ================== DANH SÁCH DỊCH VỤ ĐÃ MUA ================== */
$sql = "
SELECT 
    oi.id AS order_item_id,
    s.name AS service_name,
    s.image AS service_image,
    (oi.quantity * oi.price) AS total,
    f.message AS feedback_message,
    f.rating AS feedback_rating
FROM order_items oi
JOIN orders o   ON o.id = oi.order_id
JOIN services s ON s.id = oi.service_id
LEFT JOIN feedback f 
    ON f.service_id = s.id 
   AND f.user_id = $user_id
WHERE o.status = 'completed'
  AND o.user_id = $user_id
ORDER BY oi.id DESC
";

$services = $conn->query($sql);

/* ================== DANH SÁCH BOOKING ================== */
$bookings = $conn->query("
    SELECT 
        b.id AS booking_id,
        f.name AS field_name,
        f.image AS field_image,
        b.booking_date,
        b.start_time,
        b.end_time,
        fb.message AS feedback_message,
        fb.rating AS feedback_rating
    FROM bookings b
    JOIN fields f ON f.id = b.field_id
    LEFT JOIN feedback fb 
        ON fb.booking_id = b.id 
       AND fb.user_id = $user_id
    WHERE b.user_id = $user_id
      AND b.status = 'completed'
    ORDER BY b.created_at DESC
");
?>

<h1>Feedback của bạn</h1>

<?php if ($error): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<?php if ($success): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<ul class="nav nav-tabs">
  <li class="nav-item">
    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#serviceTab">Dịch vụ</button>
  </li>
  <li class="nav-item">
    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#bookingTab">Booking sân</button>
  </li>
</ul>

<div class="tab-content mt-3">

<!-- ================= TAB DỊCH VỤ ================= -->
<div class="tab-pane fade show active" id="serviceTab">
<table class="table table-striped">
<thead>
<tr>
  <th>#ID</th>
  <th>Dịch vụ</th>
  <th>Ảnh</th>
  <th>Tổng tiền</th>
  <th>Feedback</th>
  <th>Rating</th>
  <th>Gửi</th>
</tr>
</thead>
<tbody>
<?php while ($s = $services->fetch_assoc()): ?>
<tr>
<td><?= $s['order_item_id'] ?></td>
<td><?= htmlspecialchars($s['service_name']) ?></td>
<td>
<?php if ($s['service_image']): ?>
<img src="<?= SITE_URL.'/uploads/services/'.$s['service_image'] ?>" width="60">
<?php endif; ?>
</td>
<td><?= number_format($s['total']) ?> đ</td>
<td><?= htmlspecialchars($s['feedback_message'] ?? '') ?></td>
<td>
<?php
if ($s['feedback_rating']) {
    echo '<span style="color:gold">' .
         str_repeat('★', $s['feedback_rating']) .
         str_repeat('☆', 5 - $s['feedback_rating']) .
         '</span>';
}
?>
</td>
<td>
<?php if (!$s['feedback_message']): ?>
<form method="post">
<input type="hidden" name="feedback_type" value="service">
<input type="hidden" name="item_id" value="<?= $s['order_item_id'] ?>">
<input type="text" name="message" class="form-control mb-1" placeholder="Viết feedback..." required>
<select name="rating" class="form-control mb-1" required>
<option value="">Chọn rating</option>
<option value="1">1 ★</option>
<option value="2">2 ★</option>
<option value="3">3 ★</option>
<option value="4">4 ★</option>
<option value="5">5 ★</option>
</select>
<button class="btn btn-sm btn-primary">Gửi</button>
</form>
<?php else: ?>
<span>Đã gửi</span>
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>

<!-- ================= TAB BOOKING ================= -->
<div class="tab-pane fade" id="bookingTab">
<table class="table table-striped">
<thead>
<tr>
  <th>#ID</th>
  <th>Sân</th>
  <th>Ảnh</th>
  <th>Ngày</th>
  <th>Thời gian</th>
  <th>Feedback</th>
  <th>Rating</th>
  <th>Gửi</th>
</tr>
</thead>
<tbody>
<?php while ($b = $bookings->fetch_assoc()): ?>
<tr>
<td><?= $b['booking_id'] ?></td>
<td><?= htmlspecialchars($b['field_name']) ?></td>
<td>
<?php if ($b['field_image']): ?>
<img src="<?= SITE_URL.'/uploads/fields/'.$b['field_image'] ?>" width="60">
<?php endif; ?>
</td>
<td><?= date('d/m/Y', strtotime($b['booking_date'])) ?></td>
<td><?= $b['start_time'].' - '.$b['end_time'] ?></td>
<td><?= htmlspecialchars($b['feedback_message'] ?? '') ?></td>
<td>
<?php
if ($b['feedback_rating']) {
    echo '<span style="color:gold">' .
         str_repeat('★', $b['feedback_rating']) .
         str_repeat('☆', 5 - $b['feedback_rating']) .
         '</span>';
}
?>
</td>
<td>
<?php if (!$b['feedback_message']): ?>
<form method="post">
<input type="hidden" name="feedback_type" value="booking">
<input type="hidden" name="item_id" value="<?= $b['booking_id'] ?>">
<input type="text" name="message" class="form-control mb-1" placeholder="Viết feedback..." required>
<select name="rating" class="form-control mb-1" required>
<option value="">Chọn rating</option>
<option value="1">1 ★</option>
<option value="2">2 ★</option>
<option value="3">3 ★</option>
<option value="4">4 ★</option>
<option value="5">5 ★</option>
</select>
<button class="btn btn-sm btn-primary">Gửi</button>
</form>
<?php else: ?>
<span>Đã gửi</span>
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>

</div>

<?php require_once '../includes/footer.php'; ?>
