<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

if(!isLoggedIn()){
    echo json_encode(['success'=>false,'message'=>'Chưa đăng nhập']);
    exit;
}
function updateCartItem(cartItemId, quantity) {
  const formData = new FormData();
  formData.append('cart_item_id', cartItemId);
  formData.append('quantity', quantity);

  return fetch('update-cart-item.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
      if(!data.success){
        alert(data.message);
        return false;
      }
      return true; // trả về true khi update thành công
    })
    .catch(err => {
      alert('Lỗi server!');
      return false;
    });
}

$cart_item_id = $_POST['cart_item_id'] ?? 0;
$quantity = $_POST['quantity'] ?? 1;

if($cart_item_id <= 0 || $quantity < 1){
    echo json_encode(['success'=>false,'message'=>'Dữ liệu không hợp lệ']);
    exit;
}

$stmt = $conn->prepare("UPDATE cart_items SET quantity=? WHERE id=?");
$stmt->bind_param("ii",$quantity,$cart_item_id);

if($stmt->execute()){
    echo json_encode(['success'=>true]);
}else{
    echo json_encode(['success'=>false,'message'=>'Cập nhật thất bại']);
}
