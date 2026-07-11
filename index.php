<?php
require_once 'config/database.php';

// If user is logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: pages/dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeaveEase - Smart Leave Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Animated Background */
        .hero-section {
            background: linear-gradient(135deg, #f0f9ff 0%, #dcfce7 30%, #fce7f3 60%, #fef3c7 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: rgba(0, 168, 107, 0.08);
            animation: floatBg 8s ease-in-out infinite;
        }
        
        .hero-section::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: rgba(0, 168, 107, 0.05);
            animation: floatBg 10s ease-in-out infinite reverse;
        }
        
        @keyframes floatBg {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(10deg); }
        }
        
        .feature-card {
            transition: all 0.4s ease;
            border-radius: 20px;
            border: none;
            background: white;
            position: relative;
            z-index: 1;
        }
        .feature-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 20px 60px rgba(0, 168, 107, 0.2);
        }
        
        .feature-card .icon-wrapper {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #00a86b, #007a4d);
            border: none;
            border-radius: 50px;
            padding: 12px 40px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-success:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 10px 30px rgba(0, 168, 107, 0.4);
        }
        
        .btn-outline-success {
            border: 2px solid #00a86b;
            color: #00a86b;
            border-radius: 50px;
            padding: 12px 40px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-outline-success:hover {
            background: #00a86b;
            color: white;
            transform: translateY(-3px);
        }
        
        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 0;
        }
        
        .floating-shapes span {
            position: absolute;
            border-radius: 50%;
            background: rgba(0, 168, 107, 0.05);
            animation: floatShape 15s infinite linear;
        }
        
        @keyframes floatShape {
            0% { transform: translateY(0) rotate(0deg) scale(1); }
            50% { transform: translateY(-50px) rotate(180deg) scale(1.2); }
            100% { transform: translateY(0) rotate(360deg) scale(1); }
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #00a86b, #007a4d); padding: 1rem 0; position: sticky; top: 0; z-index: 1050;">
        <div class="container">
            <a class="navbar-brand fw-bold fs-3" href="#">
                <i class="fas fa-calendar-check"></i> LeaveEase
            </a>
            <div>
                <a href="pages/login.php" class="btn btn-light me-2">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <a href="pages/register.php" class="btn btn-outline-light">
                    <i class="fas fa-user-plus"></i> Register
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="floating-shapes">
            <span style="width: 300px; height: 300px; top: -100px; right: -50px; animation-duration: 20s;"></span>
            <span style="width: 200px; height: 200px; bottom: -50px; left: -50px; animation-duration: 25s; animation-delay: 2s;"></span>
            <span style="width: 150px; height: 150px; top: 50%; left: 50%; animation-duration: 18s; animation-delay: 4s;"></span>
        </div>
        
        <div class="container hero-content">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-3 fw-bold mb-3" style="color: #1a1a2e;">
                        Smart <span style="color: #00a86b;">Leave</span> Management
                    </h1>
                    <p class="lead mb-4" style="color: #555;">
                        Streamline your leave requests with LeaveEase. Track, approve, and manage leaves effortlessly with our intelligent system.
                    </p>
                    <div class="d-flex gap-3 flex-wrap mb-4">
                        <a href="pages/register.php" class="btn btn-success">
                            <i class="fas fa-rocket"></i> Get Started
                        </a>
                        <a href="#features" class="btn btn-outline-success">
                            <i class="fas fa-info-circle"></i> Learn More
                        </a>
                    </div>
                    <div class="d-flex gap-5">
                        <div>
                            <h3 class="mb-0" style="color: #00a86b;">500+</h3>
                            <small class="text-muted">Active Users</small>
                        </div>
                        <div>
                            <h3 class="mb-0" style="color: #00a86b;">1000+</h3>
                            <small class="text-muted">Leaves Processed</small>
                        </div>
                        <div>
                            <h3 class="mb-0" style="color: #00a86b;">98%</h3>
                            <small class="text-muted">Satisfaction Rate</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <div class="card shadow-lg border-0 p-5" style="border-radius: 30px; background: white; position: relative; z-index: 1;">
                        <i class="fas fa-calendar-check fa-5x" style="color: #00a86b;"></i>
                        <h3 class="mt-3">Manage Your Leaves</h3>
                        <p class="text-muted">Apply, track, and manage leave requests in one place</p>
                        <div class="d-flex justify-content-center gap-3 mt-3 flex-wrap">
                            <span class="badge bg-success p-2">Annual</span>
                            <span class="badge bg-warning p-2">Sick</span>
                            <span class="badge bg-info p-2">Casual</span>
                            <span class="badge bg-danger p-2">Maternity</span>
                            <span class="badge bg-secondary p-2">Paternity</span>
                        </div>
                        <div class="mt-3">
                            <div class="progress" style="height: 8px; border-radius: 10px;">
                                <div class="progress-bar bg-success" style="width: 75%; border-radius: 10px;"></div>
                            </div>
                            <small class="text-muted">75% of leaves approved within 24 hours</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5" style="background: white; position: relative;">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">Why Choose <span style="color: #00a86b;">LeaveEase</span>?</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card shadow-sm h-100 p-4 text-center">
                        <div class="icon-wrapper">
                            <i class="fas fa-shield-alt fa-2x" style="color: #00a86b;"></i>
                        </div>
                        <h5 class="mt-3 fw-bold">Secure System</h5>
                        <p class="text-muted">Role-based access control with encrypted passwords for maximum security.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card shadow-sm h-100 p-4 text-center">
                        <div class="icon-wrapper">
                            <i class="fas fa-clock fa-2x" style="color: #00a86b;"></i>
                        </div>
                        <h5 class="mt-3 fw-bold">Real-time Tracking</h5>
                        <p class="text-muted">Monitor leave balances and requests instantly with live updates.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card shadow-sm h-100 p-4 text-center">
                        <div class="icon-wrapper">
                            <i class="fas fa-robot fa-2x" style="color: #00a86b;"></i>
                        </div>
                        <h5 class="mt-3 fw-bold">AI Assistant</h5>
                        <p class="text-muted">Get help and answers from our intelligent chatbot 24/7.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>