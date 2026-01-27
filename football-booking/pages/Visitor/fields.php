<?php
$page_title = 'Danh sách sân';
include '../../includes/headerVisitor.php';
$fields = getAllFields($conn);
?>
<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-grid"></i> Danh sách sân bóng</h1>

        <!-- Thanh tìm kiếm + nút lịch đặt -->
        <div class="d-flex align-items-center mt-3">
            <input type="text" id="searchField" class="form-control me-2" placeholder="Tìm kiếm sân..." 
                   style="background: rgba(255,255,255,0.8); border:1px solid #ccc;">

            <a href="<?= SITE_URL ?>/pages/field-schedule.php" class="btn btn-info">
                <i class="bi bi-calendar-check"></i> KHUNG GIỜ ĐÃ ĐƯỢC ĐẶT
            </a>
        </div>
    </div>
</div>


<div class="row" id="fieldList">
    <?php if (count($fields) > 0): ?>
        <?php foreach ($fields as $field): ?>
            <div class="col-md-4 mb-4 field-item">
                <div class="card h-100">
                    <img src="<?php echo !empty($field['image']) ? SITE_URL . '/uploads/fields/' . $field['image'] : SITE_URL . '/assets/images/banner.jpg'; ?>" 
                        class="card-img-top" alt="<?php echo htmlspecialchars($field['name']); ?>" 
                        style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($field['name']); ?></h5>
<?php 
$desc = htmlspecialchars($field['description']);
$shortDesc = mb_strlen($desc) > 120 ? mb_substr($desc, 0, 120) . "..." : $desc;
?>

<p class="card-text description-short" id="desc-short-<?= $field['id'] ?>">
    <?= $shortDesc ?>
</p>

<p class="card-text d-none" id="desc-full-<?= $field['id'] ?>">
    <?= nl2br($desc) ?>
</p>

<?php if (mb_strlen($desc) > 120): ?>
    <span class="show-more-btn" onclick="toggleDesc(<?= $field['id'] ?>)">Xem thêm</span>
<?php endif; ?>                        <p class="text-muted mb-2">
                            <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($field['location']); ?>
                        </p>
                        <p class="text-success fw-bold mb-3">
                            <?php echo formatCurrency($field['price_per_hour']); ?>/giờ
                        </p>
                        <!-- Rating trung bình -->
<?php 
$avg = $field['avg_rating'] ? round($field['avg_rating'], 1) : 0;
$total = $field['total_reviews'];
?>

<div class="mb-2">
    <?php for ($i = 1; $i <= 5; $i++): ?>
        <span style="color: gold; font-size: 18px;">
            <?= ($i <= $avg) ? "★" : "☆" ?>
        </span>
    <?php endfor; ?>

    <span class="text-muted">(<?= $avg ?> / 5, <?= $total ?> đánh giá)</span>
</div>

                     <a href="<?= SITE_URL ?>/pages/booking.php?field_id=<?= $field['id']; ?>" class="btn btn-primary w-100">
    <i class="bi bi-calendar-plus"></i> Đặt sân
</a>

                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-md-12">
            <div class="alert alert-info">Hiện tại không có sân nào.</div>
        </div>
    <?php endif; ?>
</div>

<script>
    function toggleDesc(id) {
    const short = document.getElementById("desc-short-" + id);
    const full = document.getElementById("desc-full-" + id);
    const btn = event.target;

    if (short.classList.contains("d-none")) {
        // Đang mở -> Thu gọn
        short.classList.remove("d-none");
        full.classList.add("d-none");
        btn.innerText = "Xem thêm";
    } else {
        // Đang đóng -> Mở rộng
        short.classList.add("d-none");
        full.classList.remove("d-none");
        btn.innerText = "Thu gọn";
    }
}

// Lọc danh sách sân theo tên hoặc mô tả realtime
document.getElementById('searchField').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    document.querySelectorAll('#fieldList .field-item').forEach(item => {
        const name = item.querySelector('.card-title').innerText.toLowerCase();
        const desc = item.querySelector('.card-text').innerText.toLowerCase();
        if (name.includes(filter) || desc.includes(filter)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
});
</script>

<style>
    .description-short {
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;   /* Hiển thị 2 dòng */
    -webkit-box-orient: vertical;
}

.show-more-btn {
    color: #0d6efd;
    cursor: pointer;
    font-size: 14px;
}
</style>

<?php require_once '../../includes/footer.php'; ?>
