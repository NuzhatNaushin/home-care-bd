<?php

ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    
    header("Location: ../index.php"); 
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'] ?? 'Patient';

require_once '../dbconnect.php';

if ($conn->connect_error) {
    error_log("Database connection failed in patient/schedules.php: " . $conn->connect_error);
    $error_message = "Database connection failed. Please try again later.";
    $all_activities = []; 
} else {
    $all_activities = [];
    $error_message = '';

   
    $sql_jobs_stmt = $conn->prepare("SELECT 'Regular Job' as type, Job_ID as id, Job_title as title, Job_description as details, Location, Start_date as start_time, End_date as end_time, Status, Created_at
                                     FROM job_posting
                                     WHERE Patient_ID = ?");
    if ($sql_jobs_stmt) {
        $sql_jobs_stmt->bind_param("i", $user_id);
        $sql_jobs_stmt->execute();
        $result_jobs = $sql_jobs_stmt->get_result();
        while ($row = $result_jobs->fetch_assoc()) {
            $all_activities[] = $row;
        }
        $sql_jobs_stmt->close();
    } else {
        $error_message .= "Database error fetching job postings: " . $conn->error . "<br>";
    }

    
    $sql_emergency_stmt = $conn->prepare("SELECT 'Emergency Request' as type, Request_ID as id, 'Emergency Request' as title, Description as details, Location, Request_time as start_time, Request_time as end_time, Status, Request_time as created_at
                                          FROM emergency_request
                                          WHERE Patient_ID = ?");
     if ($sql_emergency_stmt) {
        $sql_emergency_stmt->bind_param("i", $user_id);
        $sql_emergency_stmt->execute();
        $result_emergency = $sql_emergency_stmt->get_result();
        while ($row = $result_emergency->fetch_assoc()) {
            $all_activities[] = $row;
        }
        $sql_emergency_stmt->close();
    } else {
        $error_message .= "Database error fetching emergency requests: " . $conn->error;
    }


    $conn->close();

    
    usort($all_activities, function($a, $b) {
        
        $timeA = isset($a['created_at']) ? strtotime($a['created_at']) : 0;
        $timeB = isset($b['created_at']) ? strtotime($b['created_at']) : 0;
        return $timeB - $timeA; 
    });

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Activities - Patient - Home Care BD</title> <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
     <style>
        :root {
            --primary-color: #2c4f87;
            --secondary-color: #4caf50;
            --light-bg: #f5f9ff;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
        }

        .sidebar {
            background-color: var(--primary-color);
            color: white;
            min-height: 100vh;
            padding-top: 20px;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 10px 20px;
            margin-bottom: 5px;
            border-radius: 5px;
        }

        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar .nav-link i {
            margin-right: 10px;
        }

        .main-content {
            padding: 20px;
        }

        .dashboard-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .profile-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="text-center mb-4">
                    <h4 class="mb-0"><i class="fas fa-heartbeat me-2"></i>Home Care BD</h4>
                </div>
                 <div class="text-center mb-4">
                    <img src="../images/avatar-placeholder.jpg" class="profile-image mb-3" alt="Profile">
                    <h5 class="mb-0"><?php echo htmlspecialchars($name); ?></h5>
                    <p class="text-light mb-0">Patient</p>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link" href="profile.php"> <i class="fas fa-user"></i> My Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="job_posting.php"> <i class="fas fa-search"></i> Find Attendants
                        </a>
                    </li>
                    <li class="nav-item active"> <a class="nav-link" href="schedules.php"> <i class="fas fa-list-alt"></i> My Schedule </a>
                    </li>
                    <li class="nav-item">
                         <a class="nav-link" href="medical_history.php"> <i class="fas fa-notes-medical"></i> Medical History
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="messages.php"> <i class="fas fa-envelope"></i> Messages
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="emergency.php"> <i class="fas fa-ambulance"></i> Emergency
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="payment_history.php"> <i class="fas fa-money-bill"></i> Payments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="submit_feedback.php"> <i class="fas fa-comment"></i> Feedback </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php"> <i class="fas fa-cog"></i> Settings
                        </a>
                    </li>
                    <li class="nav-item mt-3">
                         <a class="nav-link" href="../logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>

            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>My Activities</h2> <div>
                        <a href="job_posting.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i> Post New Job</a>
                        <a href="emergency.php" class="btn btn-danger"><i class="fas fa-ambulance me-2"></i> Request Emergency</a>
                    </div>
                </div>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><?php echo nl2br(htmlspecialchars($error_message)); ?></div>
                <?php endif; ?>

                <div class="dashboard-card">
                    <?php if (!empty($all_activities)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Title/Description</th>
                                        <th>Location</th>
                                        <th>Time/Dates</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_activities as $activity): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($activity['type']); ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($activity['title'] ?? ''); ?></strong><br>
                                                <?php
                                               
                                                $details = htmlspecialchars($activity['details'] ?? '');
                                                echo nl2br(mb_strimwidth($details, 0, 100, "..."));
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($activity['location'] ?? ''); ?></td>
                                            <td>
                                                <?php
                                                if ($activity['type'] === 'Regular Job') {
                                                    echo htmlspecialchars(date('M d, Y', strtotime($activity['start_time'] ?? '')))
                                                         . ' to '
                                                         . htmlspecialchars(date('M d, Y', strtotime($activity['end_time'] ?? '')));
                                                } elseif ($activity['type'] === 'Emergency Request') {
                                                     echo 'Requested on ' . htmlspecialchars(date('M d, Y h:i A', strtotime($activity['start_time'] ?? '')));
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                    $status = htmlspecialchars($activity['status'] ?? '');
                                                    $badge_class = 'bg-secondary'; // Default
                                                    switch ($status) {
                                                        case 'open': // Job Posting status
                                                        case 'pending': // Emergency Request or Job Application status
                                                            $badge_class = 'bg-warning text-dark';
                                                            break;
                                                        case 'in_progress': // Job Posting status
                                                            $badge_class = 'bg-info';
                                                            break;
                                                         case 'attendant_assigned': // Schedule status,
                                                            $badge_class = 'bg-info';
                                                            break;
                                                        case 'confirmed': // Schedule status
                                                            $badge_class = 'bg-success';
                                                            break;
                                                        case 'completed':
                                                            $badge_class = 'bg-primary';
                                                            break;
                                                        case 'canceled':
                                                            $badge_class = 'bg-danger';
                                                            break;
                                                        case 'accepted': // Job Application status
                                                            $badge_class = 'bg-success';
                                                             break;
                                                        case 'rejected': // Job Application status
                                                            $badge_class = 'bg-danger';
                                                            break;
                                                        default: 
                                                             $badge_class = 'bg-secondary';
                                                             $status = 'Unknown';
                                                             break;
                                                    }
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst(str_replace('_', ' ', $status)); ?></span>
                                            </td>
                                            <td>
                                                <?php if (($activity['type'] ?? '') === 'Regular Job'): ?>
                                                    <a href="view_job_details.php?id=<?php echo htmlspecialchars($activity['id'] ?? ''); ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                                                    <?php if (($activity['status'] ?? '') == 'open'): ?>
                                                         <a href="edit_job.php?id=<?php echo htmlspecialchars($activity['id'] ?? ''); ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                                    <?php endif; ?>
                                                     <?php if (($activity['status'] ?? '') != 'completed' && ($activity['status'] ?? '') != 'canceled'): ?>
                                                          <a href="delete_job.php?delete=<?php echo htmlspecialchars($activity['id'] ?? ''); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to cancel this job posting?');">Cancel</a>
                                                     <?php endif; ?>
                                                <?php elseif (($activity['type'] ?? '') === 'Emergency Request'): ?>
                                                    <a href="view_emergency_request.php?id=<?php echo htmlspecialchars($activity['id'] ?? ''); ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                                                    <?php if (($activity['status'] ?? '') == 'pending'): ?>
                                                         <a href="cancel_emergency.php?id=<?php echo htmlspecialchars($activity['id'] ?? ''); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to cancel this emergency request?');">Cancel</a>
                                                    <?php endif; ?>
                                                     <?php if (($activity['status'] ?? '') != 'completed' && ($activity['status'] ?? '') != 'canceled'): ?>
                                                          <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">You have no active job postings or emergency requests.</div>
                        <div class="text-center">
                            <a href="job_posting.php" class="btn btn-primary">Post Your First Job</a>
                            <a href="emergency.php" class="btn btn-danger">Request Emergency Assistance</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
            const currentPath = window.location.pathname.split('/').pop();

            sidebarLinks.forEach(link => {
                const linkPath = link.getAttribute('href').split('/').pop();
               
                if (linkPath === currentPath) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });

            
             const myActivitiesLink = document.querySelector('.sidebar .nav-link[href="schedules.php"]');
             if (myActivitiesLink && currentPath === 'schedules.php') {
                 myActivitiesLink.classList.add('active');
             }
        });
    </script>
</body>
</html>