<?php
require_once '../../includes/headerVisitor.php';

$services = $conn->query("
    SELECT 
        u.id   AS user_id,
        u.name AS user_name,
        u.avatar AS user_avatar,
        s.name AS service_name,
        s.image AS service_image,
        f.message AS feedback_message,
        f.rating AS feedback_rating,
        f.created_at AS feedback_date
    FROM feedback f
    JOIN users u     ON u.id = f.user_id
    JOIN services s  ON s.id = f.service_id
    WHERE f.service_id IS NOT NULL
    ORDER BY f.created_at DESC
");



$hasServiceFb = $services->num_rows > 0;

// ===== LẤY FEEDBACK BOOKING: CHỈ LẤY NHỮNG CÁI CÓ FEEDBACK =====
$bookings = $conn->query("
    SELECT 
        b.id AS booking_id,
        u.name AS user_name,
        u.avatar AS user_avatar,
        f.name AS field_name,
        b.booking_date,
        b.start_time,
        b.end_time,
        fb.message AS feedback_message,
        fb.rating AS feedback_rating,
        fb.created_at AS feedback_date
    FROM bookings b
    JOIN users u ON u.id = b.user_id
    JOIN fields f ON f.id = b.field_id
    JOIN feedback fb ON fb.booking_id = b.id
    ORDER BY fb.created_at DESC
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
  <th>Khách hàng</th>
  <th>Dịch vụ</th>
  <th>Ảnh</th>
  <th>Feedback</th>
  <th>Rating</th>
  <th>Ngày</th>
</tr>


        </thead>
<tbody>
<?php while ($row = $services->fetch_assoc()): ?>
<tr>
  <td>
    <img 
      src="/uploads/avatars/<?= $row['user_avatar'] ?: 'default.png' ?>" 
      width="32" height="32"
      class="rounded-circle me-2">
    <?= htmlspecialchars(mb_substr($row['user_name'], 0, 1)) ?>***
  </td>

  <td><?= htmlspecialchars($row['service_name']) ?></td>

  <td>
    <img 
      src="/uploads/services/<?= htmlspecialchars($row['service_image']) ?>" 
      width="60">
  </td>

  <td><?= nl2br(htmlspecialchars($row['feedback_message'])) ?></td>

  <td>
    <?= str_repeat('⭐', (int)$row['feedback_rating']) ?>
  </td>

  <td>
    <?= date('d/m/Y', strtotime($row['feedback_date'])) ?>
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
            <th>Sân</th>
            <th>Ngày</th>
            <th>Thời gian</th>
            <th>Feedback</th>
            <th>Rating</th>
          </tr>
        </thead>
<tbody>
<?php while ($b = $bookings->fetch_assoc()): ?>
<tr>
  <td>
    <img 
      src="/uploads/avatars/<?= $b['user_avatar'] ?: 'default.png' ?>" 
      width="32" height="32"
      class="rounded-circle me-2">
    <?= htmlspecialchars(mb_substr($b['user_name'], 0, 1)) ?>***
  </td>

  <td><?= htmlspecialchars($b['field_name']) ?></td>

  <td><?= date('d/m/Y', strtotime($b['booking_date'])) ?></td>

  <td><?= $b['start_time'].' - '.$b['end_time'] ?></td>

  <td><?= nl2br(htmlspecialchars($b['feedback_message'])) ?></td>

  <td><?= str_repeat('⭐', (int)$b['feedback_rating']) ?></td>

  <td><?= date('d/m/Y', strtotime($b['feedback_date'])) ?></td>
</tr>
<?php endwhile; ?>
</tbody>


      </table>

      <?php endif; ?>
      </div>
  </div>

</div>

<?php require_once '../../includes/footer.php'; ?>
 