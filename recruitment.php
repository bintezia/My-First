<?php
require_once 'includes/config.php';
requireLogin();

// Handle recruitment actions
if ($_POST && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action == 'add_job') {
        $job_title = $_POST['job_title'];
        $dept_id = $_POST['dept_id'];
        $job_description = $_POST['job_description'];
        $requirements = $_POST['requirements'];
        $salary_min = $_POST['salary_range_min'];
        $salary_max = $_POST['salary_range_max'];
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO job_postings (job_title, dept_id, job_description, requirements, salary_range_min, salary_range_max, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$job_title, $dept_id, $job_description, $requirements, $salary_min, $salary_max, $_SESSION['emp_id']]);
            $success = "Job posting created successfully!";
        } catch(PDOException $e) {
            $error = "Error creating job posting: " . $e->getMessage();
        }
    }
    
    if ($action == 'update_candidate_status' && isset($_POST['candidate_id'])) {
        $candidate_id = $_POST['candidate_id'];
        $status = $_POST['status'];
        
        try {
            $stmt = $pdo->prepare("UPDATE candidates SET status = ?, current_stage_date = CURDATE() WHERE candidate_id = ?");
            $stmt->execute([$status, $candidate_id]);
            $success = "Candidate status updated successfully!";
        } catch(PDOException $e) {
            $error = "Error updating candidate status: " . $e->getMessage();
        }
    }
}

// Get data
try {
    // Job postings
    $stmt = $pdo->query("
        SELECT jp.*, d.dept_name 
        FROM job_postings jp 
        LEFT JOIN departments d ON jp.dept_id = d.dept_id 
        ORDER BY jp.posting_date DESC
    ");
    $job_postings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Candidates
    $stmt = $pdo->query("
        SELECT c.*, jp.job_title, jp.dept_id 
        FROM candidates c 
        JOIN job_postings jp ON c.job_id = jp.job_id 
        ORDER BY c.applied_date DESC
    ");
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Departments for dropdown
    $stmt = $pdo->query("SELECT dept_id, dept_name FROM departments ORDER BY dept_name");
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Recruitment statistics
    $total_candidates = count($candidates);
    $hired_candidates = count(array_filter($candidates, function($c) { return $c['status'] == 'offer_accepted'; }));
    $interview_candidates = count(array_filter($candidates, function($c) { return in_array($c['status'], ['phone_interview', 'onsite_interview']); }));
    
} catch(PDOException $e) {
    $error = "Error loading recruitment data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruitment - HRMS</title>
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
                    <a href="recruitment.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-person-plus"></i> Recruitment
                    </a>
                    <a href="training.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-book"></i> Training
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-person-plus"></i> Recruitment Management</h2>
                    <div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addJobModal">
                            <i class="bi bi-plus-circle"></i> Post New Job
                        </button>
                    </div>
                </div>
                
                <?php if(isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <!-- Recruitment Statistics -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h5>Open Positions</h5>
                                <h2><?php echo count(array_filter($job_postings, function($jp) { return $jp['status'] == 'open'; })); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h5>Total Candidates</h5>
                                <h2><?php echo $total_candidates; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h5>In Interview</h5>
                                <h2><?php echo $interview_candidates; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Job Postings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Job Postings</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Job Title</th>
                                        <th>Department</th>
                                        <th>Salary Range</th>
                                        <th>Posting Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($job_postings as $job): ?>
                                    <tr>
                                        <td><strong><?php echo $job['job_title']; ?></strong></td>
                                        <td><?php echo $job['dept_name']; ?></td>
                                        <td>$<?php echo number_format($job['salary_range_min']); ?> - $<?php echo number_format($job['salary_range_max']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($job['posting_date'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $job['status'] == 'open' ? 'success' : 
                                                    ($job['status'] == 'draft' ? 'secondary' : 'danger'); 
                                            ?>">
                                                <?php echo ucfirst($job['status']); ?>
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
                
                <!-- Candidates -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Candidates</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Name</th>
                                        <th>Applied For</th>
                                        <th>Email</th>
                                        <th>Applied Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($candidates as $candidate): ?>
                                    <tr>
                                        <td><strong><?php echo $candidate['first_name'] . ' ' . $candidate['last_name']; ?></strong></td>
                                        <td><?php echo $candidate['job_title']; ?></td>
                                        <td><?php echo $candidate['email']; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($candidate['applied_date'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                $statusColors = [
                                                    'applied' => 'secondary',
                                                    'screening' => 'info',
                                                    'phone_interview' => 'primary',
                                                    'onsite_interview' => 'warning',
                                                    'offer_pending' => 'success',
                                                    'offer_accepted' => 'success',
                                                    'rejected' => 'danger'
                                                ];
                                                echo $statusColors[$candidate['status']] ?? 'secondary';
                                            ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $candidate['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary view-candidate" 
                                                    data-candidate='<?php echo json_encode($candidate); ?>'
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewCandidateModal">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                    Status
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <?php 
                                                    $statuses = ['applied', 'screening', 'phone_interview', 'onsite_interview', 'offer_pending', 'offer_accepted', 'rejected'];
                                                    foreach($statuses as $status): 
                                                    ?>
                                                    <li>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="action" value="update_candidate_status">
                                                            <input type="hidden" name="candidate_id" value="<?php echo $candidate['candidate_id']; ?>">
                                                            <input type="hidden" name="status" value="<?php echo $status; ?>">
                                                            <button type="submit" class="dropdown-item <?php echo $candidate['status'] == $status ? 'active' : ''; ?>">
                                                                <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <?php endforeach; ?>
                                                </ul>
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

    <!-- Add Job Modal -->
    <div class="modal fade" id="addJobModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Post New Job</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_job">
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
                                    <label class="form-label">Salary Range Min *</label>
                                    <input type="number" class="form-control" name="salary_range_min" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Salary Range Max *</label>
                                    <input type="number" class="form-control" name="salary_range_max" step="0.01" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Job Description *</label>
                            <textarea class="form-control" name="job_description" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Requirements *</label>
                            <textarea class="form-control" name="requirements" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Post Job</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Candidate Modal -->
    <div class="modal fade" id="viewCandidateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Candidate Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="candidateDetails">
                    <!-- Details will be loaded via JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // View candidate details
        document.querySelectorAll('.view-candidate').forEach(button => {
            button.addEventListener('click', function() {
                const candidate = JSON.parse(this.dataset.candidate);
                document.getElementById('candidateDetails').innerHTML = `
                    <p><strong>Name:</strong> ${candidate.first_name} ${candidate.last_name}</p>
                    <p><strong>Email:</strong> ${candidate.email}</p>
                    <p><strong>Phone:</strong> ${candidate.phone || 'N/A'}</p>
                    <p><strong>Applied For:</strong> ${candidate.job_title}</p>
                    <p><strong>Applied Date:</strong> ${new Date(candidate.applied_date).toLocaleDateString()}</p>
                    <p><strong>Status:</strong> <span class="badge bg-secondary">${candidate.status.replace('_', ' ')}</span></p>
                    <p><strong>Expected Salary:</strong> ${candidate.expected_salary ? '$' + candidate.expected_salary : 'N/A'}</p>
                    <p><strong>Notice Period:</strong> ${candidate.notice_period ? candidate.notice_period + ' days' : 'N/A'}</p>
                `;
            });
        });
    </script>
</body>
</html>