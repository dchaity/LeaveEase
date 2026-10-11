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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['toggle_status'])) {
        $user_id = intval($_POST['user_id']);
        $new_status = intval($_POST['new_status']);

        $stmt = $conn->prepare("UPDATE users SET is_active = ? WHERE id = ? AND role != 'admin'");
        $stmt->bind_param("ii", $new_status, $user_id);

        if ($stmt->execute()) {
            $action = $new_status ? 'activated' : 'deactivated';
            logActivity($_SESSION['user_id'], "user_$action", "User ID: $user_id");
            $success = "User $action successfully!";
        }
    }

    if (isset($_POST['update_leaves'])) {
        $user_id = intval($_POST['user_id']);
        $total_leaves = intval($_POST['total_leaves']);
        $used_leaves = intval($_POST['used_leaves']);
        $remaining = $total_leaves - $used_leaves;

        $stmt = $conn->prepare("UPDATE users SET total_leaves = ?, used_leaves = ?, remaining_leaves = ? WHERE id = ?");
        $stmt->bind_param("iiii", $total_leaves, $used_leaves, $remaining, $user_id);

        if ($stmt->execute()) {
            logActivity($_SESSION['user_id'], 'leave_updated', "Updated leaves for user ID: $user_id");
            $success = "Leave balance updated successfully!";
        }
    }
}

$search = $_GET['search'] ?? '';
$query = "SELECT * FROM users WHERE role = 'employee'";
if ($search) {
    $search_esc = $conn->real_escape_string($search);
    $query .= " AND (full_name LIKE '%$search_esc%' OR email LIKE '%$search_esc%' OR username LIKE '%$search_esc%')";
}
$query .= " ORDER BY created_at DESC";
$users = $conn->query($query);

$total_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'employee'")->fetch_assoc()['count'];
$active_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'employee' AND is_active = 1")->fetch_assoc()['count'];
$inactive_users = $total_users - $active_users;

$page_title = 'Manage Users';
?>
<?php include '../includes/header.php'; ?>

<div class="row g-4">
    <div class="col-lg-3">
        <?php include 'sidebar.php'; ?>
    </div>

    <div class="col-lg-9">
        <div class="page-header">
            <h1 class="page-title">Manage Users</h1>
            <p class="page-subtitle">View and manage employee accounts</p>
        </div>

        <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= $success ?>
        </div>
        <?php endif; ?>

        <div class="stats-grid mb-4" style="grid-template-columns: repeat(3, 1fr);">
            <div class="stat-card">
                <div class="stat-card-header"><div class="stat-card-icon green"><i class="fas fa-users"></i></div></div>
                <div class="stat-card-value"><?= $total_users ?></div>
                <div class="stat-card-label">Total Users</div>
            </div>
            <div class="stat-card success">
                <div class="stat-card-header"><div class="stat-card-icon green"><i class="fas fa-user-check"></i></div></div>
                <div class="stat-card-value"><?= $active_users ?></div>
                <div class="stat-card-label">Active</div>
            </div>
            <div class="stat-card danger">
                <div class="stat-card-header"><div class="stat-card-icon red"><i class="fas fa-user-slash"></i></div></div>
                <div class="stat-card-value"><?= $inactive_users ?></div>
                <div class="stat-card-label">Inactive</div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-9">
                        <input type="text" name="search" class="form-control" placeholder="Search by name, email or username..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn-primary w-100"><i class="fas fa-search"></i> Search</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>User</th><th>Contact</th><th>Department</th><th>Leaves</th><th>Status</th><th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($users->num_rows > 0): ?>
                                <?php while ($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <img src="<?= getProfilePic($user['id']) ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                            <div>
                                                <strong><?= htmlspecialchars($user['full_name']) ?></strong>
                                                <br><small style="color: var(--text-muted);">@<?= htmlspecialchars($user['username']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <small><?= htmlspecialchars($user['email']) ?></small>
                                        <br><small style="color: var(--text-muted);"><?= htmlspecialchars($user['phone'] ?? 'N/A') ?></small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($user['department'] ?? 'N/A') ?>
                                        <br><small style="color: var(--text-muted);"><?= htmlspecialchars($user['position'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">Total: <?= $user['total_leaves'] ?></span>
                                        <br><span class="badge badge-warning">Used: <?= $user['used_leaves'] ?></span>
                                        <br><span class="badge badge-success">Left: <?= $user['remaining_leaves'] ?></span>
                                    </td>
                                    <td>
                                        <?php if ($user['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                        <span class="badge badge-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary mb-1" onclick="openLeaveModal(<?= $user['id'] ?>, '<?= htmlspecialchars(addslashes($user['full_name'])) ?>', <?= $user['total_leaves'] ?>, <?= $user['used_leaves'] ?>)">
                                            <i class="fas fa-edit"></i> Leaves
                                        </button>
                                        <br>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <input type="hidden" name="new_status" value="<?= $user['is_active'] ? 0 : 1 ?>">
                                            <button type="submit" name="toggle_status" class="btn btn-sm <?= $user['is_active'] ? 'btn-danger' : 'btn-primary' ?>" onclick="return confirm('<?= $user['is_active'] ? 'Deactivate' : 'Activate' ?> this user?')">
                                                <i class="fas fa-<?= $user['is_active'] ? 'user-slash' : 'user-check' ?>"></i>
                                                <?= $user['is_active'] ? 'Deactivate' : 'Activate' ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center" style="padding: 60px;">
                                        <i class="fas fa-users-slash" style="font-size: 3rem; color: var(--gray-300); display: block; margin-bottom: 16px;"></i>
                                        <h5 style="color: var(--text-secondary);">No users found</h5>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="leaveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Leave Balance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="leaveUserId">
                    <p>Updating leaves for: <strong id="leaveUserName"></strong></p>
                    <div class="form-group">
                        <label class="form-label">Total Leaves</label>
                        <input type="number" name="total_leaves" id="totalLeaves" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Used Leaves</label>
                        <input type="number" name="used_leaves" id="usedLeaves" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Remaining (Auto)</label>
                        <input type="number" id="remainingLeaves" class="form-control" readonly style="background: var(--primary-bg); font-weight: 700; color: var(--primary);">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_leaves" class="btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openLeaveModal(userId, userName, total, used) {
    const modal = new bootstrap.Modal(document.getElementById('leaveModal'));
    document.getElementById('leaveUserId').value = userId;
    document.getElementById('leaveUserName').textContent = userName;
    document.getElementById('totalLeaves').value = total;
    document.getElementById('usedLeaves').value = used;
    document.getElementById('remainingLeaves').value = total - used;

    document.getElementById('totalLeaves').addEventListener('input', calculateRemaining);
    document.getElementById('usedLeaves').addEventListener('input', calculateRemaining);

    modal.show();
}

function calculateRemaining() {
    const total = parseInt(document.getElementById('totalLeaves').value) || 0;
    const used = parseInt(document.getElementById('usedLeaves').value) || 0;
    document.getElementById('remainingLeaves').value = Math.max(0, total - used);
}
</script>

<?php include '../includes/footer.php'; ?>