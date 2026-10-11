<?php
require_once '../config/database.php';

// Not logged in? Go to login.
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

// Logged in but role is missing (stale session)? Destroy and go to login.
if (!isset($_SESSION['role']) || empty($_SESSION['role'])) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

// Logged in as employee? Send them to employee dashboard (no loop).
if (!isAdmin()) {
    header('Location: ' . BASE_URL . 'pages/dashboard.php');
    exit();
}

// Get statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'employee' AND is_active = 1")->fetch_assoc()['count'];
$pending_leaves = $conn->query("SELECT COUNT(*) as count FROM leave_requests WHERE status = 'Pending'")->fetch_assoc()['count'];
$approved_leaves = $conn->query("SELECT COUNT(*) as count FROM leave_requests WHERE status = 'Approved'")->fetch_assoc()['count'];
$total_requests = $conn->query("SELECT COUNT(*) as count FROM leave_requests")->fetch_assoc()['count'];

// Recent activity
$recent_activity = $conn->query("
    SELECT al.*, u.full_name 
    FROM activity_logs al 
    JOIN users u ON al.user_id = u.id 
    ORDER BY al.created_at DESC 
    LIMIT 10
");

// Recent leave requests
$recent_leaves = $conn->query("
    SELECT lr.*, u.full_name, u.department 
    FROM leave_requests lr 
    JOIN users u ON lr.user_id = u.id 
    ORDER BY lr.created_at DESC 
    LIMIT 5
");

$page_title = 'Admin Dashboard';
?>
<?php include '../includes/header.php'; ?>

<div class="row g-4">
    <!-- Sidebar -->
    <div class="col-lg-3">
        <div class="card" style="position: sticky; top: 100px;">
            <div class="card-body" style="padding: 16px;">
                <nav style="display: flex; flex-direction: column; gap: 4px;">
                    <a href="dashboard.php" style="display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: var(--radius); background: var(--primary-bg); color: var(--primary); font-weight: 600; text-decoration: none;">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                    <a href="manage-leaves.php" style="display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: var(--radius); color: var(--text-secondary); text-decoration: none; transition: all 0.3s ease;">
                        <i class="fas fa-list"></i>
                        Manage Leaves
                        <?php if ($pending_leaves > 0): ?>
                        <span class="badge badge-warning ms-auto"><?= $pending_leaves ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="manage-users.php" style="display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: var(--radius); color: var(--text-secondary); text-decoration: none; transition: all 0.3s ease;">
                        <i class="fas fa-users"></i>
                        Manage Users
                    </a>
                    <a href="reports.php" style="display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: var(--radius); color: var(--text-secondary); text-decoration: none; transition: all 0.3s ease;">
                        <i class="fas fa-chart-bar"></i>
                        Reports
                    </a>
                    <a href="settings.php" style="display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: var(--radius); color: var(--text-secondary); text-decoration: none; transition: all 0.3s ease;">
                        <i class="fas fa-cog"></i>
                        Settings
                    </a>
                </nav>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="col-lg-9">
        <div class="page-header">
            <h1 class="page-title">Admin Dashboard</h1>
            <p class="page-subtitle">Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?>!</p>
        </div>
        
        <!-- Stats -->
        <div class="stats-grid mb-4" style="grid-template-columns: repeat(2, 1fr);">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-card-icon green">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-card-value"><?= $total_users ?></div>
                <div class="stat-card-label">Active Employees</div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-card-header">
                    <div class="stat-card-icon orange">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stat-card-value"><?= $pending_leaves ?></div>
                <div class="stat-card-label">Pending Requests</div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-card-header">
                    <div class="stat-card-icon green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-card-value"><?= $approved_leaves ?></div>
                <div class="stat-card-label">Approved Leaves</div>
            </div>
            
            <div class="stat-card info">
                <div class="stat-card-header">
                    <div class="stat-card-icon blue">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="stat-card-value"><?= $total_requests ?></div>
                <div class="stat-card-label">Total Requests</div>
            </div>
        </div>
        
        <!-- Recent Leave Requests -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-list"></i>
                Recent Leave Requests
                <a href="manage-leaves.php" class="ms-auto btn btn-sm btn-outline">View All</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Dates</th>
                                <th>Days</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_leaves->num_rows > 0): ?>
                                <?php while ($leave = $recent_leaves->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($leave['full_name']) ?></strong>
                                        <br><small style="color: var(--text-muted);"><?= htmlspecialchars($leave['department'] ?? 'N/A') ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($leave['leave_type']) ?></td>
                                    <td>
                                        <?= date('M d', strtotime($leave['start_date'])) ?> - 
                                        <?= date('M d', strtotime($leave['end_date'])) ?>
                                    </td>
                                    <td><span class="badge badge-info"><?= $leave['total_days'] ?></span></td>
                                    <td>
                                        <?php
                                        $badge_class = match($leave['status']) {
                                            'Approved' => 'badge-success',
                                            'Pending' => 'badge-warning',
                                            'Rejected' => 'badge-danger',
                                            default => 'badge-secondary'
                                        };
                                        ?>
                                        <span class="badge <?= $badge_class ?>"><?= $leave['status'] ?></span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center" style="padding: 40px; color: var(--text-muted);">
                                        No leave requests yet
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history"></i>
                Recent Activity
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if ($recent_activity->num_rows > 0): ?>
                    <?php while ($activity = $recent_activity->fetch_assoc()): ?>
                    <div style="display: flex; align-items: center; gap: 16px; padding: 16px 20px; border-bottom: 1px solid var(--gray-100);">
                        <div style="width: 36px; height: 36px; background: var(--primary-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 0.8rem;">
                            <i class="fas fa-activity"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 600; font-size: 0.9rem;"><?= htmlspecialchars($activity['full_name']) ?></div>
                            <div style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($activity['description'] ?? $activity['action']) ?></div>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); white-space: nowrap;">
                            <?= timeAgo($activity['created_at']) ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center" style="padding: 40px; color: var(--text-muted);">
                        No activity yet
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>