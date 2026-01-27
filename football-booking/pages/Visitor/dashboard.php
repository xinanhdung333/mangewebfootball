<?php
$page_title = 'Trang chủ';
include '../../includes/headerVisitor.php';
require_once '../../includes/config.php';
$user = null;
$bookings = [];

if (isset($_SESSION['user_id'])) {
    $user = getUserInfo($conn, $_SESSION['user_id']);
    $bookings = getUserBookings($conn, $_SESSION['user_id']);
}

// Thống kê
// Thống kê
$stats_total = 0;
$stats_confirmed = 0;
$stats_revenue = 0;

if (isset($_SESSION['user_id'])) {

    $user_id = intval($_SESSION['user_id']);

    $result = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE user_id = $user_id");
    $stats_total = $result->fetch_assoc()['total'];

    $result = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE user_id = $user_id AND status = 'confirmed'");
    $stats_confirmed = $result->fetch_assoc()['total'];

    $result = $conn->query("SELECT SUM(total_price) as total FROM bookings WHERE user_id = $user_id AND status = 'confirmed'");
    $stats_revenue = $result->fetch_assoc()['total'] ?? 0;
}

   // --- Demo news bóng đá ---
    $news = [
        [
            'title' => 'MU thắng nghẹt thở cuối trận',
            'description' => 'MU lội ngược dòng ở phút cuối, thắng 3-2 trước Chelsea.',
            'url' => '#',
            'urlToImage' => 'https://images2.thanhnien.vn/Uploaded/sontung/2023_01_14/2023-01-14t141009z-528690775-up1ej1e13cvbz-rtrmadp-3-soccer-england-mun-mci-report-2201.jpg',
            'publishedAt' => '2025-12-10 14:00:00'
        ],
        [
            'title' => 'Messi lập siêu phẩm ngoài vòng cấm',
            'description' => 'Messi ghi bàn ngoạn mục từ ngoài vòng cấm, giúp PSG dẫn đầu.',
            'url' => '#',
            'urlToImage' => 'https://images2.thanhnien.vn/528068263637045248/2025/7/13/messi-sut-phat-17523725364231377943710.jpg',
            'publishedAt' => '2025-12-10 12:00:00'
        ],
        [
            'title' => 'Barca đón trung vệ mới',
            'description' => 'Barca ký hợp đồng với trung vệ chất lượng từ Ligue 1.',
            'url' => '#',
            'urlToImage' => 'https://media-cdn-v2.laodong.vn/storage/newsportal/2024/4/10/1325719/Rsz_33Mt6kt-Preview.jpg',
            'publishedAt' => '2025-12-09 16:00:00'
        ],
        [
            'title' => 'Liverpool khởi đầu hoàn hảo',
            'description' => 'Liverpool thắng 4-0 trận mở màn Premier League.',
            'url' => '#',
            'urlToImage' => 'https://i.ibb.co/9tP7G2r/news4.jpg',
            'publishedAt' => '2025-12-09 10:00:00'
        ],
        [
            'title' => 'Real Madrid giữ vững ngôi đầu bảng',
            'description' => 'Real Madrid tiếp tục phong độ ấn tượng với chiến thắng 2-1.',
            'url' => '#',
            'urlToImage' => 'https://i.ibb.co/7kFxT99/news5.jpg',
            'publishedAt' => '2025-12-08 18:30:00'
        ],
    ];
    ?>
    <style>
    .hero-modern { position:relative; height:420px; display:flex; align-items:center; justify-content:center; overflow:hidden; background-color:#0f1724; margin-bottom:40px; }
    .hero-modern::before { content:""; position:absolute; inset:0; background:linear-gradient(120deg, rgba(37,99,235,.38), rgba(139,92,246,.28), rgba(249,115,22,.18)), url('../../assets/images/BANNERr.jpg') center/cover no-repeat; filter:brightness(0.95); z-index:1; }
    .hero-content-modern { text-align:center; color:#fff; z-index:2; }
    .hero-title { font-size:42px; font-weight:800; line-height:1.1; margin-bottom:10px; text-shadow:0 4px 18px rgba(0,0,0,0.35); }
    .hero-subtitle { font-size:18px; opacity:.9; margin-bottom:18px; }
    .hero-ctas { display:flex; gap:14px; justify-content:center; flex-wrap:wrap; }
    .hero-btn { padding:12px 22px; border-radius:50px; font-weight:700; box-shadow:0 8px 28px rgba(0,0,0,0.16); text-decoration:none; display:inline-flex; align-items:center; gap:6px; }
    .hero-btn.primary { background:linear-gradient(90deg,#06b6d4,#7c3aed); color:#fff; }
    .hero-btn.ghost { background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.25); color:#fff; }
    .container { max-width:1180px; margin:0 auto; padding:0 20px 40px; }
    .row.stats-row { margin-bottom:36px; display:flex; gap:20px; flex-wrap:wrap; }
    .stat-card { flex:1; padding:26px; border-radius:14px; color:#fff; box-shadow:0 18px 40px rgba(0,0,0,0.12); transition:.25s; text-align:center; }
    .stat-card:hover { transform:translateY(-8px); box-shadow:0 26px 60px rgba(0,0,0,0.18); }
    .stat-card .stat-icon { font-size:34px; margin-bottom:10px; }
    .stat-card h5 { margin:0; opacity:.85; }
    .stat-card .value { font-size:28px; font-weight:800; margin-top:8px; }
/* Thẻ 1 – Xanh đậm thể thao premium */
.stat-1 {
    background: linear-gradient(135deg, #64bd29ff, #265879ff);
}

/* Thẻ 2 – Xanh ngọc mát mắt, nổi số liệu */
.stat-2 {
    background: linear-gradient(135deg, #79a4cdff, #101258ff);
}

/* Thẻ 3 – Tím than sang, nhìn kiểu dashboard cao cấp */
.stat-3 {
    background: linear-gradient(135deg, #2a1b4d, #59308a);
}   

    .section-block { background:#fff; border-radius:14px; padding:24px; box-shadow:0 10px 30px rgba(0,0,0,0.06); margin-bottom:30px; }
    .table-custom thead th { background:#0f1724; color:#fff; border:none; }
    .table-custom tbody tr:hover { background:rgba(14,165,233,0.06); }
    .intro-box { padding:20px; background:#fff; border-left:5px solid #7c3aed; border-radius:10px; box-shadow:0 6px 20px rgba(0,0,0,0.06); }

    .news-carousel { display:flex; overflow-x:auto; gap:16px; padding-bottom:8px; }
    .news-carousel::-webkit-scrollbar { height:6px; }
    .news-carousel::-webkit-scrollbar-thumb { background:rgba(0,0,0,0.2); border-radius:3px; }
    .news-grid {
        display: grid;
        grid-template-columns: repeat(1, 1fr); /* mobile mặc định */
        gap: 18px;
        margin-top: 16px;
    }
    @media (min-width: 768px) {
        .news-grid {
            grid-template-columns: repeat(3, 1fr); /* tablet+ 3 cột ngang */
        }
    }


    .news-card {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 26px rgba(0,0,0,0.08);
        transition: .22s;
    }
    .news-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.12);
    }
    .news-card img {
        width: 100%;
        height: 180px;
        object-fit: cover;
    }
    .news-card .body {
        padding: 14px 16px;
    }
    .news-card h6 { margin:0 0 4px; font-weight:700; }
    .news-card small { color:#6b7280; } 
    .news-card p { margin-top:8px; font-size:14px; color:#333; }
    .row.g-4.mt-2 {
        display: flex;
        align-items: stretch; /* các col cùng chiều cao */
    }

    .col-lg-8{
    height:100%;
    }
footer {
    width: 100%;
    display: block;
}
    .container {
        max-width: 100% !important;
        width: 100% !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }



.image-between {
    position: relative;
    height: 0;
}

.image-between img {
    position: absolute;
    right: 30%;
    top: -330px;           /* kéo ảnh đè lên div trên */
    transform: translateX(-50%);
    width: 550px;
    z-index: 10;
}
.between-img {
    filter: drop-shadow(-10px 10px 22px rgba(12, 10, 10, 0.35));
    transition: .25s ease;
}

.between-img:hover {
    filter: drop-shadow(-18px 16px 38px rgba(0,0,0,0.5));
}


    </style>

    <section class="hero-modern">
        <div class="hero-content-modern">
            <h1 class="hero-title">Chào mừng  — Đặt sân nhanh, chơi đã</h1>
            <p class="hero-subtitle">Nhanh chóng chọn sân, giờ, và dịch vụ. Trải nghiệm đặt sân mượt mà trên mọi thiết bị.</p>
            <div class="hero-ctas">
                <a href="<?php echo SITE_URL; ?>/pages/fields.php" class="hero-btn primary"><i class="bi bi-geo-alt-fill"></i> Tìm sân gần bạn</a>
                <a href="<?php echo SITE_URL; ?>/pages/services.php" class="hero-btn ghost"><i class="bi bi-bag"></i> Dịch vụ & đồ ăn</a>
            </div>
        </div>
    </section>

    <div class="container">
        <div class="row stats-row">
            <div class="stat-card stat-1"><div class="stat-icon"><i class="bi bi-calendar-check"></i></div><h5>Tổng đặt sân</h5><p class="value"><?php echo $stats_total; ?></p></div>
            <div class="stat-card stat-2"><div class="stat-icon"><i class="bi bi-patch-check"></i></div><h5>Sân đã xác nhận</h5><p class="value"><?php echo $stats_confirmed; ?></p></div>
            <div class="stat-card stat-3"><div class="stat-icon"><i class="bi bi-cash-stack"></i></div><h5>Tổng chi phí</h5><p class="value"><?php echo formatCurrency($stats_revenue); ?></p></div>
        </div>

        <div class="row g-4 mt-2">
        <div class="col-lg-8">
            <div class="section-block">
            <h3><i class="bi bi-clock-history"></i> Đặt sân gần đây</h3>
            <div class="table-wrap">
                <?php if($bookings): ?>
                <table class="table table-hover table-custom align-middle mb-0">
                    <thead><tr><th>Sân</th><th>Ngày</th><th>Giờ</th><th>Giá</th><th>Trạng thái</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach(array_slice($bookings,0,6) as $b): ?>
                        <tr>
                        <td><?= htmlspecialchars($b['field_name']); ?></td>
                        <td><?= date('d/m/Y', strtotime($b['booking_date'])); ?></td>
                        <td><?= $b['start_time'].' - '.$b['end_time']; ?></td>
                        <td><?= formatCurrency($b['total_price']); ?></td>
                        <td><span class="badge bg-<?= $b['status']=='confirmed'?'success':($b['status']=='pending'?'warning':'danger'); ?>"><?= $b['status']=='confirmed'?'Xác nhận':($b['status']=='pending'?'Chờ':'Hủy'); ?></span></td>
                        <td><a href="booking-detail.php?id=<?= $b['id']; ?>" class="btn btn-sm btn-outline-primary">Xem</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="p-4">Chưa có đặt sân nào. <a href="<?= SITE_URL; ?>/pages/fields.php">Đặt ngay</a></div>
                <?php endif; ?>
            </div>
            <a href="my-bookings.php" class="btn btn-outline-primary mt-2">Xem tất cả</a>
            </div>
            <!-- KHUYẾN MÃI → thêm vào dưới Về chúng tôi -->
<div class="section-block mt-4">
    <h3><i class="bi bi-gift"></i> Khuyến mãi hôm nay</h3>
    <div class="intro-box">
        <p>Giảm 20% giá sân cho các khung giờ từ 14:00 - 17:00. Nhanh tay đặt ngay!</p>
        <a href="#" class="btn btn-sm btn-warning">Xem khuyến mãi</a>
    </div>
</div>

        </div>
        
    <div class="col-lg-4">
        <!-- Giới thiệu -->
        <div class="section-block">
            <h3><i class="bi bi-info-circle"></i> Giới thiệu</h3>
            <div class="intro-box">
                <p>Hệ thống sân bóng hiện đại, đạt chuẩn thi đấu – chiếu sáng LED, cỏ nhân tạo cao cấp, khu dịch vụ tiện nghi. Trải nghiệm tốt nhất cho người chơi bóng phong trào.</p>
                <a href="<?= SITE_URL; ?>/pages/about.php" class="btn btn-sm btn-primary">Tìm hiểu thêm</a>
            </div>
        </div>

        <!-- Về chúng tôi (nằm dưới Giới thiệu, cùng cột) -->
        <div class="section-block mt-4">
            <h3><i class="bi bi-info-circle"></i> Về chúng tôi</h3>
            <div class="intro-box">
                <p>Hệ thống sân bóng hiện đại, đạt chuẩn thi đấu – chiếu sáng LED, cỏ nhân tạo cao cấp, khu dịch vụ tiện nghi. Trải nghiệm tốt nhất cho người chơi bóng phong trào.</p>
                <a href="<?= SITE_URL; ?>/pages/about.php" class="btn btn-sm btn-primary">Tìm hiểu thêm</a>
            </div>
        </div>
    </div>
<div class="image-between">

<img src="../../assets/images/XXX.png" class="between-img">
</div>

    <!-- ===== NEWS ROW DƯỚI CÙNG ===== -->
   <div class="mt-4">
    <div class="section-block">
        <h3><i class="bi bi-newspaper"></i> Tin mới</h3>
        <div class="news-grid">
            <?php foreach(array_slice($news,0,3) as $a): ?>
                <div class="news-card">
                    <img src="<?php echo $a['urlToImage']; ?>" alt="">
                    <div class="body">
                        <h6><?php echo $a['title']; ?></h6>
                        <small><?php echo date('d/m/Y H:i', strtotime($a['publishedAt'])); ?></small>
                        <p><?php echo $a['description']; ?></p>
                        <a href="<?php echo $a['url']; ?>" target="_blank">Xem thêm</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

    <script>
    const hero = document.querySelector('.hero-modern');
    window.addEventListener('scroll', () => { hero.style.backgroundPositionY = `${window.scrollY*0.2}px`; }, { passive:true });
    </script>

    <?php require_once '../../includes/footer.php'; ?>
