<?php
require_once 'config/database.php';

$is_logged_in  = isLoggedIn();
$is_admin_user = ($is_logged_in && isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeaveEase - Smart Leave Management System</title>
    <meta name="description" content="Streamline your leave requests with LeaveEase. Track, approve, and manage leaves effortlessly.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="landing-nav">
        <div class="container">
            <div class="nav-content">
                <a href="index.php" class="brand">
                    <div class="brand-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <span>LeaveEase</span>
                </a>
                <div class="nav-actions">
                    <?php if ($is_logged_in): ?>
                        <a href="<?= $is_admin_user ? BASE_URL . 'admin/dashboard.php' : BASE_URL . 'pages/dashboard.php' ?>" class="btn btn-ghost">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="<?= BASE_URL ?>pages/logout.php" class="btn btn-primary-custom">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="pages/login.php" class="btn btn-ghost">
                            <i class="fas fa-sign-in-alt"></i> Sign In
                        </a>
                        <a href="pages/register.php" class="btn btn-primary-custom">
                            <i class="fas fa-user-plus"></i> Get Started
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-bg-elements">
            <div class="hero-circle hero-circle-1"></div>
            <div class="hero-circle hero-circle-2"></div>
            <div class="hero-circle hero-circle-3"></div>
        </div>

        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <div class="hero-badge">
                        <i class="fas fa-sparkles"></i>
                        <span>Smart Leave Management</span>
                    </div>
                    <h1 class="hero-title">
                        Manage Leaves
                        <span class="highlight">Effortlessly</span>
                    </h1>
                    <p class="hero-description">
                        Streamline your leave requests with our intelligent system.
                        Track balances, approve requests, and manage everything in one place.
                    </p>
                    <div class="hero-actions">
                        <?php if ($is_logged_in): ?>
                            <a href="<?= $is_admin_user ? BASE_URL . 'admin/dashboard.php' : BASE_URL . 'pages/dashboard.php' ?>" class="btn btn-primary-custom btn-lg">
                                <i class="fas fa-tachometer-alt"></i>
                                Go to Dashboard
                            </a>
                            <a href="<?= BASE_URL ?>pages/logout.php" class="btn btn-ghost btn-lg">
                                <i class="fas fa-sign-out-alt"></i>
                                Logout
                            </a>
                        <?php else: ?>
                            <a href="pages/register.php" class="btn btn-primary-custom btn-lg">
                                <i class="fas fa-rocket"></i>
                                Get Started Free
                            </a>
                            <a href="#features" class="btn btn-ghost btn-lg">
                                <i class="fas fa-play-circle"></i>
                                Learn More
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="hero-stats">
                        <div class="stat">
                            <span class="stat-value">500+</span>
                            <span class="stat-label">Active Users</span>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat">
                            <span class="stat-value">10K+</span>
                            <span class="stat-label">Leaves Processed</span>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat">
                            <span class="stat-value">98%</span>
                            <span class="stat-label">Satisfaction</span>
                        </div>
                    </div>
                </div>

                <div class="hero-visual">
                    <div class="dashboard-preview">
                        <div class="preview-header">
                            <div class="preview-dots">
                                <span></span><span></span><span></span>
                            </div>
                            <span class="preview-title">Dashboard</span>
                        </div>
                        <div class="preview-body">
                            <div class="preview-cards">
                                <div class="preview-card">
                                    <div class="preview-card-icon green">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                    <div class="preview-card-content">
                                        <span class="preview-card-label">Total Leaves</span>
                                        <span class="preview-card-value">20</span>
                                    </div>
                                </div>
                                <div class="preview-card">
                                    <div class="preview-card-icon orange">
                                        <i class="fas fa-hourglass-half"></i>
                                    </div>
                                    <div class="preview-card-content">
                                        <span class="preview-card-label">Used</span>
                                        <span class="preview-card-value">5</span>
                                    </div>
                                </div>
                                <div class="preview-card">
                                    <div class="preview-card-icon blue">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="preview-card-content">
                                        <span class="preview-card-label">Remaining</span>
                                        <span class="preview-card-value">15</span>
                                    </div>
                                </div>
                            </div>
                            <div class="preview-chart">
                                <div class="chart-bars">
                                    <div class="chart-bar" style="height: 40%"></div>
                                    <div class="chart-bar" style="height: 65%"></div>
                                    <div class="chart-bar" style="height: 45%"></div>
                                    <div class="chart-bar" style="height: 80%"></div>
                                    <div class="chart-bar" style="height: 55%"></div>
                                    <div class="chart-bar" style="height: 70%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="floating-badge badge-1">
                        <i class="fas fa-check-circle"></i>
                        <span>Approved</span>
                    </div>
                    <div class="floating-badge badge-2">
                        <i class="fas fa-bell"></i>
                        <span>New Request</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Features</span>
                <h2 class="section-title">Everything You Need</h2>
                <p class="section-description">
                    Powerful features designed to make leave management simple and efficient
                </p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                    <h3>Secure System</h3>
                    <p>Role-based access control with encrypted passwords for maximum security.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-clock"></i></div>
                    <h3>Real-time Tracking</h3>
                    <p>Monitor leave balances and requests instantly with live updates.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-robot"></i></div>
                    <h3>AI Assistant</h3>
                    <p>Get help and answers from our intelligent chatbot 24/7.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-calendar-alt"></i></div>
                    <h3>Leave Calendar</h3>
                    <p>Visual calendar view of all leaves and holidays at a glance.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-chart-bar"></i></div>
                    <h3>Reports & Analytics</h3>
                    <p>Detailed reports with charts and export functionality.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-bell"></i></div>
                    <h3>Smart Notifications</h3>
                    <p>Instant notifications for approvals, rejections, and updates.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Process</span>
                <h2 class="section-title">How It Works</h2>
                <p class="section-description">Get started in three simple steps</p>
            </div>

            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">01</div>
                    <div class="step-icon"><i class="fas fa-user-plus"></i></div>
                    <h3>Create Account</h3>
                    <p>Register with your email and department details.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">02</div>
                    <div class="step-icon"><i class="fas fa-paper-plane"></i></div>
                    <h3>Apply for Leave</h3>
                    <p>Select leave type, dates, and submit your request.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">03</div>
                    <div class="step-icon"><i class="fas fa-check-double"></i></div>
                    <h3>Get Approved</h3>
                    <p>Admin reviews and approves your request instantly.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2><?= $is_logged_in ? 'Ready to Continue?' : 'Ready to Get Started?' ?></h2>
                <p><?= $is_logged_in ? 'Head to your dashboard to manage your leaves.' : 'Join hundreds of companies managing leaves efficiently with LeaveEase.' ?></p>
                <?php if ($is_logged_in): ?>
                    <a href="<?= $is_admin_user ? BASE_URL . 'admin/dashboard.php' : BASE_URL . 'pages/dashboard.php' ?>" class="btn btn-white btn-lg">
                        <i class="fas fa-tachometer-alt"></i>
                        Go to Dashboard
                    </a>
                <?php else: ?>
                    <a href="pages/register.php" class="btn btn-white btn-lg">
                        <i class="fas fa-arrow-right"></i>
                        Start Free Trial
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="landing-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <div class="brand">
                        <div class="brand-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <span>LeaveEase</span>
                    </div>
                    <p>Smart leave management for modern teams.</p>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <?php if ($is_logged_in): ?>
                        <a href="<?= $is_admin_user ? BASE_URL . 'admin/dashboard.php' : BASE_URL . 'pages/dashboard.php' ?>">Dashboard</a>
                        <a href="<?= BASE_URL ?>pages/profile.php">My Profile</a>
                        <a href="<?= BASE_URL ?>pages/logout.php">Logout</a>
                    <?php else: ?>
                        <a href="pages/login.php">Login</a>
                        <a href="pages/register.php">Register</a>
                        <a href="#features">Features</a>
                    <?php endif; ?>
                </div>
                <div class="footer-contact">
                    <h4>Contact</h4>
                    <a href="mailto:support@leaveease.com">
                        <i class="fas fa-envelope"></i> support@leaveease.com
                    </a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> LeaveEase. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>