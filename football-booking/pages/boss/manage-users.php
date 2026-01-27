
<?php
$page_title = 'Quản lý người dùng (Boss)';
require_once '../../includes/header.php';

if (!isLoggedIn() || $_SESSION['role'] != 'boss') {
    redirect(SITE_URL . '/pages/login.php');
}

$error = '';
$success = '';

// ================== XÓA NGƯỜI DÙNG ==================
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $user_id = intval($_GET['id']);

    if ($user_id == $_SESSION['user_id']) {
        $error = 'Không thể xóa tài khoản Boss hiện tại!';
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            header("Location: manage-users.php?deleted=1");
            exit;
        } else {
            $error = 'Có lỗi xảy ra!';
        }
    }
}

// ================== THÊM NGƯỜI DÙNG ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = trim($_POST['role']);
    $password = password_hash("123456", PASSWORD_DEFAULT); // mật khẩu default

    $stmt = $conn->prepare("
        INSERT INTO users (name, email, phone, role, password)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssss", $name, $email, $phone, $role, $password);

    if ($stmt->execute()) {
        header("Location: manage-users.php?added=1");
        exit;
    } else {
        $error = "Lỗi khi thêm người dùng!";
    }
}

// ================== SỬA NGƯỜI DÙNG ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {

    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = trim($_POST['role']);

    $stmt = $conn->prepare("
        UPDATE users 
        SET name = ?, email = ?, phone = ?, role = ? 
        WHERE id = ?
    ");
    $stmt->bind_param("ssssi", $name, $email, $phone, $role, $id);

    if ($stmt->execute()) {
        header("Location: manage-users.php?updated=1");
        exit;
    } else {
        $error = "Lỗi khi cập nhật!";
    }
}

// ================== LẤY DANH SÁCH NGƯỜI DÙNG ==================
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");

?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-people-fill"></i> Quản lý người dùng (Boss)</h1>
    </div>
</div>

<?php 
if (isset($_GET['added'])) echo showSuccess("Thêm thành công!");
if (isset($_GET['updated'])) echo showSuccess("Cập nhật thành công!");
if (isset($_GET['deleted'])) echo showSuccess("Xóa thành công!");
if ($error) echo showError($error); 
?>

<!-- BUTTON THÊM -->
<div class="mb-3">
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="bi bi-person-plus"></i> Thêm người dùng
    </button>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>SĐT</th>
                        <th>Vai trò</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users->num_rows > 0): ?>
                        <?php while ($user = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?= $user['id']; ?></td>
                                <td><?= htmlspecialchars($user['name']); ?></td>
                                <td><?= htmlspecialchars($user['email']); ?></td>
                                <td><?= $user['phone']; ?></td>
                                <td>
                                    <span class="badge bg-<?= $user['role'] == 'boss' ? 'dark' : ($user['role'] == 'admin' ? 'danger' : 'primary'); ?>">
                                        <?= ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td><?= formatDateTime($user['created_at']); ?></td>

                                <td>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>

                                        <button class="btn btn-sm btn-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editUserModal"
                                                onclick='editUser(<?= json_encode($user); ?>)'>
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <a href="?action=delete&id=<?= $user['id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Xóa người này?')">
                                            <i class="bi bi-trash"></i>
                                        </a>

                                    <?php else: ?>
                                        <span class="badge bg-info">Boss (Bạn)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Không có người dùng nào</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<!-- =============== MODAL THÊM USER =============== -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm người dùng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="action" value="add">

                    <div class="mb-3">
                        <label>Họ tên</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Số điện thoại</label>
                        <input type="text" name="phone" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Vai trò</label>
                        <select name="role" class="form-control">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                            <option value="boss">Boss</option>
                        </select>
                    </div>

                    <p class="text-muted"><i>Mật khẩu mặc định: 123456</i></p>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- =============== MODAL SỬA USER =============== -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa người dùng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">

                    <div class="mb-3">
                        <label>Họ tên</label>
                        <input type="text" id="edit_name" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" id="edit_email" name="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Số điện thoại</label>
                        <input type="text" id="edit_phone" name="phone" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Vai trò</label>
                        <select id="edit_role" name="role" class="form-control">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                            <option value="boss">Boss</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
function editUser(user) {
    document.getElementById('edit_id').value = user.id;
    document.getElementById('edit_name').value = user.name;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_phone').value = user.phone;
    document.getElementById('edit_role').value = user.role;
}
</script>

<?php require_once '../../includes/footerADMIN.php'; ?>
