<?php
$page_title = 'Quản lý sân';
require_once '../../includes/header.php';
//autoUpdateBookingStatus($conn);
if (!isLoggedIn() || $_SESSION['role'] != 'admin') {
    redirect(SITE_URL . '/pages/login.php');
}

$error = '';
$success = '';

// Tạo thư mục upload nếu chưa có
$upload_dir = __DIR__ . '/../../uploads/fields/';
if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

// ================== XÓA SÂN ==================
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
   $field_id = intval($_GET['id']);
$stmt = $conn->prepare("DELETE FROM fields WHERE id = ?");
$stmt->bind_param("i", $field_id);

if ($stmt->execute()) {
    $success = 'Xóa sân thành công!';
} else {
    $error = 'Có lỗi xảy ra!';
}
}


// ================== THÊM SÂN ==================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    $price_per_hour = floatval($_POST['price_per_hour']);
    $status = $_POST['status'];

    // Upload ảnh
    $image_name = null;
    if (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = time() . "_" . rand(1000,9999) . "." . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name);
    }

    $stmt = $conn->prepare("INSERT INTO fields (name, location, description, price_per_hour, image, status, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssdss", $name, $location, $description, $price_per_hour, $image_name, $status);

    if ($stmt->execute()) {
        header("Location: manage-fields.php?added=1");
        exit;
    } else {
        $error = 'Có lỗi xảy ra!';
    }
}

// ================== SỬA SÂN ==================
// ================== UPDATE FIELD ==================
// ================== UPDATE FIELD ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {

    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price_per_hour']);
    $status = $_POST['status'];

    $oldImage = $_POST['old_image'] ?? '';
    $newImage = $oldImage;

    // Nếu có ảnh mới
    if (!empty($_FILES['image']['name'])) {

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $newImage = time() . "_" . rand(1000,9999) . "." . $ext;

        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $newImage);

        $sql = "UPDATE fields 
                SET name=?, location=?, description=?, price_per_hour=?, image=?, status=?
                WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdssi", $name, $location, $description, $price, $newImage, $status, $id);
} else {

    $sql = "UPDATE fields 
            SET name=?, location=?, description=?, price_per_hour=?, status=?
            WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssdsi", $name, $location, $description, $price, $status, $id);
}

    // 🟢 Cả 2 trường hợp đều chạy execute
    if ($stmt->execute()) {
        header("Location: manage-fields.php?updated=1");
        exit;
    } else {
        echo "SQL Error: " . $stmt->error;
    }
}


// ================== LẤY DANH SÁCH SÂN ==================
$fields = $conn->query("SELECT * FROM fields ORDER BY name ASC");
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-grid"></i> Quản lý sân</h1>
    </div>
</div>

<?php if ($error) echo showError($error); ?>
<?php if ($success) echo showSuccess($success); ?>

<div class="row mb-4">
    <div class="col-md-12">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addFieldModal">
            <i class="bi bi-plus-lg"></i> Thêm sân
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
                        <th>Ảnh sân</th>
                        <th>Tên sân</th>
                        <th>Địa chỉ</th>
                        <th>Giá/giờ</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($fields->num_rows > 0): ?>
                        <?php while ($field = $fields->fetch_assoc()): ?>
                            <tr>
                                <td><?= $field['id']; ?></td>
                                <td>
                                    <?php if ($field['image']): ?>
                                        <img src="/football-booking/uploads/fields/<?= $field['image']; ?>?t=<?= filemtime($upload_dir . $field['image']); ?>" 
                                             style="width:60px;height:60px;object-fit:cover;border-radius:6px;">
                                    <?php else: ?>
                                        <span class="text-muted">Không có</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($field['name']); ?></td>
                                <td><?= htmlspecialchars($field['location']); ?></td>
                                <td><?= formatCurrency($field['price_per_hour']); ?></td>

                                <td>
                                    <?php if ($field['status'] == 'active'): ?>
                                        <span class="badge bg-success">Hoạt động</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Ngưng hoạt động</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <button class="btn btn-sm btn-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editFieldModal"
                                            onclick='editField(<?= json_encode($field); ?>)'>
                                        <i class="bi bi-pencil"></i> Sửa
                                    </button>
                                    <a class="btn btn-sm btn-danger"
                                       onclick="return confirm('Bạn chắc chắn muốn xóa?')"
                                       href="?action=delete&id=<?= $field['id']; ?>">
                                        <i class="bi bi-trash"></i> Xóa
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">Không có sân nào</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============== MODAL THÊM ============== -->
<div class="modal fade" id="addFieldModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm sân</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">

                    <div class="mb-3">
                        <label>Tên sân</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label>Địa chỉ</label>
                        <input type="text" class="form-control" name="location" required>
                    </div>

                    <div class="mb-3">
                        <label>Mô tả</label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label>Giá/giờ</label>
                        <input type="number" class="form-control" name="price_per_hour" step="1000" required>
                    </div>

                    <div class="mb-3">
                        <label>Trạng thái</label>
                        <select name="status" class="form-control">
                            <option value="active">Đang hoạt động</option>
                            <option value="inactive">Ngưng hoạt động</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Ảnh sân</label>
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

<!-- ============== MODAL SỬA ============== -->
<div class="modal fade" id="editFieldModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa sân</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="old_image" id="edit_old_image">

                    <div class="mb-3">
                        <label>Tên sân</label>
                        <input id="edit_name" name="name" type="text" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Địa chỉ</label>
                        <input id="edit_location" name="location" type="text" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Mô tả</label>
                        <textarea id="edit_description" name="description" class="form-control"></textarea>
                    </div>

                    <div class="mb-3">
                        <label>Giá/giờ</label>
                        <input id="edit_price" name="price_per_hour" type="number" class="form-control">
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
function editField(field) {
    document.getElementById('edit_id').value = field.id;
    document.getElementById('edit_name').value = field.name;
    document.getElementById('edit_location').value = field.location;
    document.getElementById('edit_description').value = field.description;
    document.getElementById('edit_price').value = field.price_per_hour;
    document.getElementById('edit_status').value = field.status;

    document.getElementById('edit_old_image').value = field.image || '';
    document.getElementById('edit_preview').src =
        field.image ? "/football-booking/uploads/fields/" + field.image + "?t=" + new Date().getTime() : "";

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
