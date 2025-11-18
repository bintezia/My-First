<?php
require_once 'includes/config.php';
requireLogin();

$success = '';
$error = '';

// Handle payroll actions
if ($_POST && isset($_POST['action'])) {
    if ($_POST['action'] == 'process_salary') {
        $emp_id = $_POST['emp_id'];
        $base_salary = $_POST['base_salary'];
        $bonus = $_POST['bonus'] ?? 0;
        $allowances = $_POST['allowances'] ?? 0;
        $deductions = $_POST['deductions'] ?? 0;
        $pay_period_start = $_POST['pay_period_start'];
        $pay_period_end = $_POST['pay_period_end'];
        
        try {
            // Calculate net salary
            $tax_amount = $base_salary * 0.15; // 15% tax for example
            $net_salary = $base_salary + $bonus + $allowances - $deductions - $tax_amount;
            
            $stmt = $pdo->prepare("
                INSERT INTO salaries 
                (emp_id, base_salary, bonus, allowances, deductions, tax_amount, net_salary, pay_period_start, pay_period_end) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$emp_id, $base_salary, $bonus, $allowances, $deductions, $tax_amount, $net_salary, $pay_period_start, $pay_period_end]);
            
            $success = "Salary processed successfully!";
        } catch(PDOException $e) {
            $error = "Error processing salary: " . $e->getMessage();
        }
    }
}

// Get data
try {
    // Get payroll records
    $stmt = $pdo->query("
        SELECT s.*, e.first_name, e.last_name, e.email 
        FROM salaries s 
        JOIN employees e ON s.emp_id = e.emp_id 
        ORDER BY s.pay_period_start DESC
    ");
    $payroll_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get employees for dropdown
    $stmt = $pdo->query("SELECT emp_id, first_name, last_name, salary FROM employees WHERE status = 'active'");
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Payroll statistics
    $total_paid = array_sum(array_column($payroll_records, 'net_salary'));
    $pending_payments = count(array_filter($payroll_records, function($pr) { return $pr['payment_status'] == 'pending'; }));
    $paid_payments = count(array_filter($payroll_records, function($pr) { return $pr['payment_status'] == 'paid'; }));
    
} catch(PDOException $e) {
    $error = "Error loading payroll data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll - HRMS</title>
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
                    <a href="payroll.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-cash-coin"></i> Payroll
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-cash-coin"></i> Payroll Management</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#processSalaryModal">
                        <i class="bi bi-plus-circle"></i> Process Salary
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
                
                <!-- Payroll Statistics -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h5>Total Paid</h5>
                                <h2>$<?php echo number_format($total_paid, 2); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h5>Paid Payments</h5>
                                <h2><?php echo $paid_payments; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h5>Pending Payments</h5>
                                <h2><?php echo $pending_payments; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Payroll Records -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Payroll Records</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Employee</th>
                                        <th>Pay Period</th>
                                        <th>Base Salary</th>
                                        <th>Bonus</th>
                                        <th>Allowances</th>
                                        <th>Deductions</th>
                                        <th>Tax</th>
                                        <th>Net Salary</th>
                                        <th>Status</th>
                                        <th>Payment Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($payroll_records)): ?>
                                        <tr>
                                            <td colspan="10" class="text-center text-muted py-4">
                                                No payroll records found.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach($payroll_records as $record): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo $record['first_name'] . ' ' . $record['last_name']; ?></strong>
                                            </td>
                                            <td>
                                                <?php echo date('M j', strtotime($record['pay_period_start'])); ?> - 
                                                <?php echo date('M j, Y', strtotime($record['pay_period_end'])); ?>
                                            </td>
                                            <td>$<?php echo number_format($record['base_salary'], 2); ?></td>
                                            <td>$<?php echo number_format($record['bonus'], 2); ?></td>
                                            <td>$<?php echo number_format($record['allowances'], 2); ?></td>
                                            <td>$<?php echo number_format($record['deductions'], 2); ?></td>
                                            <td>$<?php echo number_format($record['tax_amount'], 2); ?></td>
                                            <td>
                                                <strong>$<?php echo number_format($record['net_salary'], 2); ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $record['payment_status'] == 'paid' ? 'success' : 
                                                        ($record['payment_status'] == 'pending' ? 'warning' : 'danger'); 
                                                ?>">
                                                    <?php echo ucfirst($record['payment_status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo $record['payment_date'] ? date('M j, Y', strtotime($record['payment_date'])) : 'Not Paid'; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Process Salary Modal -->
    <div class="modal fade" id="processSalaryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Process Salary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="process_salary">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Employee *</label>
                            <select class="form-select" name="emp_id" id="salary_emp_id" required onchange="updateBaseSalary()">
                                <option value="">Select Employee</option>
                                <?php foreach($employees as $emp): ?>
                                <option value="<?php echo $emp['emp_id']; ?>" data-salary="<?php echo $emp['salary']; ?>">
                                    <?php echo $emp['first_name'] . ' ' . $emp['last_name']; ?> 
                                    (Base: $<?php echo number_format($emp['salary'], 2); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Pay Period Start *</label>
                                    <input type="date" class="form-control" name="pay_period_start" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Pay Period End *</label>
                                    <input type="date" class="form-control" name="pay_period_end" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Base Salary *</label>
                            <input type="number" class="form-control" name="base_salary" id="base_salary" step="0.01" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Bonus</label>
                                    <input type="number" class="form-control" name="bonus" step="0.01" value="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Allowances</label>
                                    <input type="number" class="form-control" name="allowances" step="0.01" value="0">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deductions</label>
                            <input type="number" class="form-control" name="deductions" step="0.01" value="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Process Salary</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateBaseSalary() {
            const empSelect = document.getElementById('salary_emp_id');
            const baseSalaryInput = document.getElementById('base_salary');
            const selectedOption = empSelect.options[empSelect.selectedIndex];
            
            if (selectedOption.value !== '') {
                baseSalaryInput.value = selectedOption.getAttribute('data-salary');
            } else {
                baseSalaryInput.value = '';
            }
        }

        // Set default dates for pay period
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            
            document.querySelector('input[name="pay_period_start"]').valueAsDate = firstDay;
            document.querySelector('input[name="pay_period_end"]').valueAsDate = lastDay;
        });
    </script>
</body>
</html>