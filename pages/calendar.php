<?php
require_once '../config/database.php';

requireLogin();

$user_id = $_SESSION['user_id'];

// Fetch user's leave requests
$stmt = $conn->prepare("SELECT * FROM leave_requests WHERE user_id = ? AND status IN ('Approved', 'Pending') ORDER BY start_date");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$leaves = $stmt->get_result();

// Fetch holidays
$holidays = $conn->query("SELECT * FROM holidays ORDER BY date");

$page_title = 'Leave Calendar';
?>
<?php include '../includes/header.php'; ?>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Leave Calendar</h1>
        <p class="page-subtitle">View your leaves and upcoming holidays</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <span class="badge badge-success"><i class="fas fa-circle" style="font-size: 8px;"></i> Approved</span>
        <span class="badge badge-warning"><i class="fas fa-circle" style="font-size: 8px;"></i> Pending</span>
        <span class="badge badge-danger"><i class="fas fa-circle" style="font-size: 8px;"></i> Holiday</span>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div id="calendar"></div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

<style>
.fc {
    font-family: 'Inter', sans-serif;
}
.fc .fc-button-primary {
    background: var(--primary) !important;
    border-color: var(--primary) !important;
    border-radius: var(--radius) !important;
    font-weight: 600;
}
.fc .fc-button-primary:hover {
    background: var(--primary-dark) !important;
    border-color: var(--primary-dark) !important;
}
.fc .fc-button-primary:not(:disabled).fc-button-active {
    background: var(--primary-dark) !important;
}
.fc .fc-toolbar-title {
    font-size: 1.25rem !important;
    font-weight: 700 !important;
    color: var(--text-primary);
}
.fc .fc-daygrid-day-number {
    color: var(--text-primary);
    font-weight: 500;
}
.fc .fc-col-header-cell-cushion {
    color: var(--text-secondary);
    font-weight: 600;
    text-decoration: none;
}
.fc-event {
    border-radius: 6px !important;
    padding: 2px 6px !important;
    font-size: 0.8rem !important;
    font-weight: 500;
    cursor: pointer;
}
.fc-day-today {
    background: var(--primary-bg) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    var events = [
        <?php while ($leave = $leaves->fetch_assoc()): ?>
        {
            title: '<?= addslashes($leave['leave_type']) ?> (<?= $leave['total_days'] ?>d)',
            start: '<?= $leave['start_date'] ?>',
            end: '<?= date('Y-m-d', strtotime($leave['end_date'] . ' +1 day')) ?>',
            color: '<?= $leave['status'] == 'Approved' ? '#10b981' : '#f59e0b' ?>',
            textColor: 'white'
        },
        <?php endwhile; ?>
        <?php 
        $holidays->data_seek(0);
        while ($holiday = $holidays->fetch_assoc()): 
        ?>
        {
            title: '🎉 <?= addslashes($holiday['name']) ?>',
            start: '<?= $holiday['date'] ?>',
            color: '#ef4444',
            textColor: 'white'
        },
        <?php endwhile; ?>
    ];
    
    // Remove trailing comma
    if (events.length > 0) {
        events = events.filter(e => e);
    }
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        events: events,
        height: 'auto',
        buttonText: {
            today: 'Today',
            month: 'Month',
            week: 'Week'
        },
        eventClick: function(info) {
            alert('📅 ' + info.event.title);
        },
        dayCellDidMount: function(info) {
            if (info.date.getDay() === 5) { // Friday
                info.el.style.backgroundColor = 'rgba(239, 68, 68, 0.05)';
            }
        }
    });
    
    calendar.render();
});
</script>

<?php include '../includes/footer.php'; ?>