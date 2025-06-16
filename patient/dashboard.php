<?php

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {

    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$name = $_SESSION['name'] ?? 'Patient';
$email = $_SESSION['email'] ?? '';

require_once '../dbconnect.php';

$stmt = $conn->prepare("SELECT * FROM patient WHERE User_ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$patient_data = $result->fetch_assoc();
$stmt->close();


$stmt = $conn->prepare("SELECT * FROM medical_history WHERE Patient_ID = ? ORDER BY Updated_at DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$medical_history = $result->fetch_assoc();
$stmt->close();


$stmt = $conn->prepare("SELECT * FROM job_posting WHERE Patient_ID = ? AND Status = 'open' ORDER BY Created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$active_jobs = $stmt->get_result();
$stmt->close();


$stmt = $conn->prepare("SELECT ja.*, jp.Job_title, u.Name as AttendantName
                        FROM job_application ja
                        JOIN job_posting jp ON ja.Job_ID = jp.Job_ID
                        JOIN users u ON ja.Attendant_ID = u.User_ID
                        WHERE jp.Patient_ID = ?
                        ORDER BY ja.Application_Date DESC
                        LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$job_applications = $stmt->get_result();
$stmt->close();


$stmt = $conn->prepare("SELECT s.*, u.Name as AttendantName
                        FROM schedule s
                        JOIN users u ON s.Attendant_ID = u.User_ID
                        WHERE s.Patient_ID = ? AND s.Status != 'canceled' AND s.End_time >= NOW()
                        ORDER BY s.Start_time ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$scheduled_appointments = $stmt->get_result();
$stmt->close();


$stmt = $conn->prepare("SELECT s.*, u.Name as AttendantName
                        FROM schedule s
                        JOIN users u ON s.Attendant_ID = u.User_ID
                        WHERE s.Patient_ID = ? AND s.Status = 'completed'
                        ORDER BY s.End_time DESC
                        LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$completed_appointments = $stmt->get_result();
$stmt->close();


$stmt = $conn->prepare("SELECT COUNT(*) as notification_count FROM notifications
                        WHERE User_ID = ? AND Status = 'unread'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notification_data = $result->fetch_assoc();
$unread_notifications = $notification_data['notification_count'];
$stmt->close();


$stmt = $conn->prepare("SELECT COUNT(*) as message_count FROM messages
                        WHERE Receiver_ID = ? AND Status = 'unread'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$message_data = $result->fetch_assoc();
$unread_messages = isset($message_data['message_count']) ? $message_data['message_count'] : 0;
$stmt->close();


if (isset($conn) && $conn) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - Home Care BD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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

        .stat-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 15px;
            margin-bottom: 20px;
            border-left: 5px solid var(--primary-color);
        }

        .profile-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: red;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .icon-container {
            position: relative;
            display: inline-block;
            margin-right: 15px;
        }

        .small-icon {
            font-size: 1.2rem;
        }

        .medical-info {
            padding: 10px;
            border-radius: 5px;
            background-color: #f8f9fa;
            margin-bottom: 10px;
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
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link" href="profile.php"> <i class="fas fa-user"></i> My Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_my_jobs.php"> <i class="fas fa-briefcase"></i> My Job Postings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="schedules.php"> <i class="fas fa-calendar-check"></i> My Schedules
                        </a>
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
                        <a class="nav-link" href="submit_feedback.php"> <i class="fas fa-user"></i> Feedback
                        </a>
                    </li>

                    <?php

                    ?>
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
                    <h2>Welcome, <?php echo htmlspecialchars($name); ?></h2>
                    <div class="d-flex">
                        <div class="icon-container">
                            <a href="notifications.php" class="text-dark"> <i class="fas fa-bell small-icon"></i>
                                <?php if ($unread_notifications > 0): ?>
                                <span class="notification-badge"><?php echo htmlspecialchars($unread_notifications); ?></span>
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="icon-container">
                            <a href="messages.php" class="text-dark"> <i class="fas fa-envelope small-icon"></i>
                                <?php if ($unread_messages > 0): ?>
                                <span class="notification-badge"><?php echo htmlspecialchars($unread_messages); ?></span>
                                <?php endif; ?>
                            </a>
                        </div>
                        <a href="submit_feedback_form.php" class="btn btn-primary">Leave Feedback</a> </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Active Jobs</h6>
                                    <h3 class="mb-0"><?php echo htmlspecialchars($active_jobs->num_rows); ?></h3>
                                </div>
                                <div class="bg-light p-3 rounded">
                                    <i class="fas fa-briefcase text-primary fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Scheduled Appointments</h6>
                                    <h3 class="mb-0"><?php echo htmlspecialchars($scheduled_appointments->num_rows); ?></h3>
                                </div>
                                <div class="bg-light p-3 rounded">
                                    <i class="fas fa-calendar-check text-primary fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Job Applications</h6>
                                    <h3 class="mb-0"><?php echo htmlspecialchars($job_applications->num_rows); ?></h3>
                                </div>
                                <div class="bg-light p-3 rounded">
                                    <i class="fas fa-user-check text-primary fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4>Medical Summary</h4>
                        <a href="medical_history.php" class="btn btn-sm btn-outline-primary">View Details</a> </div>

                    <?php if ($medical_history): ?>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="medical-info">
                                <h6>Medical Condition</h6>
                                <p><?php echo htmlspecialchars($medical_history['Condition'] ?? 'Not specified'); ?></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="medical-info">
                                <h6>Medications</h6>
                                <p><?php echo htmlspecialchars($medical_history['Medication'] ?? 'None'); ?></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="medical-info">
                                <h6>Allergies</h6>
                                <p><?php echo htmlspecialchars($medical_history['Allergies'] ?? 'None'); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">No medical information available. Please update your medical history.</div>
                    <div class="text-center">
                        <a href="medical_history.php" class="btn btn-primary">Update Medical History</a> </div>
                    <?php endif; ?>
                </div>

                <div class="dashboard-card mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4>Upcoming Appointments</h4>
                        <a href="schedules.php" class="btn btn-sm btn-outline-primary">View All</a> </div>

                    <?php if ($scheduled_appointments->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Attendant</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($appointment = $scheduled_appointments->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($appointment['AttendantName']); ?></td>
                                            <td><?php echo htmlspecialchars(date('M d, Y h:i A', strtotime($appointment['Start_time']))); ?></td>
                                            <td><?php echo htmlspecialchars(date('M d, Y h:i A', strtotime($appointment['End_time']))); ?></td>
                                            <td>
                                                <?php if ($appointment['Status'] == 'pending'): ?>
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                <?php elseif ($appointment['Status'] == 'confirmed'): ?>
                                                    <span class="badge bg-success">Confirmed</span>
                                                <?php else: ?>
                                                    <span class="badge bg-primary"><?php echo ucfirst(htmlspecialchars($appointment['Status'])); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="view_schedule.php?id=<?php echo htmlspecialchars($appointment['Schedule_ID']); ?>" class="btn btn-sm btn-primary">View</a> </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">You have no upcoming appointments at the moment.</div>
                        <div class="text-center">
                            <a href="job_posting.php" class="btn btn-primary">Find an Attendant</a> </div>
                    <?php endif; ?>
                </div>

                <div class="dashboard-card mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4>Recent Job Applications</h4>
                        <a href="job_applications.php" class="btn btn-sm btn-outline-primary">View All</a> </div>

                    <?php if ($job_applications->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Job Title</th>
                                        <th>Applicant</th>
                                        <th>Applied On</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($application = $job_applications->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($application['Job_title']); ?></td>
                                            <td><?php echo htmlspecialchars($application['AttendantName']); ?></td>
                                            <td><?php echo htmlspecialchars(date('M d, Y', strtotime($application['Application_Date']))); ?></td>
                                            <td>
                                                <?php if ($application['Status'] == 'pending'): ?>
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                <?php elseif ($application['Status'] == 'accepted'): ?>
                                                    <span class="badge bg-success">Accepted</span>
                                                <?php elseif ($application['Status'] == 'rejected'): ?>
                                                    <span class="badge bg-danger">Rejected</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="view_application.php?id=<?php echo htmlspecialchars($application['Application_ID']); ?>" class="btn btn-sm btn-primary">View</a> </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No job applications received yet.</div>
                        <div class="text-center">
                            <a href="job_posting.php" class="btn btn-primary">Post a Job</a> </div>
                    <?php endif; ?>
                </div>

                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4>Active Job Postings</h4>
                        <a href="job_postings.php" class="btn btn-sm btn-outline-primary">View All</a> </div>

                    <?php if ($active_jobs->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Job Title</th>
                                        <th>Location</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($job = $active_jobs->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($job['Job_title']); ?></td>
                                            <td><?php echo htmlspecialchars($job['Location']); ?></td>
                                            <td><?php echo htmlspecialchars(date('M d, Y', strtotime($job['Start_date']))); ?></td>
                                            <td><?php echo htmlspecialchars(date('M d, Y', strtotime($job['End_date']))); ?></td>
                                            <td><span class="badge bg-success">Open</span></td>
                                            <td>
                                                <a href="view_job.php?id=<?php echo htmlspecialchars($job['Job_ID']); ?>" class="btn btn-sm btn-primary">View</a> </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">You have no active job postings at the moment.</div>
                        <div class="text-center">
                            <a href="job_posting.php" class="btn btn-primary">Post a Job</a> </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>

        document.addEventListener('DOMContentLoaded', function() {
            const sidebarLinks = document.querySelectorAll('.sidebar-link');

            const currentPath = window.location.pathname.split('/').pop();
            sidebarLinks.forEach(link => {
                const linkPath = link.getAttribute('href').split('/').pop();
                if (linkPath === currentPath || (currentPath === 'dashboard.php' && linkPath === 'dashboard.php')) {

                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });


        });
    </script>
</body>
</html>