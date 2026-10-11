<?php
require_once '../config/database.php';

// Emergency session reset
if (isset($_GET['reset']) && $_GET['reset'] === '1') {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

// If already logged in, validate session before redirecting
if (isLoggedIn()) {
    // Role must be set
    if (!isset($_SESSION['role']) || empty($_SESSION['role'])) {
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . 'pages/login.php');
        exit();
    }

    // Verify user still exists and is active
    $check_stmt = $conn->prepare("SELECT id, role, is_active FROM users WHERE id = ?");
    $check_stmt->bind_param("i", $_SESSION['user_id']);
    $check_stmt->execute();
    $check_user = $check_stmt->get_result()->fetch_assoc();

    if (!$check_user || !$check_user['is_active']) {
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . 'pages/login.php');
        exit();
    }

    // Valid session — redirect
    if ($_SESSION['role'] === 'admin') {
        header('Location: ' . BASE_URL . 'admin/dashboard.php');
    } else {
        header('Location: ' . BASE_URL . 'pages/dashboard.php');
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, username, email, password, role, full_name, profile_pic FROM users WHERE email = ? AND is_active = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                // Prevent session fixation
                session_regenerate_id(true);

                // Set ALL required session fields
                $_SESSION['user_id']     = $user['id'];
                $_SESSION['username']    = $user['username'];
                $_SESSION['role']        = $user['role'];
                $_SESSION['full_name']   = $user['full_name'];
                $_SESSION['profile_pic'] = $user['profile_pic'];
                
                // Update last login
                $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $stmt->bind_param("i", $user['id']);
                $stmt->execute();
                
                // Log activity
                logActivity($user['id'], 'login', 'User logged in');
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: ' . BASE_URL . 'admin/dashboard.php');
                } else {
                    header('Location: ' . BASE_URL . 'pages/dashboard.php');
                }
                exit();
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$page_title = 'Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LeaveEase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
    <div class="auth-page">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <div class="auth-header-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h1>Welcome Back</h1>
                    <p>Sign in to your LeaveEase account</p>
                </div>
                
                <div class="auth-body">
                    <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['registered'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        Registration successful! Please login.
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <div class="input-group">
                                <i class="fas fa-envelope input-icon"></i>
                                <input type="email" name="email" class="form-control" 
                                       placeholder="Enter your email" required
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" name="password" class="form-control" 
                                       placeholder="Enter your password" required>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <label class="d-flex align-items-center gap-2" style="font-size: 0.9rem; color: var(--text-secondary);">
                                <input type="checkbox" name="remember" style="accent-color: var(--primary);">
                                Remember me
                            </label>
                            <a href="forgot-password.php" style="color: var(--primary); font-size: 0.9rem; font-weight: 500;">
                                Forgot password?
                            </a>
                        </div>
                        
                        <button type="submit" class="btn-auth">
                            <i class="fas fa-sign-in-alt"></i>
                            Sign In
                        </button>
                    </form>
                    
                    <div class="auth-footer">
                        <p>Don't have an account? <a href="register.php">Create one</a></p>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="<?= BASE_URL ?>index.php" style="color: var(--text-muted); font-size: 0.9rem;">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>