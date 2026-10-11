<?php
require_once '../config/database.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$filter_status = $_GET['status'] ?? '';
$filter_type = $_GET['type'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$query = "SELECT * FROM leave_requests WHERE user_id = ?";
$params = [$user_id];
$types = "i";

if ($filter_status) {
    $query .= " AND status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

if ($filter_type) {
    $query .= " AND leave_type = ?";
    $params[] = $filter_type;
    $types .= "s";
}

if ($search) {
    $query .= " AND (reason LIKE ? OR leave_type LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$leave_requests = $stmt->get_result();

// Get statistics
$stats_stmt = $conn->prepare("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected
    FROM leave_requests WHERE user_id = ?");
$stats_stmt->bind_param("i", $user_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

$page_title = 'Leave History';
?>
<?php include '../includes/header.php'; ?>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Leave History</h1>
        <p class="page-subtitle">View and track all your leave requests</p>
    </div>
    <a href="apply-leave.php" class="btn-primary">
        <i class="fas fa-plus"></i> New Request
    </a>
</div>

<!-- Statistics -->
<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-card-icon green">
                <i class="fas fa-list"></i>
            </div>
        </div>
        <div class="stat-card-value"><?= $stats['total'] ?? 0 ?></div>
        <div class="stat-card-label">Total Requests</div>
    </div>
    
    <div class="stat-card warning">
        <div class="stat-card-header">
            <div class="stat-card-icon orange">
                <i class="fas fa-clock"></i>
            </div>
        </div>
        <div class="stat-card-value"><?= $stats['pending'] ?? 0 ?></div>
        <div class="stat-card-label">Pending</div>
    </div>
    
    <div class="stat-card success">
        <div class="stat-card-header">
            <div class="stat-card-icon green">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="stat-card-value"><?= $stats['approved'] ?? 0 ?></div>
        <div class="stat-card-label">Approved</div>
    </div>
    
    <div class="stat-card danger">
        <div class="stat-card-header">
            <div class="stat-card-icon red">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
        <div class="stat-card-value"><?= $stats['rejected'] ?? 0 ?></div>
        <div class="stat-card-label">Rejected</div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="Pending" <?= $filter_status == 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Approved" <?= $filter_status == 'Approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="Rejected" <?= $filter_status == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="type" class="form-control">
                    <option value="">All Types</option>
                    <option value="Annual" <?= $filter_type == 'Annual' ? 'selected' : '' ?>>Annual</option>
                    <option value="Sick" <?= $filter_type == 'Sick' ? 'selected' : '' ?>>Sick</option>
                    <option value="Casual" <?= $filter_type == 'Casual' ? 'selected' : '' ?>>Casual</option>
                    <option value="Maternity" <?= $filter_type == 'Maternity' ? 'selected' : '' ?>>Maternity</option>
                    <option value="Paternity" <?= $filter_type == 'Paternity' ? 'selected' : '' ?>>Paternity</option>
                    <option value="Study" <?= $filter_type == 'Study' ? 'selected' : '' ?>>Study</option>
                    <option value="Other" <?= $filter_type == 'Other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" 
                       placeholder="Search by reason..." 
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn-primary w-100">
                    <i class="fas fa-search"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-history"></i>
        All Leave Requests
        <span class="ms-auto badge badge-info"><?= $leave_requests->num_rows ?> records</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Days</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Document</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($leave_requests->num_rows > 0): ?>
                        <?php $count = 1; ?>
                        <?php while ($request = $leave_requests->fetch_assoc()): ?>
                        <tr>
                            <td><?= $count++ ?></td>
                            <td>
                                <strong><?= htmlspecialchars($request['leave_type']) ?></strong>
                            </td>
                            <td><?= date('M d, Y', strtotime($request['start_date'])) ?></td>
                            <td><?= date('M d, Y', strtotime($request['end_date'])) ?></td>
                            <td>
                                <span class="badge badge-info"><?= $request['total_days'] ?> days</span>
                            </td>
                            <td>
                                <?php
                                $badge_class = match($request['status']) {
                                    'Approved' => 'badge-success',
                                    'Pending' => 'badge-warning',
                                    'Rejected' => 'badge-danger',
                                    default => 'badge-secondary'
                                };
                                ?>
                                <span class="badge <?= $badge_class ?>">
                                    <?= $request['status'] ?>
                                </span>
                            </td>
                            <td>
                                <small style="color: var(--text-muted);">
                                    <?= date('M d, Y', strtotime($request['created_at'])) ?>
                                </small>
                            </td>
                            <td>
                                <?php if ($request['document']): ?>
                                <a href="../assets/uploads/documents/<?= $request['document'] ?>" 
                                   target="_blank" class="btn btn-sm btn-outline">
                                    <i class="fas fa-file"></i> View
                                </a>
                                <?php else: ?>
                                <span style="color: var(--text-muted);">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 60px;">
                            <i class="fas fa-inbox" style="font-size: 3rem; color: var(--gray-300); display: block; margin-bottom: 16px;"></i>
                            <h5 style="color: var(--text-secondary);">No leave requests found</h5>
                            <p style="color: var(--text-muted); margin-bottom: 20px;">Start by applying for your first leave request</p>
                            <a href="apply-leave.php" class="btn-primary">
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

<?php include '../includes/footer.php'; ?>