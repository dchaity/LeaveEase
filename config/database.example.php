<?php
// ============================================
// LEAVEEASE - DATABASE CONFIGURATION TEMPLATE
// ============================================
// Copy this file to config/database.php and fill in your credentials

date_default_timezone_set('Asia/Dhaka');

define('DEBUG_MODE', false);

if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// ============================================
// DATABASE CREDENTIALS — CHANGE THESE!
// ============================================
define('DB_HOST', 'localhost');              // e.g. sql310.infinityfree.com
define('DB_USER', 'your_db_username');       // e.g. if0_42349364
define('DB_PASS', 'your_db_password');       // your password
define('DB_NAME', 'your_db_name');           // e.g. if0_42349364_Leave_ease

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    if (DEBUG_MODE) {
        die("Connection failed: " . $conn->connect_error);
    } else {
        die("Database connection error. Please try again later.");
    }
}

$conn->set_charset("utf8mb4");
$conn->query("SET time_zone = '+06:00'");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// BASE URL CONFIGURATION
// ============================================
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

$script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$base_dir = preg_replace('#/(pages|admin)$#', '', $script_dir);
$base_dir = rtrim($base_dir, '/') . '/';
if ($base_dir === '/') { $base_dir = '/'; }

define('BASE_URL', $protocol . $host . $base_dir);
define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('ADMIN_URL', BASE_URL . 'admin/');
define('ASSETS_URL', BASE_URL . 'assets/');
define('UPLOAD_PATH', BASE_PATH . 'assets/uploads/profiles/');
define('DOCUMENT_UPLOAD_PATH', BASE_PATH . 'assets/uploads/documents/');

// ============================================
// (Copy the rest of your database.php functions here)
// ============================================
// ... [all your functions: isLoggedIn, isAdmin, sanitize, etc.]
?>