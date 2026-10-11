<?php
require_once '../config/database.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';
$user = null;

if (empty($token)) {
    header('Location: login.php');
    exit();
}

// Verify token
$stmt = $conn->prepare("SELECT id, full_name FROM users WHERE reset_token = ? AND reset_expires > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    $error = "Invalid or expired reset link. Please request a new one.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $user['id']);
        
        if ($stmt->execute()) {
            logActivity($user['id'], 'password_reset', 'Reset password via email link');
            $success = "Password reset successfully! You can now login.";
        } else {
            $error = "Failed to reset password. Please try again.";
        }
    }
}

$page_title = 'Reset Password';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - LeaveEase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-page">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <div class="auth-header-icon">
                        <i class="fas fa-lock-open"></i>
                    </div>
                    <h1>Reset Password</h1>
                    <p>Create a new password for your account</p>
                </div>
                
                <div class="auth-body">
                    <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= $success ?>
                    </div>
                    <div class="text-center">
                        <a href="login.php" class="btn-auth" style="display: inline-flex;">
                            <i class="fas fa-sign-in-alt"></i>
                            Go to Login
                        </a>
                    </div>
                    <?php elseif ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="forgot-password.php" style="color: var(--primary);">
                            Request new reset link
                        </a>
                    </div>
                    <?php elseif ($user): ?>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" name="password" class="form-control" 
                                       placeholder="Min 6 characters" required minlength="6">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" name="confirm_password" class="form-control" 
                                       placeholder="Confirm new password" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-auth">
                            <i class="fas fa-key"></i>
                            Reset Password
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>