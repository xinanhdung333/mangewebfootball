<?php
ob_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="vi">
    <link rel="icon" type="image/png" sizes="256x256" href="<?php echo SITE_URL; ?>/assets/images/logo.jpg">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">

    <style>
         #mascot {
  position: fixed;
  top: 80%;
  left: 90%;
  width: 80px;
  height: 80px;
  cursor: grab;
  z-index: 9999;
}
        .admin-nav {
            display: flex;
            gap: 10px;
            margin: 0;
            padding: 0;
            list-style: none;
            align-items: center;
        }
        .admin-nav li a {
            color: #fff;
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            transition: 0.2s;
            display: block;
        }
        .admin-nav li a:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<a href="../../includes/about.php">
<img src="../../assets/images/mascot.png" id="mascot" alt="Mascot">
    </a>
 <script>
const mascot = document.getElementById('mascot');
let isDragging = false;
let offsetX = 0, offsetY = 0;

mascot.addEventListener('mousedown',  (e) => {
    isDragging = true;
    offsetX = e.clientX - mascot.offsetLeft;
    offsetY = e.clientY - mascot.offsetTop;
    mascot.style.cursor = 'grabbing';
});

document.addEventListener('mouseup', () => {
    isDragging = false;
    mascot.style.cursor = 'grab';
});

document.addEventListener('mousemove', (e) => {
    if (!isDragging) return;

    mascot.style.left = (e.clientX - offsetX) + 'px';
    mascot.style.top  = (e.clientY - offsetY) + 'px';
});
</script>

<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="<?php echo SITE_URL; ?>/index.php">
    <i class="bi bi-dribbble"></i> Football Booking
</a>


        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                                      <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/pages/Visitor/dashboard.php"><i class="bi bi-house"></i> Trang chủ</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/pages/Visitor/fields.php"><i class="bi bi-grid"></i> Sân</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/pages/Visitor/services.php"><i class="bi bi-bag"></i> Dịch vụ</a></li>
                         <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/pages/Visitor/feedback.php"><i class="bi bi-chat-dots"></i> Feedback</a></li>
                                                  <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/includes/about.php"><i class="bi bi-chat-dots"></i> About</a></li>

                    <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/pages/login.php"><i class="bi bi-box-arrow-in-right"></i> Đăng nhập</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/pages/register.php"><i class="bi bi-person-plus"></i> Đăng ký</a></li>

            </ul>
        </div>
    </div>
</nav>

<main class="container mt-4">   <!-- THÊM DÒNG NÀY -->

<div class="container-fluid px-4">