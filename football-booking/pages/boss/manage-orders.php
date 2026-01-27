<?php
$page_title = 'Quản lý doanh thu';
require_once '../../includes/header.php';

if (!isLoggedIn() || $_SESSION['role'] != 'boss') {
    redirect(SITE_URL . '/pages/login.php');
}

/* ============================
    LỌC THEO USER
============================ */
$filter_user = $_GET['user_id'] ?? '';

$where = "";
if ($filter_user !== "") {
    $where = "WHERE us.user_id = " . intval($filter_user);
}

/* ============================
    LẤY DỮ LIỆU DOANH THU
============================ */
$sql = "
    SELECT 
        us.id,
        us.user_id,
        us.total_booking,
        us.total_services,
        us.total_spent,
        us.last_update,
        u.name AS user_name,
        u.phone AS user_phone
    FROM user_spending us
    LEFT JOIN users u ON us.user_id = u.id
    $where
    ORDER BY us.last_update DESC
";

$orders = $conn->query($sql);
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-cash-stack"></i> Quản lý doanh thu</h1>
    </div>
</div>

<!-- Bộ lọc -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Lọc theo người dùng</label>
                <select name="user_id" class="form-select">
                    <option value="">-- Tất cả --</option>
                    <?php
                    $users = $conn->query("SELECT id, name FROM users ORDER BY name ASC");
                    while ($u = $users->fetch_assoc()):
                    ?>
                        <option value="<?php echo $u['id']; ?>" 
                            <?php echo ($filter_user == $u['id']) ? 'selected' : ''; ?>>
                            <?php echo $u['name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Lọc
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bảng dữ liệu -->
<div class="card">
    <div class="card-header">
        <h5>Danh sách doanh thu</h5>
    </div>

    <div class="card-body table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Người dùng</th>
                    <th>SĐT</th>
                    <th>Đặt sân</th>
                    <th>Dịch vụ</th>
                    <th>Tổng chi tiêu</th>
                    <th>Cập nhật lần cuối</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders->num_rows > 0): ?>
                    <?php while ($row = $orders->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td><?php echo $row['user_name'] ?: 'Không rõ'; ?></td>
                            <td><?php echo $row['user_phone'] ?: '---'; ?></td>
                            <td><?php echo formatCurrency($row['total_booking']); ?></td>
                            <td><?php echo formatCurrency($row['total_services']); ?></td>
                            <td><strong><?php echo formatCurrency($row['total_spent']); ?></strong></td>
                            <td><?php echo $row['last_update']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">Không có dữ liệu</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../includes/footerADMIN.php'; ?>
