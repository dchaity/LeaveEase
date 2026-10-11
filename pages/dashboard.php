<?php
require_once '../config/database.php';

// Not logged in → login page
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

// Role missing (stale session) → destroy & go to login
if (!isset($_SESSION['role']) || empty($_SESSION['role'])) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

// Admin should NOT be here → send to admin panel
if (isAdmin()) {
    header('Location: ' . BASE_URL . 'admin/dashboard.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$balance = getLeaveBalance($user_id);

// Get user's recent leave requests
$stmt = $conn->prepare("SELECT * FROM leave_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_leaves = $stmt->get_result();

// Get user's pending requests count
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM leave_requests WHERE user_id = ? AND status = 'Pending'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pending_count = $stmt->get_result()->fetch_assoc()['count'];

// Get user's approved requests count
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM leave_requests WHERE user_id = ? AND status = 'Approved'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$approved_count = $stmt->get_result()->fetch_assoc()['count'];

// Get upcoming holidays
$holidays = $conn->query("SELECT * FROM holidays WHERE date >= CURDATE() ORDER BY date ASC LIMIT 3");

$greeting = getGreeting();

$page_title = 'Dashboard';
?>
<?php include '../includes/header.php'; ?>

<div class="page-header">
    <h1 class="page-title"><?= $greeting[0] ?>, <?= htmlspecialchars($_SESSION['full_name']) ?>! <?= $greeting[1] ?></h1>
    <p class="page-subtitle">Here's an overview of your leave status</p>
</div>

<!-- Stats Grid -->
<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-card-icon green">
                <i class="fas fa-calendar-check"></i>
            </div>
        </div>
        <div class="stat-card-value"><?= $balance['total_leaves'] ?></div>
        <div class="stat-card-label">Total Leaves</div>
    </div>
    
    <div class="stat-card warning">
        <div class="stat-card-header">
            <div class="stat-card-icon orange">
                <i class="fas fa-hourglass-half"></i>
            </div>
        </div>
        <div class="stat-card-value"><?= $balance['used_leaves'] ?></div>
        <div class="stat-card-label">Used Leaves</div>
    </div>
    
    <div class="stat-card success">
        <div class="stat-card-header">
            <div class="stat-card-icon green">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="stat-card-value"><?= $balance['remaining_leaves'] ?></div>
        <div class="stat-card-label">Remaining</div>
    </div>
    
    <div class="stat-card info">
        <div class="stat-card-header">
            <div class="stat-card-icon blue">
                <i class="fas fa-clock"></i>
            </div>
        </div>
        <div class="stat-card-value"><?= $pending_count ?></div>
        <div class="stat-card-label">Pending Requests</div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Leave Requests -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history"></i>
                Recent Leave Requests
                <a href="leave-history.php" class="ms-auto btn btn-sm btn-outline">View All</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Start</th>
                                <th>End</th>
                                <th>Days</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_leaves->num_rows > 0): ?>
                                <?php while ($leave = $recent_leaves->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($leave['leave_type']) ?></strong></td>
                                    <td><?= date('M d, Y', strtotime($leave['start_date'])) ?></td>
                                    <td><?= date('M d, Y', strtotime($leave['end_date'])) ?></td>
                                    <td><span class="badge badge-info"><?= $leave['total_days'] ?> days</span></td>
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
                                        <i class="fas fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 12px; color: var(--gray-300);"></i>
                                        No leave requests yet.
                                        <br><a href="apply-leave.php" class="btn-primary mt-3" style="display: inline-flex;">
                                            <i class="fas fa-plus"></i> Apply for Leave
                                        </a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sidebar Widgets -->
    <div class="col-lg-4">
        <!-- Leave Balance Widget -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-wallet"></i>
                Leave Balance
            </div>
            <div class="card-body text-center">
                <div style="font-size: 3rem; font-weight: 800; color: var(--primary); line-height: 1;">
                    <?= $balance['remaining_leaves'] ?>
                </div>
                <div style="color: var(--text-muted); margin-bottom: 16px;">days remaining</div>
                
                <div class="d-flex justify-content-between mb-2">
                    <span style="font-size: 0.9rem;">Approved</span>
                    <span style="font-weight: 600;"><?= $approved_count ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span style="font-size: 0.9rem;">Pending</span>
                    <span style="font-weight: 600;"><?= $pending_count ?></span>
                </div>
                
                <a href="apply-leave.php" class="btn-primary mt-3 w-100" style="justify-content: center;">
                    <i class="fas fa-plus"></i> Apply for Leave
                </a>
            </div>
        </div>
        
        <!-- Upcoming Holidays Widget -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-calendar-alt"></i>
                Upcoming Holidays
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if ($holidays->num_rows > 0): ?>
                    <?php while ($holiday = $holidays->fetch_assoc()): ?>
                    <div style="display: flex; gap: 12px; align-items: center; padding: 14px 20px; border-bottom: 1px solid var(--gray-100);">
                        <div style="width: 42px; height: 42px; background: var(--primary-bg); border-radius: var(--radius); display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--primary); font-weight: 700;">
                            <div style="font-size: 0.7rem; line-height: 1;"><?= date('M', strtotime($holiday['date'])) ?></div>
                            <div style="font-size: 1rem; line-height: 1;"><?= date('d', strtotime($holiday['date'])) ?></div>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 600; font-size: 0.9rem;"><?= htmlspecialchars($holiday['name']) ?></div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);"><?= date('l', strtotime($holiday['date'])) ?></div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center" style="padding: 30px; color: var(--text-muted);">
                        No upcoming holidays
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>