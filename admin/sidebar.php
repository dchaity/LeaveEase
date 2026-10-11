<nav class="card" style="position: sticky; top: 100px;">
    <div class="card-body" style="padding: 16px;">
        <nav style="display: flex; flex-direction: column; gap: 4px;">
            <a href="dashboard.php" style="display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: var(--radius); color: var(--text-secondary); text-decoration: none; transition: all 0.3s ease; <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'background: var(--primary-bg); color: var(--primary); font-weight: 600;' : '' ?>">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </a>
            <a href="manage-leaves.php" style="display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: var(--radius); color: var(--text-secondary); text-decoration: none; transition: all 0.3s ease; <?= basename($_SERVER['PHP_SELF']) == 'manage-leaves.php' ? 'background: var(--primary-bg); color: var(--primary); font-weight: 600;' : '' ?>">
                <i class="fas fa-list"></i>
                Manage Leaves
            </a>
            <a href="manage-users.php" style="display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: var(--radius); color: var(--text-secondary); text-decoration: none; transition: all 0.3s ease; <?= basename($_SERVER['PHP_SELF']) == 'manage-users.php' ? 'background: var(--primary-bg); color: var(--primary); font-weight: 600;' : '' ?>">
                <i class="fas fa-users"></i>
                Manage Users
            </a>
            <a href="reports.php" style="display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: var(--radius); color: var(--text-secondary); text-decoration: none; transition: all 0.3s ease; <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'background: var(--primary-bg); color: var(--primary); font-weight: 600;' : '' ?>">
                <i class="fas fa-chart-bar"></i>
                Reports
            </a>
            <a href="settings.php" style="display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: var(--radius); color: var(--text-secondary); text-decoration: none; transition: all 0.3s ease; <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'background: var(--primary-bg); color: var(--primary); font-weight: 600;' : '' ?>">
                <i class="fas fa-cog"></i>
                Settings
            </a>
        </nav>
    </div>
</nav>