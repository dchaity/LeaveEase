<?php
require_once '../config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = sanitize($_POST['full_name'] ?? '');
    $department = sanitize($_POST['department'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Email already registered. Please login instead.';
        } else {
            // Check if username exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            
            if ($stmt->get_result()->num_rows > 0) {
                $error = 'Username already taken. Please choose another.';
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, department, phone, role, total_leaves, remaining_leaves) VALUES (?, ?, ?, ?, ?, ?, 'employee', 20, 20)");
                $stmt->bind_param("ssssss", $username, $email, $hashed_password, $full_name, $department, $phone);
                
                if ($stmt->execute()) {
                    $user_id = $conn->insert_id;
                    
                    // Log activity
                    logActivity($user_id, 'register', 'New user registered');
                    
                    // Add welcome notification
                    addNotification($user_id, 'Welcome to LeaveEase!', 'Welcome! Your account has been created successfully. You can now apply for leaves.', 'success');
                    
                    header('Location: login.php?registered=success');
                    exit();
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }
}

$page_title = 'Register';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - LeaveEase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-page">
        <div class="auth-container" style="max-width: 520px;">
            <div class="auth-card">
                <div class="auth-header">
                    <div class="auth-header-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h1>Create Account</h1>
                    <p>Join LeaveEase today</p>
                </div>
                
                <div class="auth-body">
                    <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="full_name" class="form-control" 
                                       placeholder="John Doe" required
                                       value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Username *</label>
                                <input type="text" name="username" class="form-control" 
                                       placeholder="johndoe" required
                                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Email Address *</label>
                            <div class="input-group">
                                <i class="fas fa-envelope input-icon"></i>
                                <input type="email" name="email" class="form-control" 
                                       placeholder="john@example.com" required
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Password *</label>
                                <div class="input-group">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input type="password" name="password" class="form-control" 
                                           placeholder="Min 6 characters" required minlength="6">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Confirm Password *</label>
                                <div class="input-group">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input type="password" name="confirm_password" class="form-control" 
                                           placeholder="Confirm password" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Department</label>
                                <select name="department" class="form-control">
                                    <option value="">Select Department</option>
                                    <option value="IT" <?= ($_POST['department'] ?? '') == 'IT' ? 'selected' : '' ?>>IT</option>
                                    <option value="HR" <?= ($_POST['department'] ?? '') == 'HR' ? 'selected' : '' ?>>HR</option>
                                    <option value="Finance" <?= ($_POST['department'] ?? '') == 'Finance' ? 'selected' : '' ?>>Finance</option>
                                    <option value="Marketing" <?= ($_POST['department'] ?? '') == 'Marketing' ? 'selected' : '' ?>>Marketing</option>
                                    <option value="Operations" <?= ($_POST['department'] ?? '') == 'Operations' ? 'selected' : '' ?>>Operations</option>
                                    <option value="Sales" <?= ($_POST['department'] ?? '') == 'Sales' ? 'selected' : '' ?>>Sales</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" 
                                       placeholder="+880 1234-567890"
                                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-auth">
                            <i class="fas fa-user-plus"></i>
                            Create Account
                        </button>
                    </form>
                    
                    <div class="auth-footer">
                        <p>Already have an account? <a href="login.php">Sign in</a></p>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="../index.php" style="color: var(--text-muted); font-size: 0.9rem;">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>