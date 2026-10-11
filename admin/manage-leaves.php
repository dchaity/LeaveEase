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

$success = '';
$error = '';
$show_confetti = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_id = intval($_POST['leave_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $admin_comment = sanitize($_POST['admin_comment'] ?? '');

    if ($action === 'approve') {
        $stmt = $conn->prepare("SELECT user_id, total_days FROM leave_requests WHERE id = ?");
        $stmt->bind_param("i", $leave_id);
        $stmt->execute();
        $leave = $stmt->get_result()->fetch_assoc();

        if ($leave) {
            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("UPDATE leave_requests SET status = 'Approved', admin_comment = ?, approved_by = ?, approved_date = NOW() WHERE id = ?");
                $stmt->bind_param("sii", $admin_comment, $_SESSION['user_id'], $leave_id);
                $stmt->execute();

                $stmt = $conn->prepare("UPDATE users SET used_leaves = used_leaves + ?, remaining_leaves = remaining_leaves - ? WHERE id = ?");
                $stmt->bind_param("iii", $leave['total_days'], $leave['total_days'], $leave['user_id']);
                $stmt->execute();

                updateLeaveBalance($leave['user_id']);
                $conn->commit();

                addNotification($leave['user_id'], 'Leave Approved', 'Your leave request has been approved.', 'success', 'leave-history.php');
                logActivity($_SESSION['user_id'], 'leave_approved', "Approved leave ID: $leave_id");

                $success = "Leave approved successfully!";
                $show_confetti = true;
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Failed to approve leave.";
            }
        }
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("SELECT user_id FROM leave_requests WHERE id = ?");
        $stmt->bind_param("i", $leave_id);
        $stmt->execute();
        $leave = $stmt->get_result()->fetch_assoc();

        if ($leave) {
            $stmt = $conn->prepare("UPDATE leave_requests SET status = 'Rejected', admin_comment = ?, approved_by = ?, approved_date = NOW() WHERE id = ?");
            $stmt->bind_param("sii", $admin_comment, $_SESSION['user_id'], $leave_id);

            if ($stmt->execute()) {
                addNotification($leave['user_id'], 'Leave Rejected', 'Your leave request has been rejected. Reason: ' . $admin_comment, 'error', 'leave-history.php');
                logActivity($_SESSION['user_id'], 'leave_rejected', "Rejected leave ID: $leave_id");
                $success = "Leave rejected successfully!";
            } else {
                $error = "Failed to reject leave.";
            }
        }
    }
}

$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$query = "SELECT lr.*, u.full_name, u.email, u.department 
          FROM leave_requests lr 
          JOIN users u ON lr.user_id = u.id";
$params = [];
$types = "";
$conditions = [];

if ($status_filter) {
    $conditions[] = "lr.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}
if ($search) {
    $conditions[] = "(u.full_name LIKE ? OR u.email LIKE ? OR lr.leave_type LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "sss";
}
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}
$query .= " ORDER BY lr.created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$leave_requests = $stmt->get_result();

$total = $conn->query("SELECT COUNT(*) as count FROM leave_requests")->fetch_assoc()['count'];
$pending = $conn->query("SELECT COUNT(*) as count FROM leave_requests WHERE status = 'Pending'")->fetch_assoc()['count'];
$approved = $conn->query("SELECT COUNT(*) as count FROM leave_requests WHERE status = 'Approved'")->fetch_assoc()['count'];
$rejected = $conn->query("SELECT COUNT(*) as count FROM leave_requests WHERE status = 'Rejected'")->fetch_assoc()['count'];

$page_title = 'Manage Leaves';
?>
<?php include '../includes/header.php'; ?>

