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
    if (isset($_POST['update_settings'])) {
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'setting_') === 0) {
                $setting_key = str_replace('setting_', '', $key);
                $setting_value = sanitize($value);

                $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
                $stmt->bind_param("ss", $setting_value, $setting_key);
                $stmt->execute();
            }
        }
        $success = "Settings updated successfully!";
    }

    if (isset($_POST['add_holiday'])) {
        $name = sanitize($_POST['holiday_name'] ?? '');
        $date = sanitize($_POST['holiday_date'] ?? '');
        $type = sanitize($_POST['holiday_type'] ?? 'Public');

        if (empty($name) || empty($date)) {
            $error = "Holiday name and date are required.";
        } else {
            $stmt = $conn->prepare("INSERT INTO holidays (name, date, type) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $date, $type);

            if ($stmt->execute()) {
                logActivity($_SESSION['user_id'], 'holiday_added', "Added holiday: $name");
                $success = "Holiday added successfully!";
            }
        }
    }

    if (isset($_POST['delete_holiday'])) {
        $id = intval($_POST['holiday_id']);
        $stmt = $conn->prepare("DELETE FROM holidays WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $success = "Holiday deleted successfully!";
        }
    }
}

$settings_result = $conn->query("SELECT * FROM settings");
$settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$holidays = $conn->query("SELECT * FROM holidays ORDER BY date");

$page_title = 'Settings';
?>
<?php include '../includes/header.php'; ?>

<div class="row g-4">
    <div class="col-lg-3">
        <?php include 'sidebar.php'; ?>
    </div>

    <div class="col-lg-9">
        <div class="page-header">
            <h1 class="page-title">System Settings</h1>
            <p class="page-subtitle">Configure system preferences and holidays</p>
        </div>

        <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header"><i class="fas fa-building"></i> Company Settings</div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="setting_company_name" class="form-control" value="<?= htmlspecialchars($settings['company_name'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Company Email</label>
                            <input type="email" name="setting_company_email" class="form-control" value="<?= htmlspecialchars($settings['company_email'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Company Phone</label>
                            <input type="text" name="setting_company_phone" class="form-control" value="<?= htmlspecialchars($settings['company_phone'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Default Leave Days</label>
                            <input type="number" name="setting_default_leaves" class="form-control" value="<?= htmlspecialchars($settings['default_leaves'] ?? '20') ?>">
                        </div>
                    </div>
                    <button type="submit" name="update_settings" class="btn-primary">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><i class="fas fa-calendar-check"></i> Holiday Management</div>
            <div class="card-body">
                <form method="POST" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <input type="text" name="holiday_name" class="form-control" placeholder="Holiday Name" required>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="holiday_date" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <select name="holiday_type" class="form-control">
                            <option value="Public">Public</option>
                            <option value="Company">Company</option>
                            <option value="Optional">Optional</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="add_holiday" class="btn-primary w-100">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr><th>Name</th><th>Date</th><th>Type</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php if ($holidays->num_rows > 0): ?>
                                <?php while ($holiday = $holidays->fetch_assoc()): ?>
                                <tr>
                                    <td>🎉 <?= htmlspecialchars($holiday['name']) ?></td>
                                    <td><?= date('M d, Y', strtotime($holiday['date'])) ?></td>
                                    <td><span class="badge badge-warning"><?= $holiday['type'] ?></span></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="holiday_id" value="<?= $holiday['id'] ?>">
                                            <button type="submit" name="delete_holiday" class="btn btn-sm btn-danger" onclick="return confirm('Delete this holiday?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center" style="padding: 40px; color: var(--text-muted);">No holidays added yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="fas fa-info-circle"></i> System Information</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>PHP Version:</strong> <?= phpversion() ?></p>
                        <p><strong>Server:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' ?></p>
                        <p><strong>Database:</strong> MySQL</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Total Users:</strong> <?= $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0] ?></p>
                        <p><strong>Total Requests:</strong> <?= $conn->query("SELECT COUNT(*) FROM leave_requests")->fetch_row()[0] ?></p>
                        <p><strong>Last Updated:</strong> <?= date('M d, Y h:i A') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>