<?php
$page_title = 'Đặt sân';
require_once '../includes/header.php';

if (!isLoggedIn()) {
    redirect(SITE_URL . '/pages/login.php');
}

if (!isset($_GET['field_id'])) {
    redirect(SITE_URL . '/pages/fields.php');
}

$field_id = intval($_GET['field_id']);
$field = getFieldInfo($conn, $field_id);

if (!$field) {
    redirect(SITE_URL . '/pages/fields.php');
}

$error = '';
$success = '';

// Lấy danh sách dịch vụ còn hàng
$services_result = $conn->query("SELECT * FROM services WHERE status='active' AND quantity>0 ORDER BY name ASC");
$services = [];
if ($services_result->num_rows > 0) {
    while ($row = $services_result->fetch_assoc()) {
        $services[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $booking_date = trim($_POST['booking_date']);
    $start_time = trim($_POST['start_time']);
    $end_time = trim($_POST['end_time']);
    $selected_services = $_POST['services'] ?? []; // [service_id => quantity]

    // Validation
    if (empty($booking_date) || empty($start_time) || empty($end_time)) {
        $error = 'Vui lòng điền đầy đủ các trường';
    } elseif (strtotime($booking_date) < strtotime(date('Y-m-d'))) {
        $error = 'Ngày đặt sân phải từ hôm nay trở đi';
    } elseif (strtotime($start_time) >= strtotime($end_time)) {
        $error = 'Thời gian kết thúc phải sau thời gian bắt đầu';
    } elseif (!isFieldAvailable($conn, $field_id, $booking_date, $start_time, $end_time)) {
        $error = 'Sân không còn trống vào thời gian này';
    } else {
        // Tính giá sân
        $total_price = calculatePrice($field['price_per_hour'], $start_time, $end_time);

        // Tính giá dịch vụ
        $total_service_price = 0;
        foreach ($selected_services as $service_id => $quantity) {
            $quantity = intval($quantity);
            if ($quantity <= 0) continue;
            $stmt = $conn->prepare("SELECT price, quantity FROM services WHERE id=?");
            $stmt->bind_param("i", $service_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            if ($result && $quantity <= $result['quantity']) {
                $total_service_price += $result['price'] * $quantity;
            } else {
                $error = 'Số lượng dịch vụ không đủ';
            }
        }

        if (!$error) {
            $final_price = $total_price + $total_service_price;

            // Tạo booking
            $stmt = $conn->prepare("INSERT INTO bookings (user_id, field_id, booking_date, start_time, end_time, total_price, status, created_at) 
                                  VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
            $stmt->bind_param("iisssd", $_SESSION['user_id'], $field_id, $booking_date, $start_time, $end_time, $final_price);
            $stmt->execute();
            $booking_id = $conn->insert_id;
// Cập nhật tổng chi tiêu người dùng
$user_id = $_SESSION['user_id'];
$booking_total = $final_price;

$stmt = $conn->prepare("INSERT INTO user_spending (user_id, total_booking)
    VALUES (?, ?)
    ON DUPLICATE KEY UPDATE total_booking = total_booking + VALUES(total_booking)
");
$stmt->bind_param("id", $user_id, $booking_total); 
$stmt->execute();

            // Lưu dịch vụ kèm số lượng và giảm số lượng trong DB
            foreach ($selected_services as $service_id => $quantity) {
                $quantity = intval($quantity);
                if ($quantity <= 0) continue;
                $stmt = $conn->prepare("INSERT INTO booking_services (booking_id, service_id, quantity) VALUES (?, ?, ?)");
                $stmt->bind_param("iii", $booking_id, $service_id, $quantity);
                $stmt->execute();

                $stmt = $conn->prepare("UPDATE services SET quantity = quantity - ? WHERE id = ?");
                $stmt->bind_param("ii", $quantity, $service_id);
                $stmt->execute();
            }

            redirect(SITE_URL . '/pages/booking-detail.php?id=' . $booking_id . '&msg=success');
        }
    }
}
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-calendar-plus"></i> Đặt sân: <?= htmlspecialchars($field['name']); ?></h1>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <?php if ($error) echo showError($error); ?>
        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Ngày đặt sân</label>
                                <input type="date" class="form-control" name="booking_date" min="<?= date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label>Bắt đầu</label>
                                <input type="time" class="form-control" name="start_time" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label>Kết thúc</label>
                                <input type="time" class="form-control" name="end_time" required>
                            </div>
                        </div>
                    </div>

                    <!-- Dịch vụ dạng cuộn -->
                  <!-- Dịch vụ dạng lưới với ảnh -->
<?php if ($services): ?>
    <hr>
    <h5>Dịch vụ thêm</h5>
    <div style="max-height:300px; overflow-y:auto;">
        <div class="row g-2">
            <?php foreach ($services as $service): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card text-center p-2">
                        <img src="/football-booking/uploads/services/<?= $service['image'] ?>" 
                             class="card-img-top" style="height:80px; object-fit:cover; border-radius:6px;">
                        <div class="card-body p-1">
                            <strong class="d-block"><?= htmlspecialchars($service['name']); ?></strong>
                            <span class="text-success d-block"><?= formatCurrency($service['price']); ?></span>
                            <span class="text-muted d-block" style="font-size:0.8em;">Còn: <?= $service['quantity']; ?></span>
                            <div class="mt-1">
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="addService(<?= $service['id']; ?>, <?= $service['price']; ?>, <?= $service['quantity']; ?>)">+</button>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        onclick="removeService(<?= $service['id']; ?>, <?= $service['price']; ?>)">-</button>
                                <span id="qty_<?= $service['id']; ?>">0</span>
                                <input type="hidden" name="services[<?= $service['id']; ?>]" 
                                       id="input_<?= $service['id']; ?>" value="0" 
                                       data-price="<?= $service['price']; ?>" data-stock="<?= $service['quantity']; ?>">
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>


                    <hr>
                    <h5>Tổng giá: <span id="total_price" class="text-primary fw-bold"><?= formatCurrency(0); ?></span></h5>

                    <button type="submit" class="btn btn-primary mt-3">Đặt sân</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Chi tiết sân</h5>
                <p><strong>Tên sân:</strong> <?= htmlspecialchars($field['name']); ?></p>
                <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($field['location']); ?></p>
                <p><strong>Mô tả:</strong> <?= htmlspecialchars($field['description']); ?></p>
                <p><strong>Giá sân/giờ:</strong> <span class="text-success"><?= formatCurrency($field['price_per_hour']); ?></span></p>
                <hr>
                <h6>Giá dự kiến</h6>
                <p id="estimated_price" class="fs-5 text-primary fw-bold"><?= formatCurrency(0); ?></p>
            </div>
        </div>
    </div>
</div>

<script>
function addService(id, price, maxQty) {
    const input = document.getElementById('input_'+id);
    let qty = parseInt(input.value) || 0;
    if (qty >= maxQty) return;
    qty++;
    input.value = qty;
    document.getElementById('qty_'+id).innerText = qty;
    updateTotal();
}

function removeService(id, price) {
    const input = document.getElementById('input_'+id);
    let qty = parseInt(input.value) || 0;
    if (qty <= 0) return;
    qty--;
    input.value = qty;
    document.getElementById('qty_'+id).innerText = qty;
    updateTotal();
}

function updateTotal() {
    const startTime = document.querySelector('input[name="start_time"]').value;
    const endTime = document.querySelector('input[name="end_time"]').value;
    let total = 0;

    // Giá sân
    if (startTime && endTime) {
        const start = new Date('1970-01-01 '+startTime);
        const end = new Date('1970-01-01 '+endTime);
        const hours = (end - start)/(1000*60*60);
        if (hours>0) total += hours * <?= $field['price_per_hour']; ?>;
    }

    // Giá dịch vụ
    document.querySelectorAll('input[type="hidden"][name^="services"]').forEach(input=>{
        const qty = parseInt(input.value) || 0;
        const price = parseFloat(input.getAttribute('data-price')) || 0;
        total += qty * price;
    });

    const formatted = new Intl.NumberFormat('vi-VN', {style:'currency', currency:'VND'}).format(total);
    document.getElementById('total_price').innerText = formatted;
    document.getElementById('estimated_price').innerText = formatted;
}

document.querySelector('input[name="start_time"]').addEventListener('change', updateTotal);
document.querySelector('input[name="end_time"]').addEventListener('change', updateTotal);
</script>

<?php require_once '../includes/footer.php'; ?>
