<?php


session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'attendant') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'] ?? 'Attendant';


require_once '../dbconnect.php';


if ($conn->connect_error) {
    error_log("Database connection failed in attendant/job_details.php: " . $conn->connect_error);
    $error_message_db = "Database connection failed. Please try again later."; 
    $result_jobs = false; 
} else {

    $sql_jobs_stmt = $conn->prepare("SELECT jp.*, u.Name as PatientName, u.Phone as PatientPhone
                                     FROM job_posting jp
                                     JOIN users u ON jp.Patient_ID = u.User_ID
                                     WHERE jp.Status = 'open'
                                     ORDER BY jp.Created_at DESC");

    if ($sql_jobs_stmt) {
        $sql_jobs_stmt->execute();
        $result_jobs = $sql_jobs_stmt->get_result();
        $sql_jobs_stmt->close();
    } else {
        $error_message_db = "Database error: Could not prepare statement to fetch job postings. " . $conn->error;
        $result_jobs = false; 
    }

    $conn->close();
}


$success_message = $_GET['success'] ?? '';
$error_message = $_GET['error'] ?? ($error_message_db ?? ''); 

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Jobs - Attendant - HomeCareBD</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .job-card {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }
        .job-card .card-header {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        .job-card .card-body {
            padding: 15px;
        }
        .job-card .card-footer {
            background-color: #f1f1f1;
            padding: 10px 15px;
            text-align: right;
        }
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
                    <li class="nav-item active">
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
        <h2>Available Job Postings</h2>

        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>


        <?php if ($result_jobs !== false):  ?>
            <div class="row">
                <?php if ($result_jobs->num_rows > 0): ?>
                    <?php while ($job = $result_jobs->fetch_assoc()): ?>
                        <div class="col-md-6">
                            <div class="card job-card">
                                <div class="card-header">
                                    <?php echo htmlspecialchars($job['Job_title']); ?>
                                </div>
                                <div class="card-body">
                                    <p><strong>Patient:</strong> <?php echo htmlspecialchars($job['PatientName']); ?></p>
                                    <p><strong>Location:</strong> <?php echo htmlspecialchars($job['Location']); ?></p>
                                    <p><strong>Dates:</strong> <?php echo htmlspecialchars(date('M d, Y', strtotime($job['Start_date']))); ?> to <?php echo htmlspecialchars(date('M d, Y', strtotime($job['End_date']))); ?></p>
                                    <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($job['Job_description'])); ?></p>
                                </div>
                                <div class="card-footer">
                                    <a href="job_application.php?id=<?php echo htmlspecialchars($job['Job_ID']); ?>" class="btn btn-primary btn-sm">Apply Now</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i> No open jobs are currently available. Please check back later.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif (isset($error_message_db)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message_db); ?></div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>