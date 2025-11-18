<?php
require_once 'config.php';
redirectBasedOnRole();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
        }
        .feature-icon {
            font-size: 3rem;
            color: #667eea;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-people-fill"></i> HR Management System
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="login.php">Login</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold">Welcome to HRMS</h1>
            <p class="lead">Complete Human Resource Management Solution</p>
            <a href="login.php" class="btn btn-light btn-lg mt-3">Get Started</a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 mb-4">
                    <i class="bi bi-people feature-icon"></i>
                    <h4>Employee Management</h4>
                    <p>Manage employee records, profiles, and information</p>
                </div>
                <div class="col-md-3 mb-4">
                    <i class="bi bi-calendar-check feature-icon"></i>
                    <h4>Attendance Tracking</h4>
                    <p>Track employee attendance and working hours</p>
                </div>
                <div class="col-md-3 mb-4">
                    <i class="bi bi-cash-coin feature-icon"></i>
                    <h4>Payroll System</h4>
                    <p>Automated payroll processing and management</p>
                </div>
                <div class="col-md-3 mb-4">
                    <i class="bi bi-graph-up feature-icon"></i>
                    <h4>Performance Reviews</h4>
                    <p>Employee performance evaluation and tracking</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white text-center py-4">
        <div class="container">
            <p>&copy; 2024 HR Management System. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>