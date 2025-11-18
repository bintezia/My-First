<?php
require_once 'includes/config.php';
requireLogin();

// Get dashboard statistics
try {
    // Total employees
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM employees");
    $total_employees = $stmt->fetchColumn();
    
    // Active employees
    $stmt = $pdo->query("SELECT COUNT(*) as active FROM employees WHERE status = 'active'");
    $active_employees = $stmt->fetchColumn();
    
    // Total departments
    $stmt = $pdo->query("SELECT COUNT(*) as depts FROM departments");
    $total_departments = $stmt->fetchColumn();
    
    // Open positions
    $stmt = $pdo->query("SELECT COUNT(*) as jobs FROM job_postings WHERE status = 'open'");
    $open_positions = $stmt->fetchColumn();
    
    // Today's attendance
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT COUNT(*) as present FROM attendance WHERE date = ? AND status = 'present'");
    $stmt->execute([$today]);
    $today_present = $stmt->fetchColumn();
    
    // Pending leave requests
    $stmt = $pdo->query("SELECT COUNT(*) as pending_leaves FROM leave_requests WHERE status = 'pending'");
    $pending_leaves = $stmt->fetchColumn();
    
    // Recent employees
    $stmt = $pdo->query("SELECT e.*, d.dept_name FROM employees e LEFT JOIN departments d ON e.dept_id = d.dept_id ORDER BY e.emp_id DESC LIMIT 5");
    $recent_employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Recent activities
    $stmt = $pdo->query("
        (SELECT 'leave' as type, CONCAT('Leave request from ', e.first_name) as activity, l.created_at 
         FROM leave_requests l 
         JOIN employees e ON l.emp_id = e.emp_id 
         ORDER BY l.created_at DESC LIMIT 3)
        UNION
        (SELECT 'attendance' as type, CONCAT('Late arrival - ', e.first_name) as activity, a.created_at 
         FROM attendance a 
         JOIN employees e ON a.emp_id = e.emp_id 
         WHERE a.status = 'late' 
         ORDER BY a.created_at DESC LIMIT 3)
        ORDER BY created_at DESC LIMIT 5
    ");
    $recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Error loading dashboard data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - HRMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .stat-card {
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .activity-item {
            border-left: 3px solid #007bff;
            padding-left: 15px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-people-fill"></i> HR Management System
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="bi bi-person-circle"></i> 
                    <?php echo $_SESSION['user_name']; ?> (<?php echo $_SESSION['role']; ?>)
                </span>
                <a class="nav-link" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="list-group">
                    <a href="dashboard.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a href="employees.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-people"></i> Employees
                    </a>
                    <a href="attendance.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-calendar-check"></i> Attendance
                    </a>
                    <a href="payroll.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-cash-coin"></i> Payroll
                    </a>
                    <a href="recruitment.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-person-plus"></i> Recruitment
                    </a>
                    <a href="performance.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-graph-up"></i> Performance
                    </a>
                    <a href="training.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-book"></i> Training
                    </a>
                    <a href="leaves.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-calendar-event"></i> Leave Management
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9">
                <h2><i class="bi bi-speedometer2"></i> Dashboard Overview</h2>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="card text-white bg-primary stat-card">
                            <div class="card-body text-center">
                                <h5>Total Employees</h5>
                                <h2><?php echo $total_employees; ?></h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <div class="card text-white bg-success stat-card">
                            <div class="card-body text-center">
                                <h5>Active</h5>
                                <h2><?php echo $active_employees; ?></h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <div class="card text-white bg-warning stat-card">
                            <div class="card-body text-center">
                                <h5>Departments</h5>
                                <h2><?php echo $total_departments; ?></h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <div class="card text-white bg-info stat-card">
                            <div class="card-body text-center">
                                <h5>Open Jobs</h5>
                                <h2><?php echo $open_positions; ?></h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <div class="card text-white bg-secondary stat-card">
                            <div class="card-body text-center">
                                <h5>Present Today</h5>
                                <h2><?php echo $today_present; ?></h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <div class="card text-white bg-danger stat-card">
                            <div class="card-body text-center">
                                <h5>Pending Leaves</h5>
                                <h2><?php echo $pending_leaves; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Recent Employees -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-clock-history"></i> Recent Employees
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Position</th>
                                                <th>Department</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($recent_employees as $employee): ?>
                                            <tr>
                                                <td>#<?php echo $employee['emp_id']; ?></td>
                                                <td>
                                                    <strong><?php echo $employee['first_name'] . ' ' . $employee['last_name']; ?></strong>
                                                </td>
                                                <td><?php echo $employee['email']; ?></td>
                                                <td><?php echo $employee['job_title']; ?></td>
                                                <td><?php echo $employee['dept_name']; ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $employee['status'] == 'active' ? 'success' : 
                                                            ($employee['status'] == 'on_leave' ? 'warning' : 'secondary'); 
                                                    ?>">
                                                        <?php echo ucfirst($employee['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Activities -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-activity"></i> Recent Activities
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if(!empty($recent_activities)): ?>
                                    <?php foreach($recent_activities as $activity): ?>
                                    <div class="activity-item">
                                        <small class="text-muted">
                                            <?php echo date('M j, g:i A', strtotime($activity['created_at'])); ?>
                                        </small>
                                        <p class="mb-1"><?php echo $activity['activity']; ?></p>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">No recent activities</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-lightning"></i> Quick Actions
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="employees.php" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-person-plus"></i> Manage Employees
                                    </a>
                                    <a href="attendance.php" class="btn btn-outline-success btn-sm">
                                        <i class="bi bi-calendar-check"></i> Mark Attendance
                                    </a>
                                    <a href="payroll.php" class="btn btn-outline-warning btn-sm">
                                        <i class="bi bi-cash-coin"></i> Process Payroll
                                    </a>
                                    <a href="leaves.php" class="btn btn-outline-info btn-sm">
                                        <i class="bi bi-calendar-event"></i> Manage Leaves
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>