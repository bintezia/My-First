<?php
require_once 'includes/config.php';
requireLogin();

// Handle training actions
if ($_POST && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action == 'add_training') {
        $program_name = $_POST['program_name'];
        $description = $_POST['description'];
        $trainer = $_POST['trainer'];
        $training_type = $_POST['training_type'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $duration_hours = $_POST['duration_hours'];
        $max_participants = $_POST['max_participants'];
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO training_programs (program_name, description, trainer, training_type, start_date, end_date, duration_hours, max_participants) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$program_name, $description, $trainer, $training_type, $start_date, $end_date, $duration_hours, $max_participants]);
            $success = "Training program created successfully!";
        } catch(PDOException $e) {
            $error = "Error creating training program: " . $e->getMessage();
        }
    }
    
    if ($action == 'enroll_employee' && isset($_POST['training_id'])) {
        $training_id = $_POST['training_id'];
        $emp_id = $_POST['emp_id'];
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO employee_training (emp_id, training_id, enrollment_date) 
                VALUES (?, ?, CURDATE())
            ");
            $stmt->execute([$emp_id, $training_id]);
            $success = "Employee enrolled in training successfully!";
        } catch(PDOException $e) {
            $error = "Error enrolling employee: " . $e->getMessage();
        }
    }
}

// Get data
try {
    // Training programs
    $stmt = $pdo->query("SELECT * FROM training_programs ORDER BY start_date DESC");
    $training_programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Employee training
    $stmt = $pdo->query("
        SELECT et.*, e.first_name, e.last_name, e.email, tp.program_name 
        FROM employee_training et 
        JOIN employees e ON et.emp_id = e.emp_id 
        JOIN training_programs tp ON et.training_id = tp.training_id 
        ORDER BY et.enrollment_date DESC
    ");
    $employee_trainings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Employees for dropdown
    $stmt = $pdo->query("SELECT emp_id, first_name, last_name FROM employees WHERE status = 'active'");
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Training statistics
    $total_programs = count($training_programs);
    $completed_trainings = count(array_filter($employee_trainings, function($et) { return $et['completion_status'] == 'completed'; }));
    $ongoing_trainings = count(array_filter($employee_trainings, function($et) { return $et['completion_status'] == 'in_progress'; }));
    
} catch(PDOException $e) {
    $error = "Error loading training data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Management - HRMS</title>
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
                    <a href="recruitment.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-person-plus"></i> Recruitment
                    </a>
                    <a href="training.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-book"></i> Training
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-book"></i> Training Management</h2>
                    <div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTrainingModal">
                            <i class="bi bi-plus-circle"></i> Add Training
                        </button>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#enrollEmployeeModal">
                            <i class="bi bi-person-plus"></i> Enroll Employee
                        </button>
                    </div>
                </div>
                
                <?php if(isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <!-- Training Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h5>Total Programs</h5>
                                <h2><?php echo $total_programs; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h5>Completed</h5>
                                <h2><?php echo $completed_trainings; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h5>Ongoing</h5>
                                <h2><?php echo $ongoing_trainings; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h5>Total Participants</h5>
                                <h2><?php echo count($employee_trainings); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Training Programs -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Training Programs</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Program Name</th>
                                        <th>Trainer</th>
                                        <th>Type</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($training_programs as $training): ?>
                                    <tr>
                                        <td><strong><?php echo $training['program_name']; ?></strong></td>
                                        <td><?php echo $training['trainer']; ?></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo ucfirst(str_replace('_', ' ', $training['training_type'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($training['start_date'])); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($training['end_date'])); ?></td>
                                        <td><?php echo $training['duration_hours']; ?> hours</td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $training['status'] == 'scheduled' ? 'info' : 
                                                    ($training['status'] == 'ongoing' ? 'warning' : 
                                                    ($training['status'] == 'completed' ? 'success' : 'danger')); 
                                            ?>">
                                                <?php echo ucfirst($training['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Employee Training -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Employee Training Records</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Employee</th>
                                        <th>Training Program</th>
                                        <th>Enrollment Date</th>
                                        <th>Completion Date</th>
                                        <th>Status</th>
                                        <th>Score</th>
                                        <th>Certificate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($employee_trainings as $training): ?>
                                    <tr>
                                        <td><strong><?php echo $training['first_name'] . ' ' . $training['last_name']; ?></strong></td>
                                        <td><?php echo $training['program_name']; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($training['enrollment_date'])); ?></td>
                                        <td><?php echo $training['completion_date'] ? date('M j, Y', strtotime($training['completion_date'])) : 'N/A'; ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $training['completion_status'] == 'completed' ? 'success' : 
                                                    ($training['completion_status'] == 'in_progress' ? 'warning' : 
                                                    ($training['completion_status'] == 'enrolled' ? 'info' : 'danger')); 
                                            ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $training['completion_status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $training['score'] ?: 'N/A'; ?></td>
                                        <td>
                                            <?php if($training['certificate_issued']): ?>
                                                <span class="badge bg-success">Yes</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">No</span>
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

    <!-- Add Training Modal -->
    <div class="modal fade" id="addTrainingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Training Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_training">
                        <div class="mb-3">
                            <label class="form-label">Program Name *</label>
                            <input type="text" class="form-control" name="program_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Trainer *</label>
                                    <input type="text" class="form-control" name="trainer" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Training Type *</label>
                                    <select class="form-select" name="training_type" required>
                                        <option value="technical">Technical</option>
                                        <option value="soft_skills">Soft Skills</option>
                                        <option value="compliance">Compliance</option>
                                        <option value="leadership">Leadership</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Start Date *</label>
                                    <input type="date" class="form-control" name="start_date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">End Date *</label>
                                    <input type="date" class="form-control" name="end_date" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Duration (hours) *</label>
                                    <input type="number" class="form-control" name="duration_hours" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Max Participants</label>
                                    <input type="number" class="form-control" name="max_participants">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Training</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Enroll Employee Modal -->
    <div class="modal fade" id="enrollEmployeeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Enroll Employee in Training</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="enroll_employee">
                        <div class="mb-3">
                            <label class="form-label">Employee *</label>
                            <select class="form-select" name="emp_id" required>
                                <option value="">Select Employee</option>
                                <?php foreach($employees as $emp): ?>
                                <option value="<?php echo $emp['emp_id']; ?>">
                                    <?php echo $emp['first_name'] . ' ' . $emp['last_name']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Training Program *</label>
                            <select class="form-select" name="training_id" required>
                                <option value="">Select Training</option>
                                <?php foreach($training_programs as $training): ?>
                                <option value="<?php echo $training['training_id']; ?>">
                                    <?php echo $training['program_name']; ?> (<?php echo date('M j, Y', strtotime($training['start_date'])); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Enroll Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>