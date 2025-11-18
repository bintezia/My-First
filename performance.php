<?php
require_once 'includes/config.php';
requireLogin();

// Handle performance actions
if ($_POST && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action == 'add_review') {
        $emp_id = $_POST['emp_id'];
        $rating = $_POST['rating'];
        $work_quality = $_POST['work_quality'];
        $punctuality = $_POST['punctuality'];
        $teamwork = $_POST['teamwork'];
        $communication = $_POST['communication'];
        $comments = $_POST['comments'];
        $strengths = $_POST['strengths'];
        $areas_for_improvement = $_POST['areas_for_improvement'];
        $goals = $_POST['goals'];
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO performance_reviews (emp_id, reviewer_id, rating, work_quality, punctuality, teamwork, communication, comments, strengths, areas_for_improvement, goals) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$emp_id, $_SESSION['emp_id'], $rating, $work_quality, $punctuality, $teamwork, $communication, $comments, $strengths, $areas_for_improvement, $goals]);
            $success = "Performance review added successfully!";
        } catch(PDOException $e) {
            $error = "Error adding performance review: " . $e->getMessage();
        }
    }
}

// Get data
try {
    // Performance reviews
    if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'manager') {
        $stmt = $pdo->query("
            SELECT pr.*, e.first_name, e.last_name, e.email, 
                   r.first_name as reviewer_first, r.last_name as reviewer_last 
            FROM performance_reviews pr 
            JOIN employees e ON pr.emp_id = e.emp_id 
            JOIN employees r ON pr.reviewer_id = r.emp_id 
            ORDER BY pr.review_date DESC
        ");
    } else {
        $stmt = $pdo->prepare("
            SELECT pr.*, e.first_name, e.last_name, e.email, 
                   r.first_name as reviewer_first, r.last_name as reviewer_last 
            FROM performance_reviews pr 
            JOIN employees e ON pr.emp_id = e.emp_id 
            JOIN employees r ON pr.reviewer_id = r.emp_id 
            WHERE pr.emp_id = ? 
            ORDER BY pr.review_date DESC
        ");
        $stmt->execute([$_SESSION['emp_id']]);
    }
    $performance_reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Employees for dropdown (admin/manager only)
    if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'manager') {
        $stmt = $pdo->query("SELECT emp_id, first_name, last_name FROM employees WHERE status = 'active'");
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Performance statistics
    $total_reviews = count($performance_reviews);
    $avg_rating = $total_reviews > 0 ? array_sum(array_column($performance_reviews, 'rating')) / $total_reviews : 0;
    $high_performers = count(array_filter($performance_reviews, function($pr) { return $pr['rating'] >= 4.0; }));
    
} catch(PDOException $e) {
    $error = "Error loading performance data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Management - HRMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .rating-stars {
            color: #ffc107;
            font-size: 1.2em;
        }
        .performance-card {
            border-left: 4px solid #007bff;
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
                    <a href="performance.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-graph-up"></i> Performance
                    </a>
                    <a href="training.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-book"></i> Training
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-graph-up"></i> Performance Management</h2>
                    <?php if($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'manager'): ?>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addReviewModal">
                        <i class="bi bi-plus-circle"></i> Add Review
                    </button>
                    <?php endif; ?>
                </div>
                
                <?php if(isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <!-- Performance Statistics -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h5>Total Reviews</h5>
                                <h2><?php echo $total_reviews; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h5>Average Rating</h5>
                                <h2><?php echo number_format($avg_rating, 1); ?>/5</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h5>High Performers</h5>
                                <h2><?php echo $high_performers; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Performance Reviews -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Performance Reviews</h5>
                    </div>
                    <div class="card-body">
                        <?php if(empty($performance_reviews)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-clipboard-x display-4"></i>
                                <p class="mt-3">No performance reviews found</p>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach($performance_reviews as $review): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card performance-card h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-title">
                                                    <?php echo $review['first_name'] . ' ' . $review['last_name']; ?>
                                                </h6>
                                                <span class="rating-stars">
                                                    <?php 
                                                    $rating = $review['rating'];
                                                    for($i = 1; $i <= 5; $i++): 
                                                        if($i <= floor($rating)): ?>
                                                            <i class="bi bi-star-fill"></i>
                                                        <?php elseif($i == ceil($rating) && $rating != floor($rating)): ?>
                                                            <i class="bi bi-star-half"></i>
                                                        <?php else: ?>
                                                            <i class="bi bi-star"></i>
                                                        <?php endif;
                                                    endfor; ?>
                                                    <small class="text-muted">(<?php echo number_format($rating, 1); ?>)</small>
                                                </span>
                                            </div>
                                            <p class="card-text"><small class="text-muted">
                                                Reviewed by: <?php echo $review['reviewer_first'] . ' ' . $review['reviewer_last']; ?><br>
                                                Date: <?php echo date('M j, Y', strtotime($review['review_date'])); ?>
                                            </small></p>
                                            
                                            <!-- Detailed Ratings -->
                                            <div class="row text-center small mt-3">
                                                <div class="col-3">
                                                    <div>Work Quality</div>
                                                    <strong><?php echo number_format($review['work_quality'], 1); ?></strong>
                                                </div>
                                                <div class="col-3">
                                                    <div>Punctuality</div>
                                                    <strong><?php echo number_format($review['punctuality'], 1); ?></strong>
                                                </div>
                                                <div class="col-3">
                                                    <div>Teamwork</div>
                                                    <strong><?php echo number_format($review['teamwork'], 1); ?></strong>
                                                </div>
                                                <div class="col-3">
                                                    <div>Communication</div>
                                                    <strong><?php echo number_format($review['communication'], 1); ?></strong>
                                                </div>
                                            </div>
                                            
                                            <button class="btn btn-sm btn-outline-primary mt-3 w-100 view-review" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewReviewModal"
                                                    data-review='<?php echo json_encode($review); ?>'>
                                                <i class="bi bi-eye"></i> View Details
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Review Modal (Admin/Manager only) -->
    <?php if($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'manager'): ?>
    <div class="modal fade" id="addReviewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Performance Review</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_review">
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
                        
                        <!-- Rating Sliders -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Overall Rating *</label>
                                <input type="range" class="form-range" name="rating" min="1" max="5" step="0.1" value="3" oninput="updateRatingValue(this.value, 'ratingValue')">
                                <div class="text-center">
                                    <span id="ratingValue" class="rating-stars">3.0</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Work Quality</label>
                                <input type="range" class="form-range" name="work_quality" min="1" max="5" step="0.1" value="3">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Punctuality</label>
                                <input type="range" class="form-range" name="punctuality" min="1" max="5" step="0.1" value="3">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Teamwork</label>
                                <input type="range" class="form-range" name="teamwork" min="1" max="5" step="0.1" value="3">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Communication</label>
                                <input type="range" class="form-range" name="communication" min="1" max="5" step="0.1" value="3">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Strengths</label>
                            <textarea class="form-control" name="strengths" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Areas for Improvement</label>
                            <textarea class="form-control" name="areas_for_improvement" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Goals</label>
                            <textarea class="form-control" name="goals" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Comments</label>
                            <textarea class="form-control" name="comments" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- View Review Modal -->
    <div class="modal fade" id="viewReviewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Performance Review Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="reviewDetails">
                    <!-- Details will be loaded via JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update rating display
        function updateRatingValue(value, elementId) {
            const element = document.getElementById(elementId);
            let stars = '';
            for(let i = 1; i <= 5; i++) {
                if(i <= Math.floor(value)) {
                    stars += '<i class="bi bi-star-fill"></i>';
                } else if(i == Math.ceil(value) && value != Math.floor(value)) {
                    stars += '<i class="bi bi-star-half"></i>';
                } else {
                    stars += '<i class="bi bi-star"></i>';
                }
            }
            element.innerHTML = stars + ' <small class="text-muted">(' + value + ')</small>';
        }

        // Generate star ratings
        function generateStars(rating) {
            let stars = '';
            for(let i = 1; i <= 5; i++) {
                if(i <= Math.floor(rating)) {
                    stars += '<i class="bi bi-star-fill"></i>';
                } else if(i == Math.ceil(rating) && rating != Math.floor(rating)) {
                    stars += '<i class="bi bi-star-half"></i>';
                } else {
                    stars += '<i class="bi bi-star"></i>';
                }
            }
            return stars;
        }

        // View review details
        document.querySelectorAll('.view-review').forEach(button => {
            button.addEventListener('click', function() {
                const review = JSON.parse(this.dataset.review);
                document.getElementById('reviewDetails').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Employee:</strong> ${review.first_name} ${review.last_name}</p>
                            <p><strong>Reviewer:</strong> ${review.reviewer_first} ${review.reviewer_last}</p>
                            <p><strong>Review Date:</strong> ${new Date(review.review_date).toLocaleDateString()}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Overall Rating:</strong> 
                                <span class="rating-stars">
                                    ${generateStars(review.rating)}
                                    <small class="text-muted">(${review.rating})</small>
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="row mt-3 text-center">
                        <div class="col-3">
                            <div class="card">
                                <div class="card-body py-2">
                                    <h6>Work Quality</h6>
                                    <h4>${review.work_quality}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="card">
                                <div class="card-body py-2">
                                    <h6>Punctuality</h6>
                                    <h4>${review.punctuality}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="card">
                                <div class="card-body py-2">
                                    <h6>Teamwork</h6>
                                    <h4>${review.teamwork}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="card">
                                <div class="card-body py-2">
                                    <h6>Communication</h6>
                                    <h4>${review.communication}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    ${review.strengths ? `
                    <div class="mt-3">
                        <h6>Strengths</h6>
                        <p>${review.strengths}</p>
                    </div>
                    ` : ''}
                    
                    ${review.areas_for_improvement ? `
                    <div class="mt-3">
                        <h6>Areas for Improvement</h6>
                        <p>${review.areas_for_improvement}</p>
                    </div>
                    ` : ''}
                    
                    ${review.goals ? `
                    <div class="mt-3">
                        <h6>Goals</h6>
                        <p>${review.goals}</p>
                    </div>
                    ` : ''}
                    
                    ${review.comments ? `
                    <div class="mt-3">
                        <h6>Comments</h6>
                        <p>${review.comments}</p>
                    </div>
                    ` : ''}
                    
                    ${review.next_review_date ? `
                    <div class="mt-3">
                        <h6>Next Review Date</h6>
                        <p>${new Date(review.next_review_date).toLocaleDateString()}</p>
                    </div>
                    ` : ''}
                `;
            });
        });
    </script>
</body>
</html>