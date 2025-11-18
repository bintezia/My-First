<?php
require_once 'includes/config.php';
requireLogin();

$success = '';
$error = '';

// Handle leave actions
if ($_POST && isset($_POST['action'])) {
    if ($_POST['action'] == 'add_leave') {
        $emp_id = $_POST['emp_id'];
        $leave_type = $_POST['leave_type'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $reason = $_POST['reason'];
        
        try {
            // Calculate total days
            $start = new DateTime($start_date);
            $end = new DateTime($end_date);
            $total_days = $start->diff($end)->days + 1;
            
            $stmt = $pdo->prepare("
                INSERT INTO leave_requests (emp_id, leave_type, start_date, end_date, total_days, reason) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$emp_id, $leave_type, $start_date, $end_date, $total_days, $reason]);
            $success = "Leave request submitted successfully!";
        } catch(PDOException $e) {
            $error = "Error submitting leave request: " . $e->getMessage();
        }
    }
    
    if ($_POST['action'] == 'update_status' && isset($_POST['leave_id'])) {
        $leave_id = $_POST['leave_id'];
        $status = $_POST['status'];
        $comments = $_POST['comments'] ?? '';
        
        try {
            $stmt = $pdo->prepare("
                UPDATE leave_requests 
                SET status = ?, comments = ?, approved_by = ?, approved_date = CURDATE() 
                WHERE leave_id = ?
            ");
            $stmt->execute([$status, $comments, $_SESSION['emp_id'], $leave_id]);
            $success = "Leave request updated successfully!";
        } catch(PDOException $e) {
            $error = "Error updating leave request: " . $e->getMessage();
        }
    }
}

// Get leave requests
try {
    if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'manager') {
        $stmt = $pdo->query("
            SELECT lr.*, e.first_name, e.last_name, e.email 
            FROM leave_requests lr 
            JOIN employees e ON lr.emp_id = e.emp_id 
            ORDER BY lr.created_at DESC
        ");
    } else {
        $stmt = $pdo->prepare("
            SELECT lr.*, e.first_name, e.last_name, e.email 
            FROM leave_requests lr 
            JOIN employees e ON lr.emp_id = e.emp_id 
            WHERE lr.emp_id = ? 
            ORDER BY lr.created_at DESC
        ");
        $stmt->execute([$_SESSION['emp_id']]);
    }
    $leave_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get employees for dropdown (admin/manager only)
    if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'manager') {
        $stmt = $pdo->query("SELECT emp_id, first_name, last_name FROM employees WHERE status = 'active'");
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch(PDOException $e) {
    $error = "Error loading leave requests: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Management - HRMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
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
                    <?php echo $_SESSION['user_name']; ?>
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
                    <a href="dashboard.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a href="employees.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-people"></i> Employees
                    </a>
                    <a href="attendance.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-calendar-check"></i> Attendance
                    </a>
                    <a href="leaves.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-calendar-event"></i> Leave Management
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-calendar-event"></i> Leave Management</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeaveModal">
                        <i class="bi bi-plus-circle"></i> Request Leave
                    </button>
                </div>
                
                <?php if($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Leave Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h5>Pending</h5>
                                <h2><?php echo count(array_filter($leave_requests, function($lr) { return $lr['status'] == 'pending'; })); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h5>Approved</h5>
                                <h2><?php echo count(array_filter($leave_requests, function($lr) { return $lr['status'] == 'approved'; })); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h5>Rejected</h5>
                                <h2><?php echo count(array_filter($leave_requests, function($lr) { return $lr['status'] == 'rejected'; })); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h5>Total</h5>
                                <h2><?php echo count($leave_requests); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Leave Requests Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Leave Requests</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Employee</th>
                                        <th>Leave Type</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Total Days</th>
                                        <th>Status</th>
                                        <th>Applied On</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($leave_requests as $leave): ?>
                                    <tr>
                                        <td>#<?php echo $leave['leave_id']; ?></td>
                                        <td><?php echo $leave['first_name'] . ' ' . $leave['last_name']; ?></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo ucfirst(str_replace('_', ' ', $leave['leave_type'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($leave['start_date'])); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($leave['end_date'])); ?></td>
                                        <td><?php echo $leave['total_days']; ?> days</td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $leave['status'] == 'approved' ? 'success' : 
                                                    ($leave['status'] == 'pending' ? 'warning' : 'danger'); 
                                            ?>">
                                                <?php echo ucfirst($leave['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($leave['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary view-leave" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewLeaveModal"
                                                    data-leave='<?php echo json_encode($leave); ?>'>
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <?php if(($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'manager') && $leave['status'] == 'pending'): ?>
                                            <button class="btn btn-sm btn-outline-success approve-leave" 
                                                    data-leave-id="<?php echo $leave['leave_id']; ?>">
                                                <i class="bi bi-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger reject-leave" 
                                                    data-leave-id="<?php echo $leave['leave_id']; ?>">
                                                <i class="bi bi-x"></i>
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Leave Modal -->
    <div class="modal fade" id="addLeaveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Request Leave</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_leave">
                        <?php if($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'manager'): ?>
                        <div class="mb-3">
                            <label class="form-label">Employee</label>
                            <select class="form-select" name="emp_id" required>
                                <option value="">Select Employee</option>
                                <?php foreach($employees as $emp): ?>
                                <option value="<?php echo $emp['emp_id']; ?>">
                                    <?php echo $emp['first_name'] . ' ' . $emp['last_name']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php else: ?>
                        <input type="hidden" name="emp_id" value="<?php echo $_SESSION['emp_id']; ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label class="form-label">Leave Type</label>
                            <select class="form-select" name="leave_type" required>
                                <option value="vacation">Vacation</option>
                                <option value="sick_leave">Sick Leave</option>
                                <option value="personal">Personal</option>
                                <option value="emergency">Emergency</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" class="form-control" name="start_date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" class="form-control" name="end_date" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason</label>
                            <textarea class="form-control" name="reason" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Leave Modal -->
    <div class="modal fade" id="viewLeaveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Leave Request Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="leaveDetails">
                    <!-- Details will be loaded via JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // View leave details
        document.querySelectorAll('.view-leave').forEach(button => {
            button.addEventListener('click', function() {
                const leave = JSON.parse(this.dataset.leave);
                document.getElementById('leaveDetails').innerHTML = `
                    <p><strong>Employee:</strong> ${leave.first_name} ${leave.last_name}</p>
                    <p><strong>Leave Type:</strong> ${leave.leave_type.replace('_', ' ')}</p>
                    <p><strong>Period:</strong> ${new Date(leave.start_date).toLocaleDateString()} to ${new Date(leave.end_date).toLocaleDateString()}</p>
                    <p><strong>Total Days:</strong> ${leave.total_days}</p>
                    <p><strong>Reason:</strong> ${leave.reason}</p>
                    <p><strong>Status:</strong> <span class="badge bg-${leave.status === 'approved' ? 'success' : leave.status === 'pending' ? 'warning' : 'danger'}">${leave.status}</span></p>
                    ${leave.comments ? `<p><strong>Comments:</strong> ${leave.comments}</p>` : ''}
                `;
            });
        });

        // Approve/Reject leave
        document.querySelectorAll('.approve-leave, .reject-leave').forEach(button => {
            button.addEventListener('click', function() {
                const leaveId = this.dataset.leaveId;
                const status = this.classList.contains('approve-leave') ? 'approved' : 'rejected';
                const action = confirm(`Are you sure you want to ${status} this leave request?`);
                
                if (action) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="leave_id" value="${leaveId}">
                        <input type="hidden" name="status" value="${status}">
                        <input type="hidden" name="comments" value="Status updated by ${'<?php echo $_SESSION['user_name']; ?>'}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    </script>
</body>
</html>