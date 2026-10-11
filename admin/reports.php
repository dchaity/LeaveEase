<?php
require_once '../config/database.php';

// Not logged in → login
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

// Stale session (role missing) → destroy & login
if (!isset($_SESSION['role']) || empty($_SESSION['role'])) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

// Logged-in employee → their dashboard (NO LOOP)
if (!isAdmin()) {
    header('Location: ' . BASE_URL . 'pages/dashboard.php');
    exit();
}

// Monthly statistics
$monthly_stats = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM leave_requests WHERE DATE_FORMAT(created_at, '%Y-%m') = ?");
    $stmt->bind_param("s", $month);
    $stmt->execute();
    $monthly_stats[$month] = $stmt->get_result()->fetch_assoc()['count'];
}

// Department statistics
$dept_stats = $conn->query("
    SELECT u.department, 
           COUNT(lr.id) as total_requests,
           SUM(CASE WHEN lr.status = 'Approved' THEN 1 ELSE 0 END) as approved,
           SUM(CASE WHEN lr.status = 'Pending' THEN 1 ELSE 0 END) as pending,
           SUM(CASE WHEN lr.status = 'Rejected' THEN 1 ELSE 0 END) as rejected
    FROM leave_requests lr
    JOIN users u ON lr.user_id = u.id
    WHERE u.department IS NOT NULL
    GROUP BY u.department
");

// Leave type statistics
$type_stats = $conn->query("
    SELECT leave_type, 
           COUNT(*) as count,
           SUM(total_days) as total_days
    FROM leave_requests
    GROUP BY leave_type
    ORDER BY count DESC
");

$page_title = 'Reports';
?>
<?php include '../includes/header.php'; ?>

<div class="row g-4">
    <div class="col-lg-3">
        <?php include 'sidebar.php'; ?>
    </div>

    <div class="col-lg-9">
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="page-title">Reports & Analytics</h1>
                <p class="page-subtitle">View leave statistics and trends</p>
            </div>
            <button class="btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Print Report
            </button>
        </div>

        <!-- Monthly Trend Chart -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-line"></i>
                Monthly Leave Trends
            </div>
            <div class="card-body">
                <div style="height: 300px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Department Statistics -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-building"></i>
                        Department-wise Statistics
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Department</th>
                                        <th>Total</th>
                                        <th>Approved</th>
                                        <th>Pending</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($dept_stats->num_rows > 0): ?>
                                        <?php while ($dept = $dept_stats->fetch_assoc()): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($dept['department']) ?></strong></td>
                                            <td><?= $dept['total_requests'] ?></td>
                                            <td><span class="badge badge-success"><?= $dept['approved'] ?></span></td>
                                            <td><span class="badge badge-warning"><?= $dept['pending'] ?></span></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center" style="padding: 40px; color: var(--text-muted);">
                                                No department data yet
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leave Type Distribution -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-pie"></i>
                        Leave Type Distribution
                    </div>
                    <div class="card-body">
                        <div style="height: 250px;">
                            <canvas id="typeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Monthly Trend Chart
const trendCtx = document.getElementById('trendChart').getContext('2d');
new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_keys($monthly_stats)) ?>,
        datasets: [{
            label: 'Leave Requests',
            data: <?= json_encode(array_values($monthly_stats)) ?>,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4,
            fill: true,
            borderWidth: 3,
            pointBackgroundColor: '#10b981',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

// Leave Type Chart
const typeCtx = document.getElementById('typeChart').getContext('2d');
const typeLabels = [];
const typeData = [];
<?php 
$type_stats->data_seek(0);
while ($row = $type_stats->fetch_assoc()): 
?>
typeLabels.push('<?= addslashes($row['leave_type']) ?>');
typeData.push(<?= $row['count'] ?>);
<?php endwhile; ?>

// Only render chart if there's data
if (typeData.length > 0) {
    new Chart(typeCtx, {
        type: 'doughnut',
        data: {
            labels: typeLabels,
            datasets: [{
                data: typeData,
                backgroundColor: ['#10b981', '#f59e0b', '#3b82f6', '#ef4444', '#8b5cf6', '#ec4899'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 16, usePointStyle: true }
                }
            }
        }
    });
} else {
    typeCtx.canvas.parentElement.innerHTML = '<div class="text-center" style="padding: 60px; color: var(--text-muted);">No leave type data yet</div>';
}
</script>

<?php include '../includes/footer.php'; ?>