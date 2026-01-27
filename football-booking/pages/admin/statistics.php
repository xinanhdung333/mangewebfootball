<?php
$page_title = 'Thống kê';
require_once '../../includes/header.php';

if (!isLoggedIn() || $_SESSION['role'] != 'admin') {
    redirect(SITE_URL . '/pages/login.php');
}

// =======================
// THỐNG KÊ TỔNG QUAN
// =======================

$result = $conn->query("SELECT COUNT(*) as total FROM users");
$stats_users = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("SELECT COUNT(*) as total FROM fields");
$stats_fields = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'confirmed'");
$stats_bookings = $result->fetch_assoc()['total'] ?? 0;

// --- Tổng doanh thu bookings + service
$result = $conn->query("
    SELECT SUM(total_booking + total_services) AS total_revenue 
    FROM user_spending
");
$stats_revenue = $result->fetch_assoc()['total_revenue'] ?? 0;

// =======================
// THỐNG KÊ DỊCH VỤ (SERVICE)
// =======================

// Tổng số dịch vụ được đặt
$result = $conn->query("
    SELECT COUNT(*) as total
    FROM booking_services
");
$stats_services_used = $result->fetch_assoc()['total'] ?? 0;

// Doanh thu từ dịch vụ
$result = $conn->query("
    SELECT SUM(bs.quantity * s.price) AS total
    FROM booking_services bs
    JOIN services s ON bs.service_id = s.id
");
$stats_services_revenue = $result->fetch_assoc()['total'] ?? 0;

// Thống kê dịch vụ theo loại (Pie Chart)
$services_by_type = $conn->query("
    SELECT s.name AS service_name, SUM(bs.quantity) AS total_used
    FROM booking_services bs
    JOIN services s ON bs.service_id = s.id
    GROUP BY bs.service_id
");

// Chuẩn bị mảng
$service_labels = [];
$service_counts = [];

if ($services_by_type && $services_by_type->num_rows > 0) {
    while ($row = $services_by_type->fetch_assoc()) {
        $service_labels[] = $row['service_name'];
        $service_counts[] = (int)$row['total_used'];
    }
}

// =======================
// BOOKING THEO THÁNG
// =======================

$bookings_by_month = $conn->query("
    SELECT DATE_FORMAT(booking_date, '%m/%Y') as month,
           COUNT(*) as count,
           SUM(total_price) as revenue
    FROM bookings
    WHERE status = 'confirmed'
    GROUP BY DATE_FORMAT(booking_date, '%m/%Y')
    ORDER BY STR_TO_DATE(CONCAT('01/', DATE_FORMAT(booking_date, '%m/%Y')), '%d/%m/%Y') DESC
    LIMIT 12
");

$labels_month = [];
$counts_month = [];
$revenues_month = [];

if ($bookings_by_month->num_rows > 0) {
    $tmp_lbl = [];
    $tmp_cnt = [];
    $tmp_rev = [];

    while ($r = $bookings_by_month->fetch_assoc()) {
        $tmp_lbl[] = $r['month'];
        $tmp_cnt[] = (int)$r['count'];
        $tmp_rev[] = (float)$r['revenue'];
    }

    $labels_month = array_reverse($tmp_lbl);
    $counts_month = array_reverse($tmp_cnt);
    $revenues_month = array_reverse($tmp_rev);
}
// ==========================
// DOANH THU DỊCH VỤ THEO THÁNG
// ==========================
$service_revenue_labels = [];
$service_revenue_values = [];
$q = $conn->query("
    SELECT 
        DATE_FORMAT(b.booking_date, '%m/%Y') AS month,
        SUM(bs.quantity * s.price) AS revenue
    FROM booking_services bs
    JOIN bookings b ON bs.booking_id = b.id
    JOIN services s ON bs.service_id = s.id
    GROUP BY DATE_FORMAT(b.booking_date, '%Y-%m')
    ORDER BY DATE_FORMAT(b.booking_date, '%Y-%m')
");

if ($q && $q->num_rows > 0) {
    while ($r = $q->fetch_assoc()) {
        $service_revenue_labels[] = $r['month'];
        $service_revenue_values[] = (int)$r['revenue'];
    }
}

// --- đảm bảo một số biến phụ tồn tại nếu bạn dán phần 1 trước
if (!isset($fields_by_status)) {
    $fields_by_status = $conn->query("
        SELECT status, COUNT(*) AS total
        FROM fields
        GROUP BY status
    ");
}

if (!isset($field_types) || !is_array($field_types)) {
    $field_types = [];
    $field_types_counts = [];
    if ($fields_by_status && $fields_by_status->num_rows > 0) {
        while ($r = $fields_by_status->fetch_assoc()) {
            $field_types[] = $r['status'];
            $field_types_counts[] = (int)$r['total'];
        }
        // reset pointer for potential later loops
        $fields_by_status->data_seek(0);
    }
}

if (!isset($stats_services)) {
    $r = $conn->query("SELECT COUNT(*) AS total FROM services");
    $stats_services = $r->fetch_assoc()['total'] ?? 0;
}
?>
<!-- =========== Styles (include dark-mode + card effects) =========== -->
<style>
/* Reset small */
.card { border: none; }

/* Dashboard background */
.dashboard-wrapper {
    padding: 22px;
}

/* Card styles */
.stat-card {
    background-size: 200% 200%;
    animation: gradientMove 6s ease infinite;
    color: white;
    border-radius: 14px;
    transition: transform 0.28s ease, box-shadow 0.35s ease;
    padding: 18px;
}
.stat-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.18);
}
@keyframes gradientMove {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
.stat-icon { font-size: 42px; opacity: .95; }

/* Fade-in */
.fade-in { opacity: 0; transform: translateY(12px); animation: fadeInUp 0.6s ease forwards; }
@keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }

/* Layout touches */
.card .card-title { margin-bottom: 6px; font-weight: 600; }
.counter { font-weight: 700; letter-spacing: 0.3px; }

/* Chart container responsive */
.chart-wrap { padding: 14px 6px; background: var(--card-bg, #fff); border-radius: 10px; box-shadow: 0 6px 18px rgba(0,0,0,0.04); }

/* Dark mode */
:root {
    --bg: #f6f9fc;
    --text: #222;
    --card-bg: #ffffff;
}
body.dark-mode {
    --bg: #0f1724;
    --text: #e6eef8;
    --card-bg: #0b1220;
    background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0)) fixed;
}
body { background: var(--bg); color: var(--text); }
.card { background: var(--card-bg); color: var(--text); }

#darkToggle, #darkModeToggle { cursor: pointer; }

/* Dark-mode toggle bubùon */
#darkToggle {
    position: fixed;
    right: 18px;
    bottom: 18px;
    z-index: 9999;
    border-radius: 999px;
    padding: 10px 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}
.small-muted { font-size: .85rem; color: rgba(0,0,0,0.55); }
body.dark-mode .small-muted { color: rgba(255,255,255,0.55); }

/* Table tweaks */
.table thead th { background: transparent; border-bottom: 1px solid rgba(0,0,0,0.06); }
body.dark-mode .table thead th { border-bottom-color: rgba(255,255,255,0.06); }

/* Responsive small adjustments */
@media (max-width: 767px) {
    .stat-card { padding: 12px; }
    .stat-icon { font-size: 34px; }
}
</style>

<div class="dashboard-wrapper">

    <!-- Header + Dark mode toggle -->
    <div class="row mb-3 fade-in" style="animation-delay:.05s">
        <div class="col-md-8">
            <h2><i class="bi bi-bar-chart-line-fill"></i> Thống kê tổng quan</h2>
            <p class="small-muted">Cập nhật số liệu theo thời gian thực (dựa trên dữ liệu trong DB).</p>
        </div>
        <div class="col-md-4 text-md-end align-self-center">
            <button id="darkToggle" class="btn btn-outline-secondary">
                <i class="bi bi-moon-stars-fill"></i> Chuyển chế độ
            </button>
        </div>
    </div>

    <!-- Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-md-3 fade-in" style="animation-delay:.08s">
            <a href="manage-users.php" class="text-decoration-none">
                <div class="stat-card" style="background: linear-gradient(135deg,#007bff,#4dabff);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white">Người dùng</h6>
                            <div class="counter fs-3 text-white" data-target="<?php echo $stats_users; ?>">0</div>
                            <div class="small-muted" style="opacity:0.9">Tổng người dùng</div>
                        </div>
                        <i class="bi bi-people stat-icon text-white"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-md-3 fade-in" style="animation-delay:.12s">
            <a href="manage-fields.php" class="text-decoration-none">
                <div class="stat-card" style="background: linear-gradient(135deg,#28a745,#66d97a);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white">Sân</h6>
                            <div class="counter fs-3 text-white" data-target="<?php echo $stats_fields; ?>">0</div>
                            <div class="small-muted" style="opacity:0.9">Số lượng sân</div>
                        </div>
                        <i class="bi bi-pin-map stat-icon text-white"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-md-3 fade-in" style="animation-delay:.16s">
            <a href="manage-bookings.php" class="text-decoration-none">
                <div class="stat-card" style="background: linear-gradient(135deg,#ffc107,#ffd75e);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-dark">Tổng đặt sân</h6>
                            <div class="counter fs-3 text-dark" data-target="<?php echo $stats_bookings; ?>">0</div>
                            <div class="small-muted" style="opacity:0.9">Đã xác nhận</div>
                        </div>
                        <i class="bi bi-calendar-check stat-icon text-dark"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-md-3 fade-in" style="animation-delay:.20s">
            <a href="manage-orders.php" class="text-decoration-none">
                <div class="stat-card" style="background: linear-gradient(135deg,#17a2b8,#63d2e6);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white">Doanh thu</h6>
                            <div class="counter fs-5 text-white" data-target="<?php echo $stats_revenue; ?>">0</div>
                            <div class="small-muted" style="opacity:0.9">Tổng thu</div>
                        </div>
                        <i class="bi bi-cash-coin stat-icon text-white"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Service card -->
        <div class="col-sm-6 col-md-3 fade-in" style="animation-delay:.24s">
            <a href="manage-services.php" class="text-decoration-none">
                <div class="stat-card" style="background: linear-gradient(135deg,#ff5722,#ff8a50);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white">Dịch vụ</h6>
                            <div class="counter fs-3 text-white" data-target="<?php echo $stats_services; ?>">0</div>
                            <div class="small-muted" style="opacity:0.9">Tổng dịch vụ</div>
                        </div>
                        <i class="bi bi-tools stat-icon text-white"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Charts + Table layout: left big charts, right pie -->
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card chart-wrap fade-in" style="animation-delay:.25s">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Doanh thu & Lượt đặt theo tháng</h5>
                        <div class="small-muted">Dữ liệu 12 tháng gần nhất</div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <canvas id="revenueLineChart" height="120"></canvas>
                        </div>
                        <div class="col-md-6">
                            <canvas id="bookingsBarChart" height="120"></canvas>
                        </div>
                        <div class="col-md-6">
                            <div class="pt-2">
                                <div class="small-muted mb-2">Tổng doanh thu: <strong><?php echo formatCurrency($stats_revenue); ?></strong></div>
                                <div class="small-muted">Tổng đặt: <strong><?php echo number_format($stats_bookings); ?></strong></div>
                                <div class="small-muted mt-3">Lưu ý: chỉ tính trạng thái <em>confirmed</em>.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly table -->
            <div class="card mt-3 fade-in" style="animation-delay:.28s">
                <div class="card-header"><strong>Đặt sân theo tháng</strong></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Tháng</th>
                                    <th>Số lần đặt</th>
                                    <th>Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($labels_month)) {
                                    for ($i = 0; $i < count($labels_month); $i++) {
                                        $lbl = $labels_month[$i];
                                        $cnt = $counts_month[$i] ?? 0;
                                        $rev = $revenues_month[$i] ?? 0;
                                        echo "<tr>";
                                        echo "<td>{$lbl}</td>";
                                        echo "<td>" . number_format($cnt) . "</td>";
                                        echo "<td>" . formatCurrency($rev) . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo '<tr><td colspan="3" class="text-center">Không có dữ liệu</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
<div class="card chart-wrap fade-in mt-3" style="animation-delay:.40s">
    <div class="card-body">
        <h5 class="mb-3">Doanh thu dịch vụ theo tháng</h5>
        <canvas id="serviceRevenueChart" height="180"></canvas>

        <div class="mt-3">
            <div class="small-muted">Tổng doanh thu dịch vụ: 
                <strong><?php echo formatCurrency(array_sum($service_revenue_values)); ?></strong>
            </div>
        </div>
    </div>
</div>
        </div> <!-- end left col -->

        <div class="col-lg-4">
            <div class="card chart-wrap fade-in" style="animation-delay:.30s">
                <div class="card-body">
                    <h5 class="mb-3">Tỉ lệ loại sân</h5>
                    <canvas id="fieldTypePie" height="220"></canvas>

                    <div class="mt-3">
                        <?php
                        if (!empty($field_types)) {
                            foreach ($field_types as $idx => $ft) {
                                $cnt = $field_types_counts[$idx] ?? 0;
                                echo '<div class="d-flex justify-content-between small-muted"><div>' . htmlspecialchars($ft) . '</div><div><strong>' . number_format($cnt) . '</strong></div></div>';
                            }
                        } else {
                            echo '<div class="small-muted">Chưa có loại sân nào được định nghĩa.</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Service pie -->
            <div class="card chart-wrap fade-in mt-3" style="animation-delay:.36s">
                <div class="card-body">
                    <h5 class="mb-3">Tỉ lệ sử dụng dịch vụ</h5>
                    <canvas id="servicePieChart" height="220"></canvas>

                    <div class="mt-3">
                        <?php
                        if (!empty($service_labels)) {
                            foreach ($service_labels as $idx => $sv) {
                                $cnt = $service_counts[$idx] ?? 0;
                                echo '<div class="d-flex justify-content-between small-muted"><div>' . htmlspecialchars($sv) . '</div><div><strong>' . number_format($cnt) . '</strong></div></div>';
                            }
                        } else {
                            echo '<div class="small-muted">Chưa có dữ liệu sử dụng dịch vụ.</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>


            <!-- Quick actions / summary -->
            <div class="card mt-3 fade-in" style="animation-delay:.33s">
                <div class="card-body">
                    <h6>Tóm tắt nhanh</h6>
                    <ul class="list-unstyled mb-0 small-muted">
                        <li>Người dùng: <strong><?php echo number_format($stats_users); ?></strong></li>
                        <li>Sân: <strong><?php echo number_format($stats_fields); ?></strong></li>
                        <li>Đặt sân (confirmed): <strong><?php echo number_format($stats_bookings); ?></strong></li>
                        <li>Doanh thu: <strong><?php echo formatCurrency($stats_revenue); ?></strong></li>
                        <li>Dịch vụ: <strong><?php echo number_format($stats_services); ?></strong></li>
                        <li>Số lần sử dụng DV: <strong><?php echo number_format($stats_services_used ?? 0); ?></strong></li>
                        <li>Doanh thu DV: <strong><?php echo formatCurrency($stats_services_revenue ?? 0); ?></strong></li>
                    </ul>
                </div>
            </div>

        </div> <!-- end right col -->
    </div> <!-- end charts row -->

</div> <!-- end wrapper -->

<!-- Dark mode toggle button (floating) -->
<button id="darkModeToggle" class="btn btn-primary" title="Chuyển chế độ">
    <i class="bi bi-moon"></i>
</button>

<!-- ========== Scripts ========== -->
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// ------- Prepare JS data from PHP
const labelsMonth = <?php echo json_encode($labels_month); ?> || [];
const countsMonth = <?php echo json_encode($counts_month); ?> || [];
const revenuesMonth = <?php echo json_encode($revenues_month); ?> || [];
const fieldTypes = <?php echo json_encode($field_types); ?> || [];
const fieldCounts = <?php echo json_encode($field_types_counts); ?> || [];
const serviceLabels = <?php echo json_encode($service_labels); ?> || [];
const serviceCounts = <?php echo json_encode($service_counts); ?> || [];

// ------- Chart: Revenue line chart
const ctxLine = document.getElementById('revenueLineChart').getContext('2d');
const revenueLine = new Chart(ctxLine, {
    type: 'line',
    data: {
        labels: labelsMonth,
        datasets: [{
            label: 'Doanh thu',
            data: revenuesMonth,
            borderWidth: 3,
            tension: 0.35,
            fill: true,
            backgroundColor: 'rgba(63, 81, 181, 0.08)',
            borderColor: 'rgba(63, 81, 181, 0.95)',
            pointRadius: 3
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { ticks: { callback: function(val) { return new Intl.NumberFormat().format(val); } } }
        }
    }
});

// ------- Chart: Bookings bar chart
const ctxBar = document.getElementById('bookingsBarChart').getContext('2d');
const bookingsBar = new Chart(ctxBar, {
    type: 'bar',
    data: {
        labels: labelsMonth,
        datasets: [{
            label: 'Số lần đặt',
            data: countsMonth,
            borderWidth: 0,
            borderRadius: 6,
            backgroundColor: 'rgba(40, 167, 69, 0.85)'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { precision:0 } }
        }
    }
});
// ====================
// SERVICE REVENUE BAR CHART
// ====================
const serviceRevLabels = <?php echo json_encode($service_revenue_labels); ?> || [];
const serviceRevValues = <?php echo json_encode($service_revenue_values); ?> || [];

const ctxServiceRev = document.getElementById('serviceRevenueChart').getContext('2d');
const serviceRevenueChart = new Chart(ctxServiceRev, {
    type: 'bar',
    data: {
        labels: serviceRevLabels,
        datasets: [{
            label: 'Doanh thu dịch vụ',
            data: serviceRevValues,
            borderWidth: 0,
            borderRadius: 6,
            backgroundColor: 'rgba(255, 87, 34, 0.9)'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(val) {
                        return new Intl.NumberFormat().format(val);
                    }
                }
            }
        }
    }
});

// ------- Chart: Field type pie chart
const ctxPie = document.getElementById('fieldTypePie').getContext('2d');
const fieldPie = new Chart(ctxPie, {
    type: 'pie',
    data: {
        labels: fieldTypes,
        datasets: [{
            data: fieldCounts,
            backgroundColor: [
                'rgba(75, 192, 192, 0.9)',
                'rgba(255, 205, 86, 0.9)',
                'rgba(255, 99, 132, 0.9)',
                'rgba(54, 162, 235, 0.9)',
                'rgba(153, 102, 255, 0.9)'
            ],
            hoverOffset: 8
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
    }
});

// ------- Chart: Service pie chart
const ctxServicePie = document.getElementById('servicePieChart').getContext('2d');
const servicePie = new Chart(ctxServicePie, {
    type: 'pie',
    data: {
        labels: serviceLabels,
        datasets: [{
            data: serviceCounts,
            backgroundColor: [
                'rgba(255, 87, 34, 0.9)',
                'rgba(0, 188, 212, 0.9)',
                'rgba(156, 39, 176, 0.9)',
                'rgba(255, 193, 7, 0.9)',
                'rgba(76, 175, 80, 0.9)'
            ],
            hoverOffset: 8
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
    }
});

// ------- Counter animation
document.querySelectorAll('.counter').forEach(counter => {
    const targetAttr = counter.getAttribute('data-target') || counter.innerText;
    const targetRaw = Number(targetAttr) || 0;
    let target = targetRaw;
    // if PHP passed formatted string, try to remove non-digits
    if (isNaN(target) && typeof targetAttr === 'string') {
        target = Number(targetAttr.replace(/[^\d.-]/g, '')) || 0;
    }
    let count = 0;
    const duration = 800; // ms
    const frames = Math.max(24, Math.round(duration / 16));
    const increment = target / frames;
    const format = function(n) { return new Intl.NumberFormat().format(Math.floor(n)); };

    const step = () => {
        count += increment;
        if (count < target) {
            counter.innerText = format(count);
            requestAnimationFrame(step);
        } else {
            // show decimals if not integer
            if (!Number.isInteger(target)) {
                counter.innerText = new Intl.NumberFormat().format(target);
            } else {
                counter.innerText = format(target);
            }
        }
    };
    step();
});

// ------- Dark mode toggle
const dmToggle = document.getElementById('darkToggle') || document.getElementById('darkModeToggle');
function setDarkMode(on) {
    if (on) {
        document.body.classList.add('dark-mode');
        localStorage.setItem('dashboard_dark', '1');
    } else {
        document.body.classList.remove('dark-mode');
        localStorage.removeItem('dashboard_dark');
    }
}
dmToggle && dmToggle.addEventListener('click', () => {
    const isOn = document.body.classList.contains('dark-mode');
    setDarkMode(!isOn);
});

// Initialize dark mode from localStorage
if (localStorage.getItem('dashboard_dark') === '1') {
    setDarkMode(true);
}

// Position floating toggle nicely
const floatBtn = document.getElementById('darkModeToggle');
if (floatBtn) {
    floatBtn.style.position = 'fixed';
    floatBtn.style.right = '18px';
    floatBtn.style.bottom = '18px';
    floatBtn.style.zIndex = 9999;
    floatBtn.addEventListener('click', () => {
        const isOn = document.body.classList.contains('dark-mode');
        setDarkMode(!isOn);
    });
}
</script>

<?php require_once '../../includes/footerADMIN.php'; ?>
