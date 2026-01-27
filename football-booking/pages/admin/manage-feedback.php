<?php
require_once '../../includes/header.php';

if (!isLoggedIn() || $_SESSION['role'] != 'admin') {
    redirect(SITE_URL . '/pages/login.php');
}

// ===== LẤY FEEDBACK DỊCH VỤ: CHỈ LẤY NHỮNG CÁI CÓ FEEDBACK =====
$services = $conn->query("
    SELECT 
        f.id AS feedback_id,
        u.name AS user_name,
        s.id AS service_id,
        s.name AS service_name,
        s.image AS service_image,
        f.message AS feedback_message,
        f.rating AS feedback_rating,
        f.created_at
    FROM feedback f
    JOIN services s ON s.id = f.service_id
    JOIN users u ON u.id = f.user_id
    WHERE f.service_id IS NOT NULL
    ORDER BY f.id DESC
");

$hasServiceFb = $services->num_rows > 0;

$bookings = $conn->query("
    SELECT 
        fb.id AS feedback_id,
        b.id AS booking_id,
        u.name AS user_name,
        f.name AS field_name,
        f.image AS field_image,
        b.booking_date,
        b.start_time,
        b.end_time,
        fb.message AS feedback_message,
        fb.rating AS feedback_rating,
        fb.created_at  -- thêm dòng này
    FROM feedback fb
    JOIN bookings b ON b.id = fb.booking_id
    JOIN users u ON u.id = fb.user_id
    JOIN fields f ON f.id = b.field_id
    ORDER BY fb.id DESC
");


$hasBookingFb = $bookings->num_rows > 0;

?>

<h1>Feedback khách hàng</h1>

<ul class="nav nav-tabs" id="feedbackTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="service-tab" data-bs-toggle="tab" data-bs-target="#serviceTab">Dịch vụ</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="booking-tab" data-bs-toggle="tab" data-bs-target="#bookingTab">Booking sân</button>
  </li>
</ul>

<div class="tab-content mt-3">

  <!-- ================= TAB DỊCH VỤ ================= -->
  <div class="tab-pane fade show active" id="serviceTab">
      <div class="table-responsive">

      <?php if (!$hasServiceFb): ?>
        <div class="alert alert-info mt-3">Không có feedback dịch vụ.</div>
      <?php else: ?>

      <table class="table table-striped">
        <thead>
        <tr>
    <th>#ID</th>
    <th>Người dùng</th>
    <th>Tên dịch vụ</th>
    <th>Ảnh</th>
    <th>Ngày Feedback</th>
    <th>Feedback</th>
    <th>Rating</th>
</tr>

        </thead>

        <tbody>
         <?php while($service = $services->fetch_assoc()): ?>
<tr>
    <td><?= $service['service_id'] ?></td>

    <td><?= htmlspecialchars($service['user_name']) ?></td>

    <td><?= htmlspecialchars($service['service_name']) ?></td>

    <td>
        <?php if($service['service_image']): ?>
            <img src="<?= SITE_URL.'/uploads/services/'.$service['service_image'] ?>" 
                 style="width:60px;height:60px;object-fit:cover;">
        <?php endif; ?>
    </td>

<td><?= date('d/m/Y H:i:s', strtotime($service['created_at'])) ?></td>

    <td><?= htmlspecialchars($service['feedback_message']) ?></td>

    <td>
        <?php
        if ($service['feedback_rating']) {
            $stars = str_repeat('★', $service['feedback_rating']) . str_repeat('☆', 5 - $service['feedback_rating']);
            echo '<span style="color:gold;">'.$stars.'</span>';
        }
        ?>
    </td>
</tr>
<?php endwhile; ?>

        </tbody>

      </table>

      <?php endif; ?>
      </div>
  </div>
<!-- ================= TAB BOOKING ================= -->
<div class="tab-pane fade" id="bookingTab">
    <div class="table-responsive">

    <?php if (!$hasBookingFb): ?>
        <div class="alert alert-info mt-3">Không có feedback booking sân.</div>
    <?php else: ?>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>#ID</th>
            <th>Người dùng</th>
            <th>Tên sân</th>
            <th>Ảnh</th>
            <th>Ngày Feedback</th>
            <th>Feedback</th>
            <th>Rating</th>
        </tr>
        </thead>

        <tbody>
        <?php while($booking = $bookings->fetch_assoc()): ?>
        <tr>
            <td><?= $booking['booking_id'] ?></td>

            <td><?= htmlspecialchars($booking['user_name']) ?></td>

            <td><?= htmlspecialchars($booking['field_name']) ?></td>

            <td>
                <?php if($booking['field_image']): ?>
                    <img src="<?= SITE_URL.'/uploads/fields/'.$booking['field_image'] ?>" 
                        style="width:60px;height:60px;object-fit:cover;">
                <?php endif; ?>
            </td>

         <td><?= date('d/m/Y H:i:s', strtotime($booking['created_at'])) ?></td>


            <td><?= htmlspecialchars($booking['feedback_message']) ?></td>

            <td>
                <?php
                if ($booking['feedback_rating']) {
                    $stars = str_repeat('★', $booking['feedback_rating']) . 
                             str_repeat('☆', 5 - $booking['feedback_rating']);
                    echo '<span style="color:gold;">'.$stars.'</span>';
                }
                ?>
            </td>

        </tr>
        <?php endwhile; ?>
        </tbody>

    </table>

    <?php endif; ?>
    </div>
</div>


<?php require_once '../../includes/footerADMIN.php'; ?>
