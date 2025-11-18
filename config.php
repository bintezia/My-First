<?php
// Start session only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'hr_management');
define('DB_USER', 'root');
define('DB_PASS', '');

// Create database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['emp_id']);
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: ../login.php");
        exit();
    }
}

// Get user role
function getUserRole() {
    return $_SESSION['role'] ?? 'guest';
}

// Get dashboard statistics
function getDashboardStats($pdo) {
    $stats = [];
    
    try {
        // Total employees
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM employees");
        $stats['total_employees'] = $stmt->fetchColumn();
        
        // Active employees
        $stmt = $pdo->query("SELECT COUNT(*) as active FROM employees WHERE status = 'active'");
        $stats['active_employees'] = $stmt->fetchColumn();
        
        // Total departments
        $stmt = $pdo->query("SELECT COUNT(*) as depts FROM departments");
        $stats['total_departments'] = $stmt->fetchColumn();
        
        // Open positions
        $stmt = $pdo->query("SELECT COUNT(*) as jobs FROM job_postings WHERE status = 'open'");
        $stats['open_positions'] = $stmt->fetchColumn();
        
        // Today's attendance
        $today = date('Y-m-d');
        $stmt = $pdo->prepare("SELECT COUNT(*) as present FROM attendance WHERE date = ? AND status = 'present'");
        $stmt->execute([$today]);
        $stats['today_present'] = $stmt->fetchColumn();
        
        // Pending leave requests
        $stmt = $pdo->query("SELECT COUNT(*) as pending_leaves FROM leave_requests WHERE status = 'pending'");
        $stats['pending_leaves'] = $stmt->fetchColumn();
        
    } catch(PDOException $e) {
        error_log("Dashboard stats error: " . $e->getMessage());
    }
    
    return $stats;
}
?>