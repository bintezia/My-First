<?php
require_once 'includes/config.php';
requireLogin();

$success = '';
$error = '';

// Handle Add Employee
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'add_employee') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $job_title = $_POST['job_title'];
    $dept_id = $_POST['dept_id'];
    $salary = $_POST['salary'];
    $hire_date = $_POST['hire_date'];
    $user_role = $_POST['user_role'];
    $password = $_POST['password'];
    $address = $_POST['address'];
    
    try {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO employees 
            (first_name, last_name, email, phone, job_title, dept_id, salary, hire_date, user_role, password_hash, address) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$first_name, $last_name, $email, $phone, $job_title, $dept_id, $salary, $hire_date, $user_role, $hashed_password, $address]);
        
        $success = "Employee added successfully!";
    } catch(PDOException $e) {
        $error = "Error adding employee: " . $e->getMessage();
    }
}

// Handle Delete Employee
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $emp_id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("UPDATE employees SET status = 'inactive' WHERE emp_id = ?");
        $stmt->execute([$emp_id]);
        $success = "Employee deactivated successfully!";
    } catch(PDOException $e) {
        $error = "Error deactivating employee: " . $e->getMessage();
    }
}

// Handle Update Employee Status
if (isset($_GET['action']) && $_GET['action'] == 'update_status' && isset($_GET['id']) && isset($_GET['status'])) {
    $emp_id = $_GET['id'];
    $status = $_GET['status'];
    try {
        $stmt = $pdo->prepare("UPDATE employees SET status = ? WHERE emp_id = ?");
        $stmt->execute([$status, $emp_id]);
        $success = "Employee status updated successfully!";
    } catch(PDOException $e) {
        $error = "Error updating employee status: " . $e->getMessage();
    }
}

// Get all employees with department info
try {
    $stmt = $pdo->query("
        SELECT e.*, d.dept_name 
        FROM employees e 
        LEFT JOIN departments d ON e.dept_id = d.dept_id 
        ORDER BY e.emp_id DESC
    ");
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get departments for dropdown
    $stmt = $pdo->query("SELECT dept_id, dept_name FROM departments ORDER BY dept_name");
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Error loading employees: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees - HRMS</title>
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
                    <a href="employees.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-people"></i> Employees
                    </a>
                    <a href="attendance.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-calendar-check"></i> Attendance
                    </a>
                    <a href="payroll.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-cash-coin"></i> Payroll
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-people"></i> Employee Management</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                        <i class="bi bi-person-plus"></i> Add Employee
                    </button>
                </div>
                
                <!-- Success/Error Messages -->
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
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">All Employees (<?php echo count($employees); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Position</th>
                                        <th>Department</th>
                                        <th>Salary</th>
                                        <th>Hire Date</th>
                                        <th>Status</th>
                                        <th>Role</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($employees as $employee): ?>
                                    <tr>
                                        <td>#<?php echo $employee['emp_id']; ?></td>
                                        <td>
                                            <strong><?php echo $employee['first_name'] . ' ' . $employee['last_name']; ?></strong>
                                        </td>
                                        <td><?php echo $employee['email']; ?></td>
                                        <td><?php echo $employee['phone'] ?: 'N/A'; ?></td>
                                        <td><?php echo $employee['job_title']; ?></td>
                                        <td><?php echo $employee['dept_name']; ?></td>
                                        <td>$<?php echo number_format($employee['salary'], 2); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($employee['hire_date'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $employee['status'] == 'active' ? 'success' : 
                                                    ($employee['status'] == 'on_leave' ? 'warning' : 'secondary'); 
                                            ?>">
                                                <?php echo ucfirst($employee['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo ucfirst($employee['user_role']); ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <?php if($employee['status'] == 'active'): ?>
                                                <a href="employees.php?action=update_status&id=<?php echo $employee['emp_id']; ?>&status=on_leave" 
                                                   class="btn btn-sm btn-outline-warning" title="Mark as On Leave">
                                                    <i class="bi bi-clock"></i>
                                                </a>
                                                <a href="employees.php?action=delete&id=<?php echo $employee['emp_id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger" title="Deactivate"
                                                   onclick="return confirm('Are you sure you want to deactivate this employee?')">
                                                    <i class="bi bi-person-dash"></i>
                                                </a>
                                                <?php else: ?>
                                                <a href="employees.php?action=update_status&id=<?php echo $employee['emp_id']; ?>&status=active" 
                                                   class="btn btn-sm btn-outline-success" title="Activate">
                                                    <i class="bi bi-person-check"></i>
                                                </a>
                                                <?php endif; ?>
                                            </div>
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

    <!-- Add Employee Modal -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add_employee">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">First Name *</label>
                                    <input type="text" class="form-control" name="first_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" name="last_name" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email *</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control" name="phone">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Job Title *</label>
                                    <input type="text" class="form-control" name="job_title" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Department *</label>
                                    <select class="form-select" name="dept_id" required>
                                        <option value="">Select Department</option>
                                        <?php foreach($departments as $dept): ?>
                                        <option value="<?php echo $dept['dept_id']; ?>">
                                            <?php echo $dept['dept_name']; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Salary *</label>
                                    <input type="number" class="form-control" name="salary" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Hire Date *</label>
                                    <input type="date" class="form-control" name="hire_date" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">User Role</label>
                                    <select class="form-select" name="user_role">
                                        <option value="employee">Employee</option>
                                        <option value="manager">Manager</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Password *</label>
                                    <input type="password" class="form-control" name="password" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>