<?php if ($show_confetti): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.createElement('div');
    container.className = 'confetti-container';
    document.body.appendChild(container);
    const colors = ['#10b981', '#34d399', '#6ee7b7', '#fbbf24', '#f59e0b', '#3b82f6'];
    for (let i = 0; i < 150; i++) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti';
        confetti.style.left = Math.random() * 100 + '%';
        confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
        confetti.style.width = (Math.random() * 10 + 5) + 'px';
        confetti.style.height = (Math.random() * 10 + 5) + 'px';
        confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
        confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
        confetti.style.animationDelay = (Math.random() * 2) + 's';
        container.appendChild(confetti);
    }
    setTimeout(() => container.remove(), 5000);
});
</script>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-3">
        <?php include 'sidebar.php'; ?>
    </div>

    <div class="col-lg-9">
        <div class="page-header">
            <h1 class="page-title">Manage Leave Requests</h1>
            <p class="page-subtitle">Review and process employee leave requests</p>
        </div>

        <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>

        <div class="stats-grid mb-4">
            <div class="stat-card"><div class="stat-card-header"><div class="stat-card-icon green"><i class="fas fa-list"></i></div></div><div class="stat-card-value"><?= $total ?></div><div class="stat-card-label">Total</div></div>
            <div class="stat-card warning"><div class="stat-card-header"><div class="stat-card-icon orange"><i class="fas fa-clock"></i></div></div><div class="stat-card-value"><?= $pending ?></div><div class="stat-card-label">Pending</div></div>
            <div class="stat-card success"><div class="stat-card-header"><div class="stat-card-icon green"><i class="fas fa-check"></i></div></div><div class="stat-card-value"><?= $approved ?></div><div class="stat-card-label">Approved</div></div>
            <div class="stat-card danger"><div class="stat-card-header"><div class="stat-card-icon red"><i class="fas fa-times"></i></div></div><div class="stat-card-value"><?= $rejected ?></div><div class="stat-card-label">Rejected</div></div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="Pending" <?= $status_filter == 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Approved" <?= $status_filter == 'Approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="Rejected" <?= $status_filter == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Search by name, email or type..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn-primary w-100"><i class="fas fa-search"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr><th>Employee</th><th>Type</th><th>Dates</th><th>Days</th><th>Status</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php if ($leave_requests->num_rows > 0): ?>
                                <?php while ($request = $leave_requests->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($request['full_name']) ?></strong>
                                        <br><small style="color: var(--text-muted);"><?= htmlspecialchars($request['email']) ?></small>
                                        <br><small style="color: var(--text-muted);"><?= htmlspecialchars($request['department'] ?? 'N/A') ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($request['leave_type']) ?></td>
                                    <td><?= date('M d', strtotime($request['start_date'])) ?> - <?= date('M d, Y', strtotime($request['end_date'])) ?></td>
                                    <td><span class="badge badge-info"><?= $request['total_days'] ?></span></td>
                                    <td>
                                        <?php
                                        $badge_class = match($request['status']) {
                                            'Approved' => 'badge-success',
                                            'Pending' => 'badge-warning',
                                            'Rejected' => 'badge-danger',
                                            default => 'badge-secondary'
                                        };
                                        ?>
                                        <span class="badge <?= $badge_class ?>"><?= $request['status'] ?></span>
                                        <?php if ($request['admin_comment']): ?>
                                        <br><small style="color: var(--text-muted);"><?= htmlspecialchars($request['admin_comment']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($request['status'] == 'Pending'): ?>
                                        <button class="btn btn-sm btn-primary" onclick="openActionModal(<?= $request['id'] ?>, 'approve')"><i class="fas fa-check"></i> Approve</button>
                                        <button class="btn btn-sm btn-danger" onclick="openActionModal(<?= $request['id'] ?>, 'reject')"><i class="fas fa-times"></i> Reject</button>
                                        <?php else: ?>
                                        <span style="color: var(--text-muted); font-size: 0.85rem;">Processed</span>
                                        <?php endif; ?>
                                        <?php if ($request['document']): ?>
                                        <br>
                                        <a href="<?= BASE_URL ?>assets/uploads/documents/<?= $request['document'] ?>" target="_blank" class="btn btn-sm btn-outline mt-1"><i class="fas fa-file"></i> Document</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center" style="padding: 60px;"><i class="fas fa-inbox" style="font-size: 3rem; color: var(--gray-300); display: block; margin-bottom: 16px;"></i><h5 style="color: var(--text-secondary);">No leave requests found</h5></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="actionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="leave_id" id="modalLeaveId">
                    <input type="hidden" name="action" id="modalAction">
                    <p id="modalMessage"></p>
                    <div class="form-group">
                        <label class="form-label">Comment (Optional)</label>
                        <textarea name="admin_comment" class="form-control" rows="3" placeholder="Add a comment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary" id="modalConfirmBtn">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openActionModal(leaveId, action) {
    const modal = new bootstrap.Modal(document.getElementById('actionModal'));
    document.getElementById('modalLeaveId').value = leaveId;
    document.getElementById('modalAction').value = action;
    const isApprove = action === 'approve';
    document.getElementById('modalTitle').textContent = isApprove ? 'Approve Leave Request' : 'Reject Leave Request';
    document.getElementById('modalMessage').textContent = isApprove ? 'Are you sure you want to approve this leave request?' : 'Are you sure you want to reject this leave request?';
    document.getElementById('modalConfirmBtn').textContent = isApprove ? 'Approve' : 'Reject';
    document.getElementById('modalConfirmBtn').className = isApprove ? 'btn-primary' : 'btn-danger';
    modal.show();
}
</script>

<?php include '../includes/footer.php'; ?>