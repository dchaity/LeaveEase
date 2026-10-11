<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$is_admin_area = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;

$user_id   = $_SESSION['user_id']   ?? 0;
$full_name = $_SESSION['full_name'] ?? 'User';
$role      = $_SESSION['role']      ?? 'employee';

$is_logged_in  = isLoggedIn();
$is_admin_user = ($is_logged_in && isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

$unread_count = ($is_logged_in && function_exists('getUnreadNotificationCount')) ? getUnreadNotificationCount($user_id) : 0;
$greeting = function_exists('getGreeting') ? getGreeting() : ['Hello', '👋'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - LeaveEase' : 'LeaveEase' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <?php if ($is_admin_area): ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
    <?php endif; ?>
    <script>
        const BASE_URL = '<?= BASE_URL ?>';
        const IS_LOGGED_IN = <?= $is_logged_in ? 'true' : 'false' ?>;
    </script>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="app-nav">
        <div class="container">
            <div class="app-nav-content">
                <a href="<?= $is_logged_in ? ($is_admin_user ? BASE_URL . 'admin/dashboard.php' : BASE_URL . 'pages/dashboard.php') : BASE_URL . 'index.php' ?>" class="brand">
                    <div class="brand-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <span>LeaveEase</span>
                </a>

                <?php if ($is_logged_in): ?>
                <div class="nav-links">
                    <a href="<?= BASE_URL ?>pages/dashboard.php" class="nav-link <?= $current_page == 'dashboard.php' && !$is_admin_area ? 'active' : '' ?>">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="<?= BASE_URL ?>pages/apply-leave.php" class="nav-link <?= $current_page == 'apply-leave.php' ? 'active' : '' ?>">
                        <i class="fas fa-plus-circle"></i>
                        <span>Apply Leave</span>
                    </a>
                    <a href="<?= BASE_URL ?>pages/leave-history.php" class="nav-link <?= $current_page == 'leave-history.php' ? 'active' : '' ?>">
                        <i class="fas fa-history"></i>
                        <span>History</span>
                    </a>
                    <a href="<?= BASE_URL ?>pages/calendar.php" class="nav-link <?= $current_page == 'calendar.php' ? 'active' : '' ?>">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Calendar</span>
                    </a>
                    <?php if ($is_admin_user): ?>
                    <a href="<?= BASE_URL ?>admin/dashboard.php" class="nav-link <?= $is_admin_area ? 'active' : '' ?>">
                        <i class="fas fa-shield-alt"></i>
                        <span>Admin</span>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="nav-right">
                    <?php if ($is_logged_in): ?>
                        <!-- Live Clock -->
                        <div class="live-clock">
                            <div class="live-clock-time" id="liveClock">--:--:--</div>
                            <div class="live-clock-date" id="liveDate">Loading...</div>
                        </div>

                        <!-- Notifications -->
                        <a href="<?= BASE_URL ?>pages/notifications.php" class="nav-link position-relative" style="padding: 10px;">
                            <i class="fas fa-bell"></i>
                            <?php if ($unread_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                <?= $unread_count ?>
                            </span>
                            <?php endif; ?>
                        </a>

                        <!-- User Menu -->
                        <div class="user-menu dropdown">
                            <img src="<?= getProfilePic($user_id) ?>" alt="Profile" class="user-avatar">
                            <div class="user-info d-none d-md-block">
                                <div class="user-name"><?= htmlspecialchars($full_name) ?></div>
                                <div class="user-role"><?= htmlspecialchars($role) ?></div>
                            </div>
                            <i class="fas fa-chevron-down" style="font-size: 0.7rem; color: var(--text-muted);"></i>

                            <ul class="dropdown-menu dropdown-menu-end" style="border: none; box-shadow: var(--shadow-lg); border-radius: var(--radius); margin-top: 8px;">
                                <li>
                                    <a class="dropdown-item" href="<?= BASE_URL ?>pages/profile.php" style="padding: 10px 16px;">
                                        <i class="fas fa-user-circle text-success me-2"></i> My Profile
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= BASE_URL ?>pages/notifications.php" style="padding: 10px 16px;">
                                        <i class="fas fa-bell text-warning me-2"></i> Notifications
                                        <?php if ($unread_count > 0): ?>
                                        <span class="badge bg-danger ms-2"><?= $unread_count ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <?php if ($is_admin_user): ?>
                                <li>
                                    <a class="dropdown-item" href="<?= BASE_URL ?>admin/dashboard.php" style="padding: 10px 16px;">
                                        <i class="fas fa-shield-alt text-primary me-2"></i> Admin Panel
                                    </a>
                                </li>
                                <?php endif; ?>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?= BASE_URL ?>pages/logout.php" style="padding: 10px 16px;">
                                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <!-- GUEST: Login / Register buttons -->
                        <a href="<?= BASE_URL ?>pages/login.php" class="btn btn-ghost btn-sm">
                            <i class="fas fa-sign-in-alt"></i> Sign In
                        </a>
                        <a href="<?= BASE_URL ?>pages/register.php" class="btn btn-primary-custom btn-sm">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?= $_SESSION['flash_type'] == 'success' ? 'check-circle' : 'info-circle' ?>"></i>
                <?= $_SESSION['flash_message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php
                unset($_SESSION['flash_message']);
                unset($_SESSION['flash_type']);
            endif;
            ?>