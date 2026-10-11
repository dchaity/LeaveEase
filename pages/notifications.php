<?php
require_once '../config/database.php';

requireLogin();

$user_id = $_SESSION['user_id'];

// Mark all as read
if (isset($_POST['mark_all_read'])) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    header('Location: notifications.php');
    exit();
}

// Delete notification
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    header('Location: notifications.php');
    exit();
}

// Mark single as read
if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    $id = intval($_GET['read']);
    $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    header('Location: notifications.php');
    exit();
}

// Fetch notifications
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result();

$unread_count = getUnreadNotificationCount($user_id);

$page_title = 'Notifications';
?>
<?php include '../includes/header.php'; ?>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">
            Notifications
            <?php if ($unread_count > 0): ?>
            <span class="badge badge-danger ms-2"><?= $unread_count ?> new</span>
            <?php endif; ?>
        </h1>
        <p class="page-subtitle">Stay updated with your leave requests and system updates</p>
    </div>
    <?php if ($unread_count > 0): ?>
    <form method="POST">
        <button type="submit" name="mark_all_read" class="btn-primary">
            <i class="fas fa-check-double"></i> Mark All Read
        </button>
    </form>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        <?php if ($notifications->num_rows > 0): ?>
            <?php while ($notif = $notifications->fetch_assoc()): ?>
            <div class="notification-item <?= $notif['is_read'] ? '' : 'unread' ?>" 
                 style="border-radius: 0; border-left: none; border-right: none; margin-bottom: 0;">
                <div class="notification-icon <?= $notif['type'] ?>">
                    <?php
                    $icon = match($notif['type']) {
                        'success' => 'fa-check-circle',
                        'warning' => 'fa-exclamation-triangle',
                        'error' => 'fa-times-circle',
                        default => 'fa-info-circle'
                    };
                    ?>
                    <i class="fas <?= $icon ?>"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-title"><?= htmlspecialchars($notif['title']) ?></div>
                    <div class="notification-message"><?= htmlspecialchars($notif['message']) ?></div>
                    <div class="notification-time">
                        <i class="far fa-clock"></i>
                        <?= timeAgo($notif['created_at']) ?>
                    </div>
                </div>
                <div class="d-flex gap-2 align-items-start">
                    <?php if (!$notif['is_read']): ?>
                    <a href="?read=<?= $notif['id'] ?>" class="btn btn-sm btn-outline" title="Mark as read">
                        <i class="fas fa-check"></i>
                    </a>
                    <?php endif; ?>
                    <?php if ($notif['link']): ?>
                    <a href="<?= $notif['link'] ?>" class="btn btn-sm btn-outline" title="View">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                    <?php endif; ?>
                    <a href="?delete=<?= $notif['id'] ?>" class="btn btn-sm btn-outline" 
                       style="color: var(--danger); border-color: var(--danger);"
                       onclick="return confirm('Delete this notification?')" title="Delete">
                        <i class="fas fa-trash"></i>
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
        <div class="text-center" style="padding: 60px;">
            <i class="fas fa-bell-slash" style="font-size: 3rem; color: var(--gray-300); display: block; margin-bottom: 16px;"></i>
            <h5 style="color: var(--text-secondary);">No notifications yet</h5>
            <p style="color: var(--text-muted);">You'll see notifications here when you receive updates.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>