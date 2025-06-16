<?php


ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'attendant') {
    header("Location: ../index.php");
    exit();
}

$attendant_id = $_SESSION['user_id'];
$name = $_SESSION['name'] ?? 'Attendant';

require_once '../dbconnect.php'; 


if ($conn->connect_error) {
    error_log("Database connection failed in job_application.php: " . $conn->connect_error);
   
    $error_message_db = "Database connection failed. Please try again later.";
    $job = null; 
    $result_check_applied = false; 
} else {
    $job = null;
    $error_message = null;
    $already_applied = false;
    $error_message_db = null; 

    
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $job_id = intval($_GET['id']);

       
        $sql_job_stmt = $conn->prepare("SELECT jp.*, u.Name as PatientName, u.Phone as PatientPhone
                                      FROM job_posting jp
                                      JOIN users u ON jp.Patient_ID = u.User_ID
                                      WHERE jp.Job_ID = ? AND jp.Status = 'open'"); 
        if ($sql_job_stmt) {
            $sql_job_stmt->bind_param("i", $job_id);
            $sql_job_stmt->execute();
            $result_job = $sql_job_stmt->get_result();
            if ($result_job->num_rows > 0) {
                $job = $result_job->fetch_assoc();

                
                $sql_check_applied = "SELECT Application_ID FROM job_application WHERE Job_ID = ? AND Attendant_ID = ?";
                $stmt_check_applied = $conn->prepare($sql_check_applied);
                if ($stmt_check_applied) {
                    $stmt_check_applied->bind_param("ii", $job_id, $attendant_id);
                    $stmt_check_applied->execute();
                    $stmt_check_applied->store_result();
                    if ($stmt_check_applied->num_rows > 0) {
                        $already_applied = true;
                    }
                    $stmt_check_applied->close();
                } else {
                     $error_message = "Database error: Could not prepare statement to check application status. " . $conn->error;
                     error_log("Prepare check application failed in job_application.php: " . $conn->error);
                }

            } else {
                $error_message = "Job not found or not available for application.";
            }
            $sql_job_stmt->close();
        } else {
            $error_message = "Database error: Could not prepare statement to fetch job details. " . $conn->error;
            error_log("Prepare job details failed in job_application.php: " . $conn->error);
        }

    } else {
   
        $error_message = "Invalid request: Job ID not provided.";
    }

    $conn->close(); 
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Job - HomeCareBD</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .sidebar {
            min-width: 200px;
            max-width: 200px;
            background-color: #f8f9fa;
            padding: 15px;
            height: 100vh;
            position: fixed;
        }
        .sidebar .nav-link {
            color: #333;
        }
        .sidebar .nav-link.active {
            font-weight: bold;
            color: #007bff;
        }
        .content {
            margin-left: 220px; 
            padding: 20px;
        }
         .job-details {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar">
             <h5 class="mb-4">Attendant Dashboard</h5>
            <ul class="nav flex-column">
                 <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-home me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="job_details.php">
                        <i class="fas fa-search me-2"></i> Find Jobs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="my_applications.php">
                        <i class="fas fa-file-alt me-2"></i> My Applications
                    </a>
                </li>
                 <li class="nav-item">
                    <a class="nav-link" href="schedule.php">
                        <i class="fas fa-calendar-alt me-2"></i> Schedule
                    </a>
                </li>
                 <li class="nav-item">
                    <a class="nav-link" href="emergency_requests.php">
                        <i class="fas fa-ambulance me-2"></i> Emergency Requests
                    </a>
                </li>
                 <li class="nav-item">
                    <a class="nav-link" href="messages.php">
                        <i class="fas fa-envelope me-2"></i> Messages
                    </a>
                </li>
                 <li class="nav-item">
                    <a class="nav-link" href="received_feedback.php">
                        <i class="fas fa-comments me-2"></i> Received Feedback
                    </a>
                </li>
                 <li class="nav-item">
                    <a class="nav-link" href="settings.php">
                        <i class="fas fa-cog me-2"></i> Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </li>
            </ul>
        </div>

        <div class="content flex-grow-1">
            <h2 class="mb-4">Job Application Confirmation</h2>

             <?php if (isset($error_message_db)):  ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message_db); ?></div>
            <?php endif; ?>

            <?php if (isset($error_message) && $error_message && !$error_message_db):  ?>
                <div class="alert alert-warning"><?php echo htmlspecialchars($error_message); ?></div>
                 <a href="job_details.php" class="btn btn-secondary">Back to Available Jobs</a>
            <?php elseif ($job && !$already_applied && !$error_message_db):  ?>
                 <div class="card">
                    <div class="card-header">
                        Apply for: <?php echo htmlspecialchars($job['Job_title']); ?>
                    </div>
                    <div class="card-body">
                        <p>Review the details and click "Confirm Application" to apply.</p>
                        <p><strong>Patient:</strong> <?php echo htmlspecialchars($job['PatientName']); ?></p>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($job['Location']); ?></p>
                        <p><strong>Dates:</strong> <?php echo htmlspecialchars(date('M d, Y', strtotime($job['Start_date']))); ?> to <?php echo htmlspecialchars(date('M d, Y', strtotime($job['End_date']))); ?></p>
                        <p><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($job['Job_description'])); ?></p>

                        <hr>

                        <form method="post" action="process_application.php">
                            <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($job['Job_ID']); ?>">
                            <button type="submit" name="submit_application" class="btn btn-success">Confirm Application</button>
                            <a href="job_details.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            <?php elseif ($already_applied && $job && !$error_message_db):  ?>
                 <div class="job-details">
                     <h3><?php echo htmlspecialchars($job['Job_title']); ?></h3>
                     <p><strong>Patient:</strong> <?php echo htmlspecialchars($job['PatientName']); ?></p>
                     <p><strong>Location:</strong> <?php echo htmlspecialchars($job['Location']); ?></p>
                     <p><strong>Dates:</strong> <?php echo htmlspecialchars(date('M d, Y', strtotime($job['Start_date']))); ?> to <?php echo htmlspecialchars(date('M d, Y', strtotime($job['End_date']))); ?></p>
                     <p><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($job['Job_description'])); ?></p>
                     <hr>
                     <p class="text-info">You have already applied for this job.</p>
                     <a href="job_details.php" class="btn btn-secondary">Back to Available Jobs</a>
                 </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
     <script>
       
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
            
            const currentPath = window.location.pathname.split('/').pop().split('?')[0];

            sidebarLinks.forEach(link => {
               
                const linkPath = link.getAttribute('href').split('/').pop().split('?')[0];
                if (linkPath === currentPath) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>