<?php
require_once '../config/database.php';

requireLogin();

$user_id = $_SESSION['user_id'];   // ← Each user's own ID
$error = '';
$success = '';

// Fetch current user
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$current_pic = $user['profile_pic'] ?? 'default.png';

// ============================================
// HANDLE PROFILE PICTURE UPLOAD
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_picture'])) {
    if (!isset($_FILES['profile_pic']) || $_FILES['profile_pic']['error'] !== UPLOAD_ERR_OK) {
        $err_code = $_FILES['profile_pic']['error'] ?? 'no file';
        $error = "Upload failed. Error code: $err_code";
    } else {
        $file = $_FILES['profile_pic'];
        $allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];

        if (!in_array($file['type'], $allowed)) {
            $error = "Only JPG, PNG, GIF, and WEBP files are allowed. (Got: " . htmlspecialchars($file['type']) . ")";
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $error = "File size must be less than 5MB. (Got: " . round($file['size'] / 1024 / 1024, 2) . "MB)";
        } else {
            if (!is_dir(UPLOAD_PATH)) {
                @mkdir(UPLOAD_PATH, 0755, true);
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
            $full_path = UPLOAD_PATH . $filename;

            // Delete user's own old custom pic (not others')
            if ($current_pic != 'default.png' && file_exists(UPLOAD_PATH . $current_pic)) {
                @unlink(UPLOAD_PATH . $current_pic);
            }

            if (move_uploaded_file($file['tmp_name'], $full_path)) {
                @chmod($full_path, 0644);

                // Update ONLY this user's record
                $update_stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                $update_stmt->bind_param("si", $filename, $user_id);

                if ($update_stmt->execute()) {
                    $_SESSION['profile_pic'] = $filename;
                    logActivity($user_id, 'profile_pic_upload', 'Uploaded new profile picture');
                    header('Location: profile.php?pic_updated=1');
                    exit();
                } else {
                    $error = "Failed to update database: " . $update_stmt->error;
                }
            } else {
                $error = "Failed to move uploaded file.";
            }
        }
    }
}

// ============================================
// HANDLE PROFILE INFO UPDATE
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name  = sanitize($_POST['full_name'] ?? '');
    $phone      = sanitize($_POST['phone'] ?? '');
    $department = sanitize($_POST['department'] ?? '');
    $position   = sanitize($_POST['position'] ?? '');
    $address    = sanitize($_POST['address'] ?? '');

    $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, department = ?, position = ?, address = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $full_name, $phone, $department, $position, $address, $user_id);

    if ($stmt->execute()) {
        $_SESSION['full_name'] = $full_name;
        logActivity($user_id, 'profile_update', 'Updated profile information');
        header('Location: profile.php?updated=1');
        exit();
    } else {
        $error = "Failed to update profile.";
    }
}

// ============================================
// HANDLE PASSWORD CHANGE
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!password_verify($current_password, $user['password'])) {
        $error = "Current password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed, $user_id);
        if ($stmt->execute()) {
            logActivity($user_id, 'password_change', 'Changed account password');
            header('Location: profile.php?pw_changed=1');
            exit();
        } else {
            $error = "Failed to change password.";
        }
    }
}

// Success messages from redirects
if (isset($_GET['pic_updated'])) $success = "Profile picture updated successfully!";
if (isset($_GET['updated']))     $success = "Profile updated successfully!";
if (isset($_GET['pw_changed']))  $success = "Password changed successfully!";

// Re-fetch in case of updates
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$current_pic = $user['profile_pic'] ?? 'default.png';

// Build image URL (cache-bust so new pic shows instantly)
if ($current_pic != 'default.png' && file_exists(UPLOAD_PATH . $current_pic)) {
    $profile_image = BASE_URL . 'assets/uploads/profiles/' . $current_pic . '?t=' . time();
} else {
    $profile_image = BASE_URL . 'assets/images/default-avatar.png';
}

$page_title = 'My Profile';
?>
<?php include '../includes/header.php'; ?>

<div class="page-header">
    <h1 class="page-title">My Profile</h1>
    <p class="page-subtitle">Manage your account settings and preferences</p>
</div>

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

<div class="row g-4">
    <!-- Profile Card -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center">

                <!-- ===== PROFILE PICTURE UPLOAD FORM ===== -->
                <form method="POST" enctype="multipart/form-data" id="picForm">
                    <input type="hidden" name="upload_picture" value="1">
                    <input type="file" name="profile_pic" id="profilePicInput" accept="image/*" style="display: none;">

                    <div class="profile-avatar-container mx-auto mb-3" style="position: relative; display: inline-block;">
                        <img src="<?= $profile_image ?>" alt="Profile" class="profile-avatar" id="profilePreview"
                             style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid var(--primary);">

                        <label for="profilePicInput" class="profile-avatar-upload"
                               style="position: absolute; bottom: 5px; right: 5px; width: 44px; height: 44px;
                                      background: var(--primary); color: white; border-radius: 50%;
                                      display: flex; align-items: center; justify-content: center;
                                      cursor: pointer; border: 3px solid white;
                                      box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: transform 0.2s;"
                               onmouseover="this.style.transform='scale(1.1)'"
                               onmouseout="this.style.transform='scale(1)'">
                            <i class="fas fa-camera"></i>
                        </label>
                    </div>

                    <button type="submit" class="btn-primary btn-sm" id="uploadBtn" style="display: none;">
                        <i class="fas fa-upload"></i> Save Photo
                    </button>

                    <small style="color: var(--text-muted); display: block; margin-top: 8px; font-size: 0.8rem;">
                        Click the camera icon to choose your photo<br>
                        Max 5MB • JPG, PNG, GIF, WEBP
                    </small>
                </form>

                <h3 style="font-size: 1.25rem; font-weight: 700; margin: 20px 0 4px;">
                    <?= htmlspecialchars($user['full_name']) ?>
                </h3>
                <p style="color: var(--text-muted); margin-bottom: 8px;">
                    @<?= htmlspecialchars($user['username']) ?>
                </p>
                <span class="badge badge-<?= $user['role'] == 'admin' ? 'danger' : 'success' ?>">
                    <i class="fas fa-<?= $user['role'] == 'admin' ? 'shield-alt' : 'user' ?>"></i>
                    <?= ucfirst($user['role']) ?>
                </span>

                <hr style="margin: 24px 0;">

                <div class="text-start">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <i class="fas fa-envelope" style="color: var(--primary); width: 20px;"></i>
                        <span style="font-size: 0.9rem;"><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <i class="fas fa-phone" style="color: var(--primary); width: 20px;"></i>
                        <span style="font-size: 0.9rem;"><?= htmlspecialchars($user['phone'] ?? 'Not set') ?></span>
                    </div>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <i class="fas fa-building" style="color: var(--primary); width: 20px;"></i>
                        <span style="font-size: 0.9rem;"><?= htmlspecialchars($user['department'] ?? 'Not set') ?></span>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <i class="fas fa-calendar-alt" style="color: var(--primary); width: 20px;"></i>
                        <span style="font-size: 0.9rem;">Joined <?= date('M d, Y', strtotime($user['created_at'])) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-edit"></i>
                Edit Profile
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Department</label>
                            <select name="department" class="form-control">
                                <option value="">Select Department</option>
                                <option value="IT" <?= $user['department'] == 'IT' ? 'selected' : '' ?>>IT</option>
                                <option value="HR" <?= $user['department'] == 'HR' ? 'selected' : '' ?>>HR</option>
                                <option value="Finance" <?= $user['department'] == 'Finance' ? 'selected' : '' ?>>Finance</option>
                                <option value="Marketing" <?= $user['department'] == 'Marketing' ? 'selected' : '' ?>>Marketing</option>
                                <option value="Operations" <?= $user['department'] == 'Operations' ? 'selected' : '' ?>>Operations</option>
                                <option value="Sales" <?= $user['department'] == 'Sales' ? 'selected' : '' ?>>Sales</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Position</label>
                            <input type="text" name="position" class="form-control" value="<?= htmlspecialchars($user['position'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" name="update_profile" class="btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>

        <!-- Change Password -->
        <div class="card mt-4">
            <div class="card-header">
                <i class="fas fa-lock"></i>
                Change Password
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" name="change_password" class="btn-primary">
                        <i class="fas fa-key"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('profilePicInput').addEventListener('change', function() {
    if (this.files && this.files[0]) {
        // Preview
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').src = e.target.result;
        };
        reader.readAsDataURL(this.files[0]);

        // Show save button
        document.getElementById('uploadBtn').style.display = 'inline-flex';

        // Auto-submit after 300ms (gives preview time to render)
        setTimeout(() => {
            document.getElementById('picForm').submit();
        }, 300);
    }
});
</script>

<?php include '../includes/footer.php'; ?>