<?php

session_start();


$conn = new mysqli('localhost', 'root', '', 'homecarebd');


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$jobs = [];
$error_message = $success_message = "";

if(!isset($_SESSION['email'])) {

    header("Location: login_process.php");
    exit();
}


$email = $_SESSION['email'];


$stmt = $conn->prepare("SELECT users.User_ID, users.Role FROM users WHERE Email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {

    $_SESSION['error_message'] = "User account not found.";
    header("Location: index.php");
    exit();
}

$user_data = $result->fetch_assoc();
$user_id = $user_data['User_ID'];
$role = $user_data['Role'];
$stmt->close();


if($role !== 'patient') {
    $_SESSION['error_message'] = "You must be a patient to view your jobs.";
    header("Location: index.php");
    exit();
}


if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $job_id = (int)$_GET['delete'];
    

    $verify_stmt = $conn->prepare("SELECT Job_ID FROM job_posting WHERE Job_ID = ? AND Patient_ID = ?");
    $verify_stmt->bind_param("ii", $job_id, $user_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if($verify_result->num_rows > 0) {

        $delete_stmt = $conn->prepare("DELETE FROM job_posting WHERE Job_ID = ?");
        $delete_stmt->bind_param("i", $job_id);
        
        if($delete_stmt->execute()) {
            $success_message = "Job deleted successfully!";
        } else {
            $error_message = "Error deleting job: " . $conn->error;
        }
        
        $delete_stmt->close();
    } else {
        $error_message = "You don't have permission to delete this job.";
    }
    
    $verify_stmt->close();
}


$jobs_stmt = $conn->prepare("SELECT * FROM job_posting WHERE Patient_ID = ? ORDER BY Start_date DESC");
$jobs_stmt->bind_param("i", $user_id);
$jobs_stmt->execute();
$jobs_result = $jobs_stmt->get_result();

if($jobs_result->num_rows > 0) {
    while($row = $jobs_result->fetch_assoc()) {
        $jobs[] = $row;
    }
}
$jobs_stmt->close();


if(isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

if(isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Posted Jobs</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 1000px;
            margin-top: 30px;
        }
        .job-card {
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .job-actions {
            display: flex;
            gap: 10px;
        }
        .empty-state {
            text-align: center;
            padding: 50px 0;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>My Posted Jobs</h2>
            <a href="job_posting.php" class="btn btn-primary">Post New Job</a>
        </div>
        
        <?php 

        if (!empty($error_message)) {
            echo '<div class="alert alert-danger">' . $error_message . '</div>';
        }
        
 
        if (!empty($success_message)) {
            echo '<div class="alert alert-success">' . $success_message . '</div>';
        }
        
  
        if (empty($jobs)) {
            echo '<div class="empty-state">
                    <h4>No jobs posted yet</h4>
                    <p>When you post a job, it will appear here.</p>
                    <a href="post_job.php" class="btn btn-outline-primary">Post Your First Job</a>
                  </div>';
        } else {
            foreach ($jobs as $job) {
                $status_badge = '';
                switch($job['Status']) {
                    case 'open':
                        $status_badge = '<span class="badge bg-success">Open</span>';
                        break;
                    case 'closed':
                        $status_badge = '<span class="badge bg-secondary">Closed</span>';
                        break;
                    case 'in_progress':
                        $status_badge = '<span class="badge bg-primary">In Progress</span>';
                        break;
                    default:
                        $status_badge = '<span class="badge bg-info">' . ucfirst($job['Status']) . '</span>';
                }
                
                echo '<div class="card job-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">' . htmlspecialchars($job['Job_title']) . ' ' . $status_badge . '</h5>
                            <div class="job-actions">
                                <a href="edit_job.php?id=' . $job['Job_ID'] . '" class="btn btn-sm btn-outline-primary">Edit</a>
                                <a href="view_my_jobs.php?delete=' . $job['Job_ID'] . '" class="btn btn-sm btn-outline-danger" 
                                   onclick="return confirm(\'Are you sure you want to delete this job?\');">Delete</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="card-text">' . nl2br(htmlspecialchars($job['Job_description'])) . '</p>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <p><strong>Location:</strong> ' . htmlspecialchars($job['Location']) . '</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Dates:</strong> ' . htmlspecialchars(date('M d, Y', strtotime($job['Start_date']))) . 
                                    ' to ' . htmlspecialchars(date('M d, Y', strtotime($job['End_date']))) . '</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-muted">
                            Posted on ' . htmlspecialchars(date('M d, Y', strtotime($job['Created_at'] ?? 'now'))) . '
                        </div>
                      </div>';
            }
        }
        ?>
        
        <div class="mt-4">
            <a href="dashboard.php" class="btn btn-secondary">Back to Home</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>