<?php
require_once '../config/database.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$balance = getLeaveBalance($user_id);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_type = sanitize($_POST['leave_type'] ?? '');
    $start_date = sanitize($_POST['start_date'] ?? '');
    $end_date = sanitize($_POST['end_date'] ?? '');
    $reason = sanitize($_POST['reason'] ?? '');

    $total_days = calculateDays($start_date, $end_date);

    if (empty($leave_type) || empty($start_date) || empty($end_date) || empty($reason)) {
        $error = 'Please fill in all required fields.';
    } elseif (strtotime($start_date) < strtotime(date('Y-m-d'))) {
        $error = 'Start date cannot be in the past.';
    } elseif (strtotime($end_date) < strtotime($start_date)) {
        $error = 'End date must be after start date.';
    } elseif ($total_days > $balance['remaining_leaves']) {
        $error = "Insufficient leave balance. You have {$balance['remaining_leaves']} days remaining.";
    } elseif (checkLeaveConflict($user_id, $start_date, $end_date)) {
        $error = 'You already have a leave request for this period.';
    } else {
        // Handle document upload
        $document_name = null;
        if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['document'];
            $allowed = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];

            if (in_array($file['type'], $allowed) && $file['size'] < 5 * 1024 * 1024) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $document_name = 'doc_' . $user_id . '_' . time() . '.' . $ext;

                if (!is_dir(DOCUMENT_UPLOAD_PATH)) {
                    mkdir(DOCUMENT_UPLOAD_PATH, 0777, true);
                }

                move_uploaded_file($file['tmp_name'], DOCUMENT_UPLOAD_PATH . $document_name);
            }
        }

        $stmt = $conn->prepare("INSERT INTO leave_requests (user_id, leave_type, start_date, end_date, total_days, reason, document) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssiss", $user_id, $leave_type, $start_date, $end_date, $total_days, $reason, $document_name);

        if ($stmt->execute()) {
            $leave_id = $conn->insert_id;

            addNotification($user_id, 'Leave Request Submitted', "Your $leave_type leave request has been submitted for approval.", 'info', 'leave-history.php');
            logActivity($user_id, 'leave_applied', "Applied for $leave_type leave ($total_days days)");

            $admins = $conn->query("SELECT id FROM users WHERE role = 'admin' AND is_active = 1");
            while ($admin = $admins->fetch_assoc()) {
                addNotification($admin['id'], 'New Leave Request', "{$_SESSION['full_name']} has submitted a $leave_type leave request.", 'warning', '../admin/manage-leaves.php');
            }

            $success = "Leave request submitted successfully! Your request is now pending approval.";
            $_POST = [];
        } else {
            $error = 'Failed to submit leave request. Please try again.';
        }
    }
}

$page_title = 'Apply for Leave';
?>
<?php include '../includes/header.php'; ?>

<div class="page-header">
    <h1 class="page-title">Apply for Leave</h1>
    <p class="page-subtitle">Submit a new leave request for approval</p>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-paper-plane"></i>
                Leave Request Form
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data" id="leaveForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Leave Type *</label>
                            <select name="leave_type" class="form-control" required>
                                <option value="">Select Leave Type</option>
                                <option value="Annual" <?= ($_POST['leave_type'] ?? '') == 'Annual' ? 'selected' : '' ?>>Annual Leave</option>
                                <option value="Sick" <?= ($_POST['leave_type'] ?? '') == 'Sick' ? 'selected' : '' ?>>Sick Leave</option>
                                <option value="Casual" <?= ($_POST['leave_type'] ?? '') == 'Casual' ? 'selected' : '' ?>>Casual Leave</option>
                                <option value="Maternity" <?= ($_POST['leave_type'] ?? '') == 'Maternity' ? 'selected' : '' ?>>Maternity Leave</option>
                                <option value="Paternity" <?= ($_POST['leave_type'] ?? '') == 'Paternity' ? 'selected' : '' ?>>Paternity Leave</option>
                                <option value="Study" <?= ($_POST['leave_type'] ?? '') == 'Study' ? 'selected' : '' ?>>Study Leave</option>
                                <option value="Other" <?= ($_POST['leave_type'] ?? '') == 'Other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Total Days</label>
                            <input type="text" id="total_days_display" class="form-control" readonly style="background: var(--primary-bg); font-weight: 700; color: var(--primary);" placeholder="Auto-calculated">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Start Date *</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" required min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">End Date *</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" required min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($_POST['end_date'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Reason for Leave *</label>
                        <textarea name="reason" class="form-control" required placeholder="Please provide a detailed reason for your leave request..."><?= htmlspecialchars($_POST['reason'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Supporting Document (Optional)</label>
                        <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <div class="form-help">Max 5MB. Allowed formats: PDF, JPG, PNG</div>
                    </div>

                    <div class="d-flex gap-3 mt-4">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-paper-plane"></i>
                            Submit Request
                        </button>
                        <a href="dashboard.php" class="btn-secondary">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-wallet"></i>
                Your Leave Balance
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div style="font-size: 3rem; font-weight: 800; color: var(--primary); line-height: 1;">
                        <?= $balance['remaining_leaves'] ?>
                    </div>
                    <div style="color: var(--text-muted);">days remaining</div>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span style="font-size: 0.9rem;">Total</span>
                    <span style="font-weight: 600;"><?= $balance['total_leaves'] ?> days</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span style="font-size: 0.9rem;">Used</span>
                    <span style="font-weight: 600;"><?= $balance['used_leaves'] ?> days</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="fas fa-info-circle"></i>
                Guidelines
            </div>
            <div class="card-body">
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li class="d-flex gap-2 mb-3">
                        <i class="fas fa-check-circle" style="color: var(--primary); margin-top: 4px;"></i>
                        <span style="font-size: 0.9rem; color: var(--text-secondary);">Submit requests at least 3 days in advance</span>
                    </li>
                    <li class="d-flex gap-2 mb-3">
                        <i class="fas fa-check-circle" style="color: var(--primary); margin-top: 4px;"></i>
                        <span style="font-size: 0.9rem; color: var(--text-secondary);">Ensure you have sufficient leave balance</span>
                    </li>
                    <li class="d-flex gap-2 mb-3">
                        <i class="fas fa-check-circle" style="color: var(--primary); margin-top: 4px;"></i>
                        <span style="font-size: 0.9rem; color: var(--text-secondary);">Attach supporting documents for medical leaves</span>
                    </li>
                    <li class="d-flex gap-2">
                        <i class="fas fa-check-circle" style="color: var(--primary); margin-top: 4px;"></i>
                        <span style="font-size: 0.9rem; color: var(--text-secondary);">You will be notified once your request is processed</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    const totalDaysDisplay = document.getElementById('total_days_display');

    function calculateDays() {
        if (startDate.value && endDate.value) {
            const start = new Date(startDate.value);
            const end = new Date(endDate.value);
            if (end >= start) {
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                totalDaysDisplay.value = diffDays + ' day' + (diffDays > 1 ? 's' : '');
            } else {
                totalDaysDisplay.value = 'Invalid dates';
            }
        }
    }

    startDate.addEventListener('change', calculateDays);
    endDate.addEventListener('change', calculateDays);

    startDate.addEventListener('change', function() {
        endDate.min = this.value;
        if (endDate.value && endDate.value < this.value) {
            endDate.value = this.value;
        }
        calculateDays();
    });
});
</script>

<?php include '../includes/footer.php'; ?>