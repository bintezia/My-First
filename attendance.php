<?php
require_once 'includes/config.php';
requireLogin();

$success = '';
$error = '';

// Handle attendance actions
if ($_POST && isset($_POST['action'])) {
    if ($_POST['action'] == 'mark_attendance') {
        $emp_id = $_POST['emp_id'];
        $date = $_POST['date'];
        $status = $_POST['status'];
        $check_in = $_POST['check_in'];
        $check_out = $_POST['check_out'];
        $notes = $_POST['notes'];
        
        try {
            // Calculate work hours if both check-in and check-out are provided
            $work_hours = 0;
            if ($check_in && $check_out) {
                $start = new DateTime($check_in);
                $end = new DateTime($check_out);
                $diff = $start->diff($end);
                $work_hours = $diff->h + ($diff->i / 60);
            }
            
            // Check if attendance already exists for this employee and date
            $check_stmt = $pdo->prepare("SELECT attendance_id FROM attendance WHERE emp_id = ? AND date = ?");
            $check_stmt->execute([$emp_id, $date]);
            $existing = $check_stmt->fetch();
            
            if ($existing) {
                // Update existing record
                $stmt = $pdo->prepare("
                    UPDATE attendance 
                    SET status = ?, check_in = ?, check_out = ?, work_hours = ?, notes = ? 
                    WHERE attendance_id = ?
                ");
                $stmt->execute([$status, $check_in, $check_out, $work_hours, $notes, $existing['attendance_id']]);
                $success = "Attendance updated successfully!";
            } else {
                // Insert new record
                $stmt = $pdo->prepare("
                    INSERT INTO attendance (emp_id, date, status, check_in, check_out, work_hours, notes) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$emp_id, $date, $status, $check_in, $check_out, $work_hours, $notes]);
                $success = "Attendance marked successfully!";
            }
        } catch(PDOException $e) {
            $error = "Error marking attendance: " . $e->getMessage();
        }
    }
}

// Get data
try {
    // Get selected date or use today
    $selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    
    // Get attendance for selected date
    $stmt = $pdo->prepare("
        SELECT a.*, e.first_name, e.last_name, e.email 
        FROM attendance a 
        JOIN employees e ON a.emp_id = e.emp_id 
        WHERE a.date = ? 
        ORDER BY a.check_in DESC
    ");
    $stmt->execute([$selected_date]);
    $attendance_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get employees for dropdown
    $stmt = $pdo->query("SELECT emp_id, first_name, last_name FROM employees WHERE status = 'active'");
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Attendance statistics
    $total_present = count(array_filter($attendance_records, function($a) { return $a['status'] == 'present'; }));
    $total_absent = count(array_filter($attendance_records, function($a) { return $a['status'] == 'absent'; }));
    $total_late = count(array_filter($attendance_records, function($a) { return $a['status'] == 'late'; }));
    
} catch(PDOException $e) {
    $error = "Error loading attendance data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - HRMS</title>
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
                    <a href="attendance.php" class="list-group-item list-group-item-action active">
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
                    <h2><i class="bi bi-calendar-check"></i> Attendance Management</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#markAttendanceModal">
                        <i class="bi bi-plus-circle"></i> Mark Attendance
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
                
                <!-- Date Selector -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-center">
                            <div class="col-md-4">
                                <label for="date" class="form-label">Select Date:</label>
                                <input type="date" class="form-control" id="date" name="date" value="<?php echo $selected_date; ?>" onchange="this.form.submit()">
                            </div>
                            <div class="col-md-8">
                                <p class="mb-0 mt-2">Showing attendance for: <strong><?php echo date('F j, Y', strtotime($selected_date)); ?></strong></p>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Attendance Statistics -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h5>Present</h5>
                                <h2><?php echo $total_present; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h5>Absent</h5>
                                <h2><?php echo $total_absent; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h5>Late</h5>
                                <h2><?php echo $total_late; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Attendance Records -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Attendance Records for <?php echo date('F j, Y', strtotime($selected_date)); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Employee</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Work Hours</th>
                                        <th>Status</th>
                                        <th>Notes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($attendance_records)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                No attendance records found for this date.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach($attendance_records as $record): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo $record['first_name'] . ' ' . $record['last_name']; ?></strong>
                                            </td>
                                            <td><?php echo $record['check_in'] ? date('g:i A', strtotime($record['check_in'])) : 'N/A'; ?></td>
                                            <td><?php echo $record['check_out'] ? date('g:i A', strtotime($record['check_out'])) : 'N/A'; ?></td>
                                            <td><?php echo $record['work_hours'] ? number_format($record['work_hours'], 2) . ' hrs' : 'N/A'; ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $record['status'] == 'present' ? 'success' : 
                                                        ($record['status'] == 'absent' ? 'danger' : 
                                                        ($record['status'] == 'late' ? 'warning' : 'secondary')); 
                                                ?>">
                                                    <?php echo ucfirst($record['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $record['notes'] ?: 'N/A'; ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-warning edit-attendance"
                                                        data-record='<?php echo json_encode($record); ?>'
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#markAttendanceModal">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
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

    <!-- Mark Attendance Modal -->
    <div class="modal fade" id="markAttendanceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mark Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="mark_attendance">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Employee *</label>
                            <select class="form-select" name="emp_id" id="attendance_emp_id" required>
                                <option value="">Select Employee</option>
                                <?php foreach($employees as $emp): ?>
                                <option value="<?php echo $emp['emp_id']; ?>">
                                    <?php echo $emp['first_name'] . ' ' . $emp['last_name']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date *</label>
                            <input type="date" class="form-control" name="date" id="attendance_date" value="<?php echo $selected_date; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status *</label>
                            <select class="form-select" name="status" id="attendance_status" required>
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="late">Late</option>
                                <option value="half_day">Half Day</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Check In</label>
                                    <input type="time" class="form-control" name="check_in" id="attendance_check_in">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Check Out</label>
                                    <input type="time" class="form-control" name="check_out" id="attendance_check_out">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" id="attendance_notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Attendance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Edit attendance functionality
        document.querySelectorAll('.edit-attendance').forEach(button => {
            button.addEventListener('click', function() {
                const record = JSON.parse(this.dataset.record);
                document.getElementById('attendance_emp_id').value = record.emp_id;
                document.getElementById('attendance_date').value = record.date;
                document.getElementById('attendance_status').value = record.status;
                document.getElementById('attendance_check_in').value = record.check_in ? record.check_in.substring(11, 16) : '';
                document.getElementById('attendance_check_out').value = record.check_out ? record.check_out.substring(11, 16) : '';
                document.getElementById('attendance_notes').value = record.notes || '';
                
                // Change modal title
                document.querySelector('#markAttendanceModal .modal-title').textContent = 'Edit Attendance';
            });
        });

        // Reset modal when closed
        document.getElementById('markAttendanceModal').addEventListener('hidden.bs.modal', function () {
            document.querySelector('#markAttendanceModal .modal-title').textContent = 'Mark Attendance';
            document.getElementById('markAttendanceModal').querySelector('form').reset();
            document.getElementById('attendance_date').value = '<?php echo $selected_date; ?>';
        });
    </script>
</body>
</html>