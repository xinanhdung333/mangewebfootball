<?php
$page_title = 'Chi tiết dịch vụ';
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect(SITE_URL . '/pages/login.php');
}

if (!isset($_GET['id'])) redirect('services.php');

$id = intval($_GET['id']);

// Lấy dịch vụ
$stmt = $conn->prepare("SELECT * FROM services WHERE id=? AND status='active'");
if (!$stmt) die("Prepare failed: " . $conn->error);
$stmt->bind_param("i", $id);
$stmt->execute();
$service = $stmt->get_result()->fetch_assoc();
if (!$service) redirect('services.php');

// Xử lý BUY NOW
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy_now'])) {

    $service_id = (int)$_POST['service_id'];
    $quantity   = (int)$_POST['quantity'];
    $user_id    = $_SESSION['user_id'];

    /* ======================
       1. Lấy thông tin service
    ====================== */
    $stmt = $conn->prepare("
        SELECT id, price, quantity 
        FROM services 
        WHERE id = ? AND status = 'active'
    ");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $service = $stmt->get_result()->fetch_assoc();

    if (!$service || $service['quantity'] < $quantity) {
        $error = "Số lượng dịch vụ không đủ!";
        return;
    }

    /* ======================
       2. Tìm order pending
    ====================== */
    $stmt = $conn->prepare("
        SELECT id 
        FROM orders 
        WHERE user_id = ? AND status = 'pending'
        ORDER BY id DESC 
        LIMIT 1
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if ($order) {
        $order_id = $order['id'];
    } else {
        /* ======================
           3. Tạo order mới
        ====================== */
        $stmt = $conn->prepare("
            INSERT INTO orders (user_id, total_amount, status, created_at)
            VALUES (?, 0, 'pending', NOW())
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $order_id = $conn->insert_id;
    }

    /* ======================
       4. Kiểm tra order_items
    ====================== */
    $stmt = $conn->prepare("
        SELECT id, quantity 
        FROM order_items 
        WHERE order_id = ? AND service_id = ?
    ");
    $stmt->bind_param("ii", $order_id, $service_id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();

    if ($item) {
        // Cộng dồn số lượng
        $new_qty = $item['quantity'] + $quantity;
        $stmt = $conn->prepare("
            UPDATE order_items 
            SET quantity = ? 
            WHERE id = ?
        ");
        $stmt->bind_param("ii", $new_qty, $item['id']);
        $stmt->execute();
    } else {
        // Thêm mới
        $stmt = $conn->prepare("
            INSERT INTO order_items (order_id, service_id, quantity, price)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "iiid",
            $order_id,
            $service_id,
            $quantity,
            $service['price']
        );
        $stmt->execute();
    }

    /* ======================
       5. Cập nhật tổng tiền order
    ====================== */
    $total_add = $quantity * $service['price'];

    $stmt = $conn->prepare("
        UPDATE orders 
        SET total_amount = total_amount + ? 
        WHERE id = ?
    ");
    $stmt->bind_param("di", $total_add, $order_id);
    $stmt->execute();

    /* ======================
       6. Trừ số lượng service
    ====================== */
    $stmt = $conn->prepare("
        UPDATE services 
        SET quantity = quantity - ? 
        WHERE id = ?
    ");
    $stmt->bind_param("ii", $quantity, $service_id);
    $stmt->execute();

    /* ======================
       7. Redirect
    ====================== */
    header("Location: service-detail.php?id={$service_id}&bought=1");
    exit;
}
?>
 
<?php require_once '../includes/header.php'; ?>

<div style="max-width:1200px; margin:30px auto; background:#fff; padding:20px; border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,0.1); font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">

    <?php if(isset($_GET['bought'])): ?>
        <div style="padding:10px; background:#d4edda; color:#155724; border-radius:8px; margin-bottom:15px;">
            Mua thành công!
        </div>
    <?php endif; ?>

    <?php if(isset($error)): ?>
        <div style="padding:10px; background:#f8d7da; color:#721c24; border-radius:8px; margin-bottom:15px;">
            <?= htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div style="display:flex; gap:20px; flex-wrap:wrap;">
        <!-- LEFT: IMAGE -->
        <div style="flex:7; min-width:300px;">
            <img id="mainImg" style="width:100%; height:480px; object-fit:contain; border:1px solid #ddd; border-radius:12px; background:#fff;" 
                 src="<?= !empty($service['image']) ? SITE_URL.'/uploads/services/'.$service['image'] : SITE_URL.'/assets/images/default.png'; ?>" 
                 alt="<?= htmlspecialchars($service['name']); ?>">
            <div id="thumbs" style="display:flex; gap:10px; margin-top:15px;">
                <img class="thumb active" src="<?= !empty($service['image']) ? SITE_URL.'/uploads/services/'.$service['image'] : SITE_URL.'/assets/images/default.png'; ?>" 
                     style="width:80px; height:80px; object-fit:cover; border:1px solid #ccc; border-radius:8px; cursor:pointer;">
            </div>
        </div>

        <!-- RIGHT: INFO -->
        <div style="flex:5; display:flex; flex-direction:column; gap:15px;">
            <h2><?= htmlspecialchars($service['name']); ?></h2>
            <div>Brand: <?= htmlspecialchars($service['brand'] ?? 'N/A'); ?></div>

            <div style="display:flex; align-items:end; gap:12px; margin-top:10px;">
                <div style="font-size:32px; font-weight:bold; color:#ff3b85;"><?= formatCurrency($service['price']); ?></div>
                <?php if(!empty($service['old_price'])): ?>
                    <div style="text-decoration:line-through; color:#666; font-size:14px;"><?= formatCurrency($service['old_price']); ?></div>
                    <div style="color:#19a463; font-size:14px; font-weight:bold;">
                        -<?= round(($service['old_price']-$service['price'])/$service['old_price']*100) ?>%
                    </div>
                <?php endif; ?>
            </div>

            <p>Delivery: <?= htmlspecialchars($service['location'] ?? 'Tỉnh/Thành phố'); ?></p>
            <p>100% Authentic • 30 Days Free Return</p>

            <h4>Quantity</h4>
            <div style="display:flex; align-items:center; gap:10px;">
                <button class="qty-btn" onclick="changeQty(-1)" style="width:32px; height:32px; border:1px solid #aaa; background:#fff; cursor:pointer;">-</button>
                <span id="qty">1</span>
                <button class="qty-btn" onclick="changeQty(1)" style="width:32px; height:32px; border:1px solid #aaa; background:#fff; cursor:pointer;">+</button>
                <span style="color:#777;">Available: <?= $service['quantity']; ?></span>
            </div>

            <div style="display:flex; gap:10px; margin-top:20px;">
                <!-- ADD TO CART (AJAX + JSON) -->
                <button id="btnAddToCart" data-id="<?= $service['id']; ?>" 
                        style="flex:1; width:100%; padding:14px; border-radius:12px; cursor:pointer; font-weight:bold; border:1px solid #ff3b85; background:#ff3b85; color:#fff;">
                    Add to Cart
                </button>

                <!-- BUY NOW -->
                <form method="POST" action="" style="flex:1;">
                    <input type="hidden" name="service_id" value="<?= $service['id']; ?>">
                    <input type="hidden" name="quantity" id="buyNowQty" value="1">
                    <button type="submit" name="buy_now" 
                        style="width:100%; padding:14px; border-radius:12px; cursor:pointer; 
                               font-weight:bold; border:1px solid #ff3b85; background:#fff; color:#ff3b85;">
                        Buy Now
                    </button>
                </form>
            </div>

            <p style="margin-top:20px; color:#666;">Shipping fee: <?= formatCurrency($service['shipping_fee'] ?? 0); ?></p>
        </div>
    </div>
</div>

<script>
// Thumbnails click
const mainImg = document.getElementById("mainImg");
const thumbs = document.querySelectorAll(".thumb");
thumbs.forEach(t=>{
    t.onclick=()=>{
        mainImg.src = t.src;
        thumbs.forEach(x=>x.classList.remove('active'));
        t.classList.add('active');
    }
});

// Quantity control
function changeQty(n){
    let qtyEl = document.getElementById('qty');
    let inputQtyEl = document.getElementById('buyNowQty');
    let addCartQtyEl = document.getElementById('btnAddToCartQty');
    
    let qty = parseInt(qtyEl.innerText);
    qty = Math.max(1, qty+n);
    qtyEl.innerText = qty;
    inputQtyEl.value = qty;
}

// Add to Cart AJAX
document.getElementById("btnAddToCart").addEventListener("click", () => {
    const service_id = document.getElementById("btnAddToCart").dataset.id;
    const quantity   = document.getElementById("qty").innerText;

    let formData = new FormData();
    formData.append("service_id", service_id);
    formData.append("quantity", quantity);

    fetch("add-to-cart.php", { method: "POST", body: formData })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert(data.error || "Không thể thêm vào giỏ");
            return;
        }

        // Redirect sang giỏ hàng
        window.location.href = "cart.php";
    })
    .catch(err => { 
        console.error(err);
        alert("Lỗi kết nối server!");
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
