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
    error_log("Database connection failed in my_applications.php: " . $conn->connect_error);
    $error_message = "Database connection failed. Please try again later.";
    $result_applications = false; 
} else {

 
    $sql_applications_stmt = $conn->prepare("SELECT ja.*, jp.Job_title, jp.Location, jp.Start_date, jp.End_date, u.Name as PatientName
                                           FROM job_application ja
                                           JOIN job_posting jp ON ja.Job_ID = jp.Job_ID
                                           JOIN users u ON jp.Patient_ID = u.User_ID
                                           WHERE ja.Attendant_ID = ?
                                           ORDER BY ja.Application_Date DESC");
    if ($sql_applications_stmt) {
        $sql_applications_stmt->bind_param("i", $attendant_id);
        $sql_applications_stmt->execute();
        $result_applications = $sql_applications_stmt->get_result();
        $sql_applications_stmt->close();
    } else {
         error_log("Database error preparing application fetch in my_applications.php: " . $conn->error);
         $error_message = "Database error fetching your applications.";
         $result_applications = false; 
    }

    $conn->close(); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Job Applications - Attendant - HomeCareBD</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        .container {
            max-width: 800px;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        .application-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        .application-item:last-child {
            border-bottom: none;
        }
        .application-item h5 {
            margin-bottom: 5px;
        }
        .application-item .status-badge {
             font-size: 0.9rem;
             padding: 5px 10px;
             border-radius: 5px;
        }
         .badge-success { background-color: #28a745; color: white; }
         .badge-warning { background-color: #ffc107; color: #212529; }
         .badge-danger { background-color: #dc3545; color: white; }
    </style>
</head>
<body>
     <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">HomeCareBD</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="job_details.php">Find Jobs</a>
                    </li>
                    <li class="nav-item">
                         <a class="nav-link" href="schedule.php">My Schedule</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="messages.php">Messages</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($name); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="profile.php">My Profile</a>
                            <a class="dropdown-item" href="edit_profile.php">Edit Profile</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="../logout.php">Logout</a>
                        </div>
                    </li>
                    </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">My Job Applications</h2>

        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if ($result_applications !== false && $result_applications->num_rows > 0): ?>
            <div class="application-list">
                <?php while ($application = $result_applications->fetch_assoc()): ?>
                    <div class="application-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5><?php echo htmlspecialchars($application['Job_title'] ?? 'N/A'); ?></h5>
                             <span class="status-badge
                                <?php
                                     $status = htmlspecialchars($application['Status'] ?? 'unknown');
                                     switch ($status) {
                                         case 'accepted': echo 'badge-success'; break;
                                         case 'pending': echo 'badge-warning'; break;
                                         case 'rejected': echo 'badge-danger'; break;
                                         default: echo 'badge-secondary'; break;
                                     }
                                ?>
                             "><?php echo ucfirst(str_replace('_', ' ', $status)); ?></span>
                        </div>
                        <p class="text-muted mb-1"><strong>Patient:</strong> <?php echo htmlspecialchars($application['PatientName'] ?? 'N/A'); ?></p>
                        <p class="text-muted mb-1"><strong>Location:</strong> <?php echo htmlspecialchars($application['Location'] ?? 'N/A'); ?></p>
                        <p class="text-muted mb-1"><strong>Dates:</strong> <?php echo htmlspecialchars(date('M d, Y', strtotime($application['Start_date'] ?? 'now'))); ?> to <?php echo htmlspecialchars(date('M d, Y', strtotime($application['End_date'] ?? 'now'))); ?></p>
                        <p class="text-muted mb-1"><strong>Applied On:</strong> <?php echo htmlspecialchars(date('M d, Y', strtotime($application['Application_Date'] ?? 'now'))); ?></p>
                         <div class="mt-3">
                             <a href="job_details.php?id=<?php echo htmlspecialchars($application['Job_ID'] ?? ''); ?>" class="btn btn-sm btn-outline-primary">View Job Details</a>
                             </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php elseif ($result_applications !== false): ?>
            <div class="alert alert-info">You have not submitted any job applications yet.</div>
             <div class="text-center">
                <a href="job_details.php" class="btn btn-primary">Find Available Jobs</a>
             </div>
        <?php else: ?>
             <div class="alert alert-danger">Error loading your job applications.</div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>