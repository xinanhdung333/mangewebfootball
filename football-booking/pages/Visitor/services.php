<?php
$page_title = 'Dịch vụ';
include '../../includes/headerVisitor.php';
require_once '../../includes/functions.php';

// Lấy dữ liệu tìm kiếm
$search = trim($_GET['q'] ?? '');
$min_price = $_GET['min'] ?? '';
$max_price = $_GET['max'] ?? '';

// Base query
$sql = "
    SELECT 
        s.*,
        COALESCE(AVG(f.rating), 0) AS avg_rating,
        COUNT(f.id) AS total_reviews
    FROM services s
    LEFT JOIN feedback f ON f.service_id = s.id
    WHERE s.status = 'active'
      AND s.quantity > 0
";

$params = [];
$types = "";

// ==========================
//  FILTER TÊN
// ==========================
if ($search !== "") {
    $sql .= " AND s.name LIKE ? ";
    $params[] = "%$search%";
    $types .= "s";
}

// ==========================
//  FILTER GIÁ TỐI THIỂU
// ==========================
if ($min_price !== "") {
    $sql .= " AND s.price >= ? ";
    $params[] = $min_price;
    $types .= "i";
}

// ==========================
//  FILTER GIÁ TỐI ĐA
// ==========================
if ($max_price !== "") {
    $sql .= " AND s.price <= ? ";
    $params[] = $max_price;
    $types .= "i";
}

$sql .= " GROUP BY s.id ORDER BY s.name ASC";

// Nếu có dữ liệu filter → dùng prepare
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

} else {
    $result = $conn->query($sql);
}

$services = [];
while ($row = $result->fetch_assoc()) $services[] = $row;

// Lấy tổng số lượng cart
$stmt = $conn->prepare("
    SELECT SUM(ci.quantity) AS total_items
    FROM cart_items ci
    JOIN cart c ON ci.cart_id = c.id
    WHERE c.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_items = $stmt->get_result()->fetch_assoc()['total_items'] ?? 0;

?>

<!-- Giỏ hàng góc phải -->
<div style="position:relative; display:inline-block; margin-left:20px;">
    <a href="<?= SITE_URL ?>/pages/cart.php" id="cart-icon" style="position:relative; display:flex; align-items:center; text-decoration:none; color:#333; font-size:24px;">
        <i class="bi bi-cart-fill"></i>
        <?php if ($total_items > 0): ?>
            <span id="cart-count" style="position:absolute; top:-5px; right:-10px; background:red; color:white; font-size:12px; padding:2px 6px; border-radius:50%;"><?= $total_items ?></span>
        <?php else: ?>
            <span id="cart-count" style="display:none;"></span>
        <?php endif; ?>
    </a>
</div>

<div style="max-width:1200px; margin:20px auto; padding:0 10px;">
  
    <!-- FORM TÌM KIẾM -->
    <form method="GET" class="mb-3" style="display:flex; gap:10px; flex-wrap:wrap;">
        <input type="text" name="q" value="<?= htmlspecialchars($search); ?>" 
               placeholder="Tìm theo tên..." 
               style="flex:1; padding:8px; border:1px solid #ccc; border-radius:4px;">

        <input type="number" name="min" value="<?= htmlspecialchars($min_price); ?>" 
               placeholder="Giá tối thiểu" 
               style="width:150px; padding:8px; border:1px solid #ccc; border-radius:4px;">

        <input type="number" name="max" value="<?= htmlspecialchars($max_price); ?>" 
               placeholder="Giá tối đa" 
               style="width:150px; padding:8px; border:1px solid #ccc; border-radius:4px;">

        <button type="submit" style="padding:8px 15px; background:#007bff; color:white; border:none; border-radius:4px; cursor:pointer;">
            Tìm kiếm
        </button>
    </form>

    <!-- Grid sản phẩm -->
    <div class="product-grid">
        <?php foreach ($services as $service): ?>
            <div class="product-card" data-service-id="<?= $service['id']; ?>">
                <a href="service-detail.php?id=<?= $service['id']; ?>">
                    <img src="<?= !empty($service['image']) ? SITE_URL.'/uploads/services/'.$service['image'] : SITE_URL.'/assets/images/default.png'; ?>" 
                         alt="<?= htmlspecialchars($service['name']); ?>" class="product-image">
                </a>
                <div class="product-desc"><?= htmlspecialchars($service['name']); ?></div>
                <div class="product-price"><?= formatCurrency($service['price']); ?></div>
 
 <?php
      // lấy rating + total review an toàn
      $avg = isset($service['avg_rating']) ? number_format($service['avg_rating'], 1) : 0;
$total = $service['total_reviews'];
    ?>
    <div class="mb-2">
    <?php for ($i = 1; $i <= 5; $i++): ?>
        <span style="color: gold; font-size: 18px;">
            <?= ($i <= $avg) ? "★" : "☆" ?>
        </span>
    <?php endfor; ?>

    <span class="text-muted">(<?= $avg ?> / 5, <?= $total ?> đánh giá)</span>
</div>

    <button class="btn-add-cart" data-service-id="<?= $service['id']; ?>">+</button>
  </div>
<?php endforeach; ?>
</div>


<style>
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill,minmax(180px,1fr));
    gap: 15px;
}
.product-desc {
    padding: 5px 0;
    font-weight: 500;
    color: #333;
    font-size: 0.9rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.product-card {
    display: flex;
    flex-direction: column;
    text-decoration: none;
    background:#fff;
    border-radius:8px;
    overflow:hidden;
    box-shadow:0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
    text-align:center;
    position: relative;
}
.product-card:hover {
    transform: translateY(-3px);
    box-shadow:0 6px 15px rgba(0,0,0,0.15);
}
.product-image {
    width:100%;
    height:140px;
    object-fit:cover;
}
.product-price {
    padding:8px 0;
    font-weight:700;
    color:#e53935;
    font-size:1.1rem;
}
.btn-add-cart {
    margin: 5px auto 10px;
    padding: 5px 10px;
    background: #28a745;
    color: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.2rem;
    font-weight: bold;
    width: 36px;
    height: 36px;
    line-height: 26px;
    transition: transform 0.2s, background 0.2s;
}
.btn-add-cart:hover {
    background: #218838;
    transform: scale(1.2);
}
</style>

<script>
document.querySelectorAll('.btn-add-cart').forEach(btn => {
    btn.addEventListener('click', () => {
        const serviceId = btn.dataset.serviceId;

      fetch('add-to-cart.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `service_id=${serviceId}&quantity=1`
})

        .then(res => res.json())
        .then(data => {
            if(data.success){
                // Cập nhật real-time cart
                const cartCount = document.getElementById('cart-count');
                let current = parseInt(cartCount.textContent || '0');
                current += 1;
                cartCount.textContent = current;
                cartCount.style.display = 'inline-block';

                // Hiệu ứng nhấn nút
                btn.style.transform = 'scale(1.4)';
                setTimeout(() => btn.style.transform = 'scale(1.2)', 200);
            } else {
                alert(data.error || 'Lỗi thêm vào giỏ hàng!');
            }
        })
        .catch(err => alert('Lỗi kết nối AJAX!'));
    });
});
</script>
<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
<?php require_once '../../includes/footer.php'; ?>
