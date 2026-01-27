<?php
$page_title = 'Xuất hóa đơn';
require_once '../../includes/header.php';

if (!isLoggedIn() || $_SESSION['role'] != 'admin') {
    redirect(SITE_URL . '/pages/login.php');
}

$bookings = $conn->query("
    SELECT b.id, b.booking_date, b.status, u.name
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    WHERE b.status IN ('confirmed','completed')
")->fetch_all(MYSQLI_ASSOC);

$orders = $conn->query("
    SELECT o.id, o.created_at, o.status, u.name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.status IN ('confirmed','completed')
")->fetch_all(MYSQLI_ASSOC);

/* ================== HTML TAB UI SAMPLE ==================
 * File goi: pages/admin/invoices.php
 * Trang nay chi de hien thi & bam nut xuat PDF
 * ======================================================= */
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xuất hóa đơn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h3 class="mb-3">Xuất hóa đơn</h3>

    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#booking">Booking</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#service">Service</button>
        </li>
    </ul>

    <div class="tab-content mt-3">
        <!-- BOOKING TAB -->
        <div class="tab-pane fade show active" id="booking">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Khách</th>
                        <th>Ngày</th>
                        <th>Trạng thái</th>
                        <th>Hóa đơn</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td><?= $b['id'] ?></td>
                        <td><?= $b['name'] ?></td>
                        <td><?= $b['booking_date'] ?></td>
                        <td><?= $b['status'] ?></td>
                        <td>
                            <a target="_blank" class="btn btn-sm btn-primary"
                               href="../../includes/export_invoice.php?type=booking&id=<?= $b['id'] ?>">
                               Xuất hóa đơn
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- SERVICE TAB -->
        <div class="tab-pane fade" id="service">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Khách</th>
                        <th>Ngày</th>
                        <th>Trạng thái</th>
                        <th>Hóa đơn</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                    <tr>
                        <td><?= $o['id'] ?></td>
                        <td><?= $o['name'] ?></td>
                        <td><?= $o['created_at'] ?></td>
                        <td><?= $o['status'] ?></td>
                        <td>
                            <a target="_blank" class="btn btn-sm btn-success"
                               href="../../includes/export_invoice.php?type=service&id=<?= $o['id'] ?>">
                               Xuất PDF
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

