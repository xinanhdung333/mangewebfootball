<?php
$page_title = 'Giỏ hàng';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';

if (!isLoggedIn()) {
    redirect(SITE_URL . '/pages/login.php');
}

$user_id = $_SESSION['user_id'];

/* ===============================================
   LẤY GIỎ HÀNG
================================================ */
$stmt = $conn->prepare("
    SELECT * FROM cart 
    WHERE user_id=? 
    ORDER BY created_at DESC 
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart = $stmt->get_result()->fetch_assoc();

$items = [];
$total_price = 0;

if ($cart) {
    $cart_id = $cart['id'];

    $stmt = $conn->prepare("
        SELECT ci.*, s.name, s.price, s.image, s.quantity AS stock
        FROM cart_items ci
        JOIN services s ON ci.service_id = s.id
        WHERE ci.cart_id = ?
    ");
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $total_price = array_reduce($items, function($sum, $item){
        return $sum + $item['price'] * $item['quantity'];
    }, 0);
}

/* ===============================================
   LẤY LỊCH SỬ ĐẶT DỊCH VỤ
================================================ */
$history = $conn->query("
    SELECT o.id AS order_id, o.total_amount, o.created_at,
           oi.quantity, s.name, s.image
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN services s ON oi.service_id = s.id
    WHERE o.user_id = $user_id
    ORDER BY o.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

?>

<div class="container-cart">

    <!-- ==== TAB MENU ==== -->
    <ul class="tab-menu">
        <li class="tab active" data-tab="cart-tab">Giỏ hàng</li>
        <li class="tab" data-tab="history-tab">Lịch sử dịch vụ đã mua</li>
    </ul>

    <!-- ==========================================
         TAB 1: GIỎ HÀNG
    ============================================ -->
    <div id="cart-tab" class="tab-content active">

        <h2>Giỏ hàng của bạn</h2>

        <?php if ($items): ?>
            <div class="cart-list">
                <?php foreach ($items as $item): ?>
                    <div class="cart-item" 
                         data-id="<?= $item['id']; ?>" 
                         data-price="<?= $item['price']; ?>" 
                         data-stock="<?= $item['stock']; ?>">

                        <div class="checkbox-holder">
                            <input type="checkbox" class="select-item" value="<?= $item['id']; ?>">
                        </div>

                        <img src="<?= !empty($item['image']) 
                            ? SITE_URL.'/uploads/services/'.$item['image'] 
                            : SITE_URL.'/assets/images/default.png'; ?>" 
                            alt="<?= htmlspecialchars($item['name']); ?>">

                        <div class="cart-details">
                            <h3><?= htmlspecialchars($item['name']); ?></h3>
                            <p>Giá: <span class="price"><?= formatCurrency($item['price']); ?></span></p>

                            <div class="quantity-control">
                                <button class="qty-btn decrease">-</button>
                                <span class="qty"><?= $item['quantity']; ?></span>
                                <button class="qty-btn increase">+</button>
                            </div>

                            <p>Tổng: 
                                <span class="item-total">
                                    <?= formatCurrency($item['price'] * $item['quantity']); ?>
                                </span>
                            </p>

                            <form method="POST" action="remove-from-cart.php">
                                <input type="hidden" name="cart_item_id" value="<?= $item['id']; ?>">
                                <button type="submit" class="remove-btn">Xóa</button>
                            </form>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <h3>Tổng thanh toán: <span id="cart-total"><?= formatCurrency($total_price); ?></span></h3>
                <a href="checkout.php" class="checkout-btn">Thanh toán tất cả</a>
            </div>

            <form method="POST" action="checkout-multiple.php" id="checkout-selected-form">
                <input type="hidden" name="selected_items" id="selected-items">
                <button type="submit" class="checkout-btn" style="margin-top:15px; width:100%;">
                    Thanh toán sản phẩm đã chọn
                </button>
            </form>

        <?php else: ?>
            <p class="empty-cart">Giỏ hàng trống.</p>
        <?php endif; ?>

    </div>

    <!-- ==========================================
         TAB 2: LỊCH SỬ DỊCH VỤ ĐÃ MUA
    ============================================ -->
    <div id="history-tab" class="tab-content">

        <h2>Lịch sử dịch vụ đã mua</h2>

        <?php if ($history): ?>
            <div class="history-list">
                <?php foreach ($history as $h): ?>
                    <div class="history-item">

                        <img src="<?= !empty($h['image']) 
                            ? SITE_URL.'/uploads/services/'.$h['image'] 
                            : SITE_URL.'/assets/images/default.png'; ?>">

                        <div class="history-details">
                            <h3><?= htmlspecialchars($h['name']); ?></h3>
                            <p>Số lượng: <?= $h['quantity']; ?></p>
                            <p>Tổng đơn: <?= formatCurrency($h['total_amount']); ?></p>
                            <p>Ngày mua: <?= date("d/m/Y H:i", strtotime($h['created_at'])); ?></p>
                            <a href="order-detail.php?id=<?= $h['order_id']; ?>" class="history-btn">Xem chi tiết</a>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="empty-cart">Bạn chưa mua dịch vụ nào.</p>
        <?php endif; ?>
    </div>

</div>
<style>
/* TAB */
.tab-menu { 
    display:flex; 
    gap:20px; 
    margin-bottom:20px; 
    border-bottom:2px solid #ddd; 
    padding-bottom:10px;
}
.tab { 
    cursor:pointer; 
    padding:8px 15px; 
    border-radius:5px;
}
.tab.active { 
    background:#007bff; 
    color:#fff; 
    font-weight:bold;
}
.tab-content { 
    display:none;
    text-align:left !important;
}

/* FIX: Hiển thị tab đầu */
.tab-content.active { display:block; }

/* GIỎ HÀNG */
.container-cart { 
    max-width:900px; 
    margin:20px auto; 
    padding:0 10px;
    text-align:left !important;
}
.cart-list { display:grid; gap:15px; }
.cart-item { 
    display:flex; 
    background:#fff; 
    border-radius:10px; 
    box-shadow:0 2px 10px rgba(0,0,0,0.1); 
    position: relative;
}
.checkbox-holder { 
    margin-left:auto; 
    padding:15px; 
    display:flex; 
    align-items:center; 
    z-index: 5;
}
.select-item { width:25px; height:25px; transform:scale(1.3); cursor:pointer; }
.cart-item img { width:120px; height:120px; object-fit:cover; }
.cart-details { padding:10px; flex:1; }
.quantity-control { display:flex; gap:10px; align-items:center; }
.qty-btn { padding:5px 12px; background:#007bff; color:#fff; border:none; border-radius:5px; cursor:pointer; font-size:18px; }
.cart-summary { margin-top:20px; padding:15px; background:#eee; border-radius:10px; display:flex; justify-content:space-between; }
.checkout-btn { padding:10px 20px; background:#007bff; color:#fff; border-radius:5px; text-decoration:none; display:block; }

/* HISTORY */
.history-list { display:grid; gap:15px; }
.history-item { display:flex; background:#fff; padding:10px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
.history-item img { width:120px; height:120px; object-fit:cover; border-radius:10px; }
.history-details { padding-left:15px; }
.history-btn { display:inline-block; padding:5px 12px; background:#28a745; color:#fff; border-radius:5px; margin-top:5px; text-decoration:none; }
</style>

<script>
// ==== CHUYỂN TAB ====
document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', () => {
        // xóa active tab cũ
        document.querySelector('.tab.active').classList.remove('active');
        tab.classList.add('active');

        // ẩn nội dung cũ
        document.querySelector('.tab-content.active').classList.remove('active');

        // hiện đúng tab mới
        const tabId = tab.getAttribute('data-tab');
        document.getElementById(tabId).classList.add('active');
    });
});

// ==== UPDATE SỐ LƯỢNG + TÍNH TỔNG ====
document.querySelectorAll('.cart-item').forEach(item => {
    const qtyEl = item.querySelector('.qty');
    const cartItemId = item.dataset.id;

    function updateToServer(newQty){
        let formData = new FormData();
        formData.append("cart_item_id", cartItemId);
        formData.append("quantity", newQty);

        fetch("update-quantity.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success){
                qtyEl.innerText = data.new_quantity;

                item.querySelector('.item-total').innerText =
                    data.item_total.toLocaleString('vi-VN') + " VNĐ";

                document.getElementById('cart-total').innerText =
                    data.cart_total.toLocaleString('vi-VN') + " VNĐ";

                updateSelectedTotal();
            }
        })
        .catch(err => console.error("Lỗi server:", err));
    }

    item.querySelector('.increase').addEventListener('click', () => {
        let qty = parseInt(qtyEl.innerText);
        const stock = parseInt(item.dataset.stock);
        if(qty < stock){
            updateToServer(qty + 1);
        }
    });

    item.querySelector('.decrease').addEventListener('click', () => {
        let qty = parseInt(qtyEl.innerText);
        if(qty > 1){
            updateToServer(qty - 1);
        }
    });
});

// ==== TÍNH TỔNG MỤC CHỌN ====
function updateSelectedTotal(){
    let total = 0;

    document.querySelectorAll('.cart-item').forEach(item => {
        const cb = item.querySelector('.select-item');
        if(cb.checked){
            let qty = parseInt(item.querySelector('.qty').innerText);
            let price = parseFloat(item.dataset.price);
            total += qty * price;
        }
    });

    document.getElementById('cart-total').innerText =
        total.toLocaleString('vi-VN') + " VNĐ";
}

document.querySelectorAll('.select-item').forEach(cb => {
    cb.addEventListener('change', updateSelectedTotal);
});

// ==== THANH TOÁN SẢN PHẨM ĐÃ CHỌN ====
document.getElementById('checkout-selected-form').addEventListener('submit', function(e) {
    let selected = [];
    document.querySelectorAll('.select-item:checked').forEach(cb => {
        selected.push(cb.value);
    });

    if(selected.length === 0){
        alert("Vui lòng chọn ít nhất một sản phẩm!");
        e.preventDefault();
        return;
    }

    document.getElementById('selected-items').value = JSON.stringify(selected);
});
</script>


<?php require_once '../includes/footer.php'; ?>
