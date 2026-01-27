<?php
$page_title = 'Quản lý dịch vụ';
require_once '../../includes/header.php';
//autoUpdateBookingStatus($conn);
if (!isLoggedIn() || $_SESSION['role'] != 'admin') {
    redirect(SITE_URL . '/pages/login.php');
}

$error = '';
$success = '';

// Tạo thư mục upload nếu chưa có
$upload_dir = __DIR__ . '/../../uploads/services/';
if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

// ================== XÓA DỊCH VỤ ==================
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $service_id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
    $stmt->bind_param("i", $service_id);

    if ($stmt->execute()) {
        $success = 'Xóa dịch vụ thành công!';
    } else {
        $error = 'Có lỗi xảy ra!';
    }
}

// ================== THÊM DỊCH VỤ ==================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $status = $_POST['status'];
    $quantity = intval($_POST['quantity']);

    // Upload ảnh
    $image_name = null;
    if (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = time() . "_" . rand(1000,9999) . "." . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name);
    }

    $stmt = $conn->prepare("INSERT INTO services (name, price, image, status, quantity, created_at) 
                            VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sdssi", $name, $price, $image_name, $status, $quantity);

    if ($stmt->execute()) {
        header("Location: manage-services.php?added=1");
        exit;
    } else {
        $error = 'Có lỗi xảy ra!';
    }
}

// ================== SỬA DỊCH VỤ ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {

    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $status = $_POST['status'];
    $quantity = intval($_POST['quantity']);

    $oldImage = $_POST['old_image'] ?? '';
    $newImage = $oldImage;

    if (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $newImage = time() . "_" . rand(1000,9999) . "." . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $newImage);

        $sql = "UPDATE services 
                SET name=?, price=?, status=?, quantity=?, image=?
                WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdsssi", $name, $price, $status, $quantity, $newImage, $id);
    } else {
        $sql = "UPDATE services 
                SET name=?, price=?, status=?, quantity=?
                WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdssi", $name, $price, $status, $quantity, $id);
    }

    if ($stmt->execute()) {
        header("Location: manage-services.php?updated=1");
        exit;
    } else {
        echo "SQL Error: " . $stmt->error;
    }
}

// ================== LẤY DANH SÁCH DỊCH VỤ ==================
$services = $conn->query("SELECT * FROM services ORDER BY name ASC");
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-grid"></i> Quản lý dịch vụ</h1>
    </div>
</div>

<?php if ($error) echo showError($error); ?>
<?php if ($success) echo showSuccess($success); ?>

<div class="row mb-4">
    <div class="col-md-12">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addServiceModal">
            <i class="bi bi-plus-lg"></i> Thêm dịch vụ
        </button>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Tên dịch vụ</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($services->num_rows > 0): ?>
                        <?php while ($service = $services->fetch_assoc()): ?>
                            <tr>
                                <td><?= $service['id']; ?></td>
                                <td>
                                    <?php if ($service['image']): ?>
                                        <img src="/football-booking/uploads/services/<?= $service['image']; ?>?t=<?= filemtime($upload_dir . $service['image']); ?>" 
                                             style="width:60px;height:60px;object-fit:cover;border-radius:6px;">
                                    <?php else: ?>
                                        <span class="text-muted">Không có</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($service['name']); ?></td>
                                <td><?= formatCurrency($service['price']); ?></td>
                                <td><?= $service['quantity']; ?></td>
                                <td>
                                    <?php if ($service['status'] == 'active'): ?>
                                        <span class="badge bg-success">Hoạt động</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Ngưng hoạt động</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editServiceModal"
                                            onclick='editService(<?= json_encode($service); ?>)'>
                                        <i class="bi bi-pencil"></i> Sửa
                                    </button>
                                    <a class="btn btn-sm btn-danger"
                                       onclick="return confirm('Bạn chắc chắn muốn xóa?')"
                                       href="?action=delete&id=<?= $service['id']; ?>">
                                        <i class="bi bi-trash"></i> Xóa
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">Không có dịch vụ nào</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL THÊM DỊCH VỤ -->
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm dịch vụ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">

                    <div class="mb-3">
                        <label>Tên dịch vụ</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label>Giá</label>
                        <input type="number" class="form-control" name="price" step="1000" required>
                    </div>

                    <div class="mb-3">
                        <label>Số lượng</label>
                        <input type="number" class="form-control" name="quantity" min="0" value="0" required>
                    </div>

                    <div class="mb-3">
                        <label>Trạng thái</label>
                        <select name="status" class="form-control">
                            <option value="active">Đang hoạt động</option>
                            <option value="inactive">Ngưng hoạt động</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Ảnh</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button class="btn btn-primary">Thêm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL SỬA DỊCH VỤ -->
<div class="modal fade" id="editServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa dịch vụ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="old_image" id="edit_old_image">

                    <div class="mb-3">
                        <label>Tên dịch vụ</label>
                        <input id="edit_name" name="name" type="text" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Giá</label>
                        <input id="edit_price" name="price" type="number" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Số lượng</label>
                        <input id="edit_quantity" name="quantity" type="number" class="form-control" min="0">
                    </div>

                    <div class="mb-3">
                        <label>Trạng thái</label>
                        <select id="edit_status" name="status" class="form-control">
                            <option value="active">Đang hoạt động</option>
                            <option value="inactive">Ngưng hoạt động</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Ảnh hiện tại</label><br>
                        <img id="edit_preview" src="" style="width:100px;height:100px;border-radius:6px;object-fit:cover;">
                    </div>

                    <div class="mb-3">
                        <label>Đổi ảnh</label>
                        <input type="file" name="image" class="form-control" accept="image/*" id="edit_image_input">
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editService(service) {
    document.getElementById('edit_id').value = service.id;
    document.getElementById('edit_name').value = service.name;
    document.getElementById('edit_price').value = service.price;
    document.getElementById('edit_quantity').value = service.quantity || 0;
    document.getElementById('edit_status').value = service.status;

    document.getElementById('edit_old_image').value = service.image || '';
    document.getElementById('edit_preview').src =
        service.image ? "/football-booking/uploads/services/" + service.image + "?t=" + new Date().getTime() : "";

    document.getElementById('edit_image_input').value = "";
}

// Preview khi chọn file mới
document.getElementById('edit_image_input').addEventListener('change', function(event) {
    const [file] = event.target.files;
    if (file) {
        document.getElementById('edit_preview').src = URL.createObjectURL(file);
    }
});
</script>



<?php require_once '../../includes/footerADMIN.php'; ?>
    