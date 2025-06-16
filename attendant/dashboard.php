<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'attendant') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

require_once '../dbconnect.php';

// Check for database connection errors
if ($conn->connect_error) {
    error_log("Database connection failed in attendant/dashboard.php: " . $conn->connect_error);
    $error_message = "Database connection failed. Please try again later.";
    // Initialize empty results to prevent errors later
    $attendant = null;
    $result_applications = false;
    $result_schedule = false;
    $result_available_jobs = false;
    $result_payments = false;
    $result_notifications = false;
    $unread_messages = 0;
    $result_received_feedback = false;
    $result_emergency_requests = false; // Initialize for emergency requests
} else {
    // Attendant profile query
    $sql_attendant_stmt = $conn->prepare("SELECT a.*, u.Name, u.Email, u.Phone
                                         FROM attendant a
                                         JOIN users u ON a.User_ID = u.User_ID
                                         WHERE a.User_ID = ?");
    $sql_attendant_stmt->bind_param("i", $user_id);
    $sql_attendant_stmt->execute();
    $result_attendant = $sql_attendant_stmt->get_result();
    $attendant = $result_attendant->fetch_assoc();
    $sql_attendant_stmt->close();

    // Job applications query
    $sql_applications_stmt = $conn->prepare("SELECT ja.*, jp.Job_title, jp.Location, jp.Start_date, jp.End_date
                                            FROM job_application ja
                                            JOIN job_posting jp ON ja.Job_ID = jp.Job_ID
                                            WHERE ja.Attendant_ID = ?
                                            ORDER BY ja.Application_Date DESC");
    $sql_applications_stmt->bind_param("i", $user_id);
    $sql_applications_stmt->execute();
    $result_applications = $sql_applications_stmt->get_result();
    $sql_applications_stmt->close();

    // Schedule query (only upcoming confirmed/assigned schedules for dashboard summary)
    $sql_schedule_stmt = $conn->prepare("SELECT s.*, u.Name as PatientName, u.Phone as PatientPhone
                                        FROM schedule s
                                        JOIN users u ON s.Patient_ID = u.User_ID
                                        WHERE s.Attendant_ID = ? AND s.End_time >= NOW() AND (s.Status = 'confirmed' OR s.Status = 'attendant_assigned')
                                        ORDER BY s.Start_time");
    $sql_schedule_stmt->bind_param("i", $user_id);
    $sql_schedule_stmt->execute();
    $result_schedule = $sql_schedule_stmt->get_result();
    $sql_schedule_stmt->close();


    // Available jobs query (jobs not applied for by this attendant)
    $sql_available_jobs_stmt = $conn->prepare("SELECT jp.* FROM job_posting jp
                                              WHERE jp.Status = 'open'
                                              AND jp.Job_ID NOT IN (
                                                  SELECT ja.Job_ID FROM job_application ja
                                                  WHERE ja.Attendant_ID = ?
                                              )
                                              ORDER BY jp.Created_at DESC LIMIT 5"); // Limit to 5 for dashboard
    $sql_available_jobs_stmt->bind_param("i", $user_id);
    $sql_available_jobs_stmt->execute();
    $result_available_jobs = $sql_available_jobs_stmt->get_result();
    $sql_available_jobs_stmt->close();

    // Payments query
    $sql_payments_stmt = $conn->prepare("SELECT p.*, jp.Job_title, u.Name as PayerName
                                        FROM payment p
                                        JOIN job_posting jp ON p.Job_ID = jp.Job_ID
                                        JOIN users u ON p.Payer_ID = u.User_ID
                                        WHERE p.Receiver_ID = ?
                                        ORDER BY p.Date DESC LIMIT 5"); // Limit to 5 for dashboard
    $sql_payments_stmt->bind_param("i", $user_id);
    $sql_payments_stmt->execute();
    $result_payments = $sql_payments_stmt->get_result();
    $sql_payments_stmt->close();

    // Notifications query
    $sql_notifications_stmt = $conn->prepare("SELECT * FROM notifications
                                             WHERE User_ID = ?
                                             ORDER BY Created_at DESC
                                             LIMIT 5");
    $sql_notifications_stmt->bind_param("i", $user_id);
    $sql_notifications_stmt->execute();
    $result_notifications = $sql_notifications_stmt->get_result();
    $sql_notifications_stmt->close();

    // Unread messages count query
    $sql_messages_count_stmt = $conn->prepare("SELECT COUNT(*) as unread FROM messages
                                              WHERE Receiver_ID = ?
                                              AND Status = 'unread'");
    $sql_messages_count_stmt->bind_param("i", $user_id);
    $sql_messages_count_stmt->execute();
    $result_messages_count = $sql_messages_count_stmt->get_result();
    $unread_messages = 0;
    if ($result_messages_count && $row = $result_messages_count->fetch_assoc()) {
        $unread_messages = $row['unread'];
    }
    $sql_messages_count_stmt->close();

    // Feedback query
    $sql_received_feedback_stmt = $conn->prepare("SELECT f.*, u_r.Name as ReviewerName
                                                 FROM feedback f
                                                 JOIN users u_r ON f.Reviewer_ID = u_r.User_ID
                                                 WHERE f.Reviewee_ID = ? ORDER BY f.Created_at DESC LIMIT 5"); // Limit to 5 for dashboard
    $sql_received_feedback_stmt->bind_param("i", $user_id);
    $sql_received_feedback_stmt->execute();
    $result_received_feedback = $sql_received_feedback_stmt->get_result();
    $sql_received_feedback_stmt->close();

    // Emergency requests query (Include pending, unclaimed AND assigned)
    $sql_emergency_requests_stmt = $conn->prepare("SELECT er.*, u.Name as PatientName, u.Phone as PatientPhone
                                                  FROM emergency_request er
                                                  JOIN users u ON er.Patient_ID = u.User_ID
                                                  WHERE er.Status = 'pending' OR (er.Attendant_ID = ? AND er.Status = 'in_progress')
                                                  ORDER BY er.Request_time DESC");
     // Note: Assuming 'in_progress' is the status after claiming, adjust if needed.
    $sql_emergency_requests_stmt->bind_param("i", $user_id);
    $sql_emergency_requests_stmt->execute();
    $result_emergency_requests = $sql_emergency_requests_stmt->get_result();
    $sql_emergency_requests_stmt->close();


    // Update availability
    if (isset($_POST['update_availability'])) {
        $new_status = trim($_POST['availability'] ?? '');

        $update_sql_stmt = $conn->prepare("UPDATE attendant SET Availability = ? WHERE User_ID = ?");
        $update_sql_stmt->bind_param("si", $new_status, $user_id);

        if ($update_sql_stmt->execute()) {
            $success_message = "Availability status updated successfully!";
            $attendant['Availability'] = $new_status;
        } else {
            $error_message = "Error updating availability: " . $update_sql_stmt->error;
        }
        $update_sql_stmt->close();

        header("Location: dashboard.php?msg=" . urlencode($success_message ?? $error_message));
        exit();
    }

    // Close database connection at the end if it was opened successfully
    if (isset($conn) && $conn) {
       // $conn->close(); // Keep connection open until end of script if needed for display
    }
}

// Ensure connection is closed if it was opened
if (isset($conn) && $conn && $conn->ping()) {
    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendant Dashboard - HomeCareBD</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .dashboard-card {
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            padding: 3px 6px;
            border-radius: 50%;
            background-color: #dc3545;
            color: white;
            font-size: 0.7rem;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 3px 8px;
        }
        .profile-section {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .availability-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 5px;
        }
        .available {
            background-color: #28a745;
        }
        .unavailable {
            background-color: #dc3545;
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
                    <li class="nav-item active">
                         <a class="nav-link" href="dashboard.php">Dashboard</a>
                        </li>
                    <li class="nav-item">
                         <a class="nav-link" href="job_details.php">Find Jobs</a>
                        </li>
                    <li class="nav-item">
                         <a class="nav-link" href="schedule.php">My Schedule</a>
                        </li>
                    <li class="nav-item">
                         <a class="nav-link" href="messages.php">
                            Messages
                            <?php if ($unread_messages > 0): ?>
                                <span class="badge badge-light"><?php echo htmlspecialchars($unread_messages); ?></span>
                            <?php endif; ?>
                        </a>
                        </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($attendant['Name'] ?? 'Attendant'); ?>
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
        <?php if (isset($_GET['msg'])):  ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>

        <?php if (isset($error_message) && $error_message):  ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <h2>Attendant Dashboard</h2>

        <div class="profile-section">
            <div class="row">
                <div class="col-md-3 text-center">
                    <div class="mb-3">
                        <img src="assets/img/default-avatar.png" alt="Profile Picture" class="img-fluid rounded-circle" style="width: 150px; height: 150px;">
                        </div>
                </div>
                <div class="col-md-5">
                    <h4><?php echo htmlspecialchars($attendant['Name'] ?? 'Attendant'); ?></h4>
                    <p><strong>Specialization:</strong> <?php echo htmlspecialchars($attendant['Specialization'] ?? 'Not specified'); ?></p>
                    <p><strong>Experience:</strong> <?php echo htmlspecialchars(isset($attendant['Experience']) && $attendant['Experience'] ? $attendant['Experience'] . ' years' : 'Not specified'); ?></p>
                    <p><strong>Rating:</strong>
                        <?php
                        if (isset($attendant['Average_rating']) && $attendant['Average_rating']) {
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $attendant['Average_rating']) {
                                    echo '<i class="fas fa-star text-warning"></i>';
                                } elseif ($i - 0.5 <= $attendant['Average_rating']) {
                                    echo '<i class="fas fa-star-half-alt text-warning"></i>';
                                } else {
                                    echo '<i class="far fa-star text-warning"></i>';
                                }
                            }
                            echo ' (' . htmlspecialchars($attendant['Average_rating']) . ')';
                        } else {
                            echo 'No ratings yet';
                        }
                        ?>
                    </p>
                    <p><strong>Verification Status:</strong>
                        <?php if (($attendant['Verification_status'] ?? '') == 'verified'): ?>
                            <span class="badge badge-success">Verified</span>
                        <?php elseif (($attendant['Verification_status'] ?? '') == 'pending'): ?>
                            <span class="badge badge-warning">Pending Verification</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Rejected</span>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Availability Status</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <span class="availability-indicator <?php echo (($attendant['Availability'] ?? '') == 'available') ? 'available' : 'unavailable'; ?>"></span>
                                <span>Current Status: <strong><?php echo ucfirst(htmlspecialchars($attendant['Availability'] ?? 'Not set')); ?></strong></span>
                            </div>
                            <form method="post" action="">
                                <div class="form-group">
                                    <select name="availability" class="form-control">
                                        <option value="available" <?php echo (($attendant['Availability'] ?? '') == 'available') ? 'selected' : ''; ?>>Available</option>
                                        <option value="unavailable" <?php echo (($attendant['Availability'] ?? '') == 'unavailable') ? 'selected' : ''; ?>>Unavailable</option>
                                    </select>
                                </div>
                                <button type="submit" name="update_availability" class="btn btn-primary btn-sm btn-block">Update Status</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card dashboard-card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-calendar-alt mr-2"></i> Upcoming Schedules</h5>
                    </div>
                    <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                        <?php if ($result_schedule !== false && $result_schedule->num_rows > 0): ?>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Patient</th>
                                        <th>Date & Time</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($schedule = $result_schedule->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($schedule['PatientName'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php
                                                $start = new DateTime($schedule['Start_time'] ?? 'now');
                                                $end = new DateTime($schedule['End_time'] ?? 'now');
                                                echo htmlspecialchars($start->format('M d, Y')) . '<br>';
                                                echo htmlspecialchars($start->format('h:i A') . ' - ' . $end->format('h:i A'));
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                    $status = htmlspecialchars($schedule['Status'] ?? 'unknown');
                                                    $badge_class = 'badge-secondary'; // Default
                                                    switch ($status) {
                                                        case 'confirmed':
                                                            $badge_class = 'badge-success';
                                                            break;
                                                        case 'pending':
                                                            $badge_class = 'badge-warning';
                                                            break;
                                                        case 'attendant_assigned':
                                                             $badge_class = 'badge-info';
                                                             break;
                                                        case 'completed':
                                                            $badge_class = 'badge-secondary';
                                                            break;
                                                        case 'canceled':
                                                            $badge_class = 'badge-danger';
                                                            break;
                                                        default:
                                                             $badge_class = 'badge-secondary';
                                                             $status = 'Unknown';
                                                             break;
                                                    }
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst(str_replace('_', ' ', $status)); ?></span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php elseif ($result_schedule !== false): ?>
                            <p class="text-muted">No upcoming schedules found.</p>
                        <?php else: ?>
                            <p class="text-danger">Error loading schedules.</p>
                        <?php endif; ?>
                         <a href="schedule.php" class="btn btn-sm btn-outline-info mt-2">View Full Schedule</a>
                         </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card dashboard-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-briefcase mr-2"></i> My Job Applications</h5>
                    </div>
                    <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                        <?php if ($result_applications !== false && $result_applications->num_rows > 0): ?>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Job Title</th>
                                        <th>Applied On</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($application = $result_applications->fetch_assoc()): ?>
                                        <tr>
                                             <td><a href="job_details.php?id=<?php echo htmlspecialchars($application['Job_ID'] ?? ''); ?>"><?php echo htmlspecialchars($application['Job_title'] ?? 'N/A'); ?></a></td>
                                             <td><?php echo htmlspecialchars(date('M d, Y', strtotime($application['Application_Date'] ?? 'now'))); ?></td>
                                            <td>
                                                <?php
                                                     $status = htmlspecialchars($application['Status'] ?? 'unknown');
                                                    $badge_class = 'badge-secondary'; // Default
                                                    switch ($status) {
                                                        case 'accepted':
                                                            $badge_class = 'badge-success';
                                                            break;
                                                        case 'pending':
                                                            $badge_class = 'badge-warning';
                                                            break;
                                                        case 'rejected':
                                                            $badge_class = 'badge-danger';
                                                            break;
                                                        default:
                                                             $badge_class = 'badge-secondary';
                                                             $status = 'Unknown';
                                                             break;
                                                    }
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst(str_replace('_', ' ', $status)); ?></span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php elseif ($result_applications !== false): ?>
                            <p class="text-muted">No job applications found.</p>
                        <?php else: ?>
                             <p class="text-danger">Error loading job applications.</p>
                        <?php endif; ?>
                         <a href="my_applications.php" class="btn btn-sm btn-outline-primary mt-2">View All Applications</a>
                         </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card dashboard-card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-clipboard-list mr-2"></i> Available Jobs</h5>
                    </div>
                    <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                        <?php if ($result_available_jobs !== false && $result_available_jobs->num_rows > 0): ?>
                            <div class="list-group">
                                <?php while ($job = $result_available_jobs->fetch_assoc()): ?>
                                     <a href="job_application.php?id=<?php echo htmlspecialchars($job['Job_ID'] ?? ''); ?>" class="list-group-item list-group-item-action">
                                     <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($job['Job_title'] ?? 'N/A'); ?></h6>
                                            <small>Posted: <?php echo htmlspecialchars(date('M d', strtotime($job['Created_at'] ?? 'now'))); ?></small>
                                        </div>
                                        <p class="mb-1 text-truncate"><?php echo htmlspecialchars(substr($job['Job_description'] ?? 'No description', 0, 80)) . '...'; ?></p>
                                        <small>
                                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['Location'] ?? 'N/A'); ?> |
                                            <i class="fas fa-calendar"></i> <?php echo htmlspecialchars(date('M d', strtotime($job['Start_date'] ?? 'now'))); ?> - <?php echo htmlspecialchars(date('M d', strtotime($job['End_date'] ?? 'now'))); ?>
                                        </small>
                                    </a>
                                <?php endwhile; ?>
                            </div>
                        <?php elseif ($result_available_jobs !== false): ?>
                            <p class="text-muted">No available jobs found.</p>
                        <?php else: ?>
                            <p class="text-danger">Error loading available jobs.</p>
                        <?php endif; ?>
                         <a href="job_details.php" class="btn btn-sm btn-outline-success mt-2">Find More Jobs</a>
                         </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card dashboard-card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-money-bill-wave mr-2"></i> Payment History</h5>
                    </div>
                    <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                        <?php if ($result_payments !== false && $result_payments->num_rows > 0): ?>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Job</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Method</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($payment = $result_payments->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($payment['Job_title'] ?? 'N/A'); ?></td>
                                            <td>৳<?php echo htmlspecialchars(number_format($payment['Amount'] ?? 0, 2)); ?></td>
                                            <td><?php echo htmlspecialchars(date('M d, Y', strtotime($payment['Date'] ?? 'now'))); ?></td>
                                            <td><?php echo ucfirst(htmlspecialchars($payment['Payment_method'] ?? 'N/A')); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php elseif ($result_payments !== false): ?>
                            <p class="text-muted">No payment history found.</p>
                        <?php else: ?>
                             <p class="text-danger">Error loading payment history.</p>
                        <?php endif; ?>
                         <a href="payment_history.php" class="btn btn-sm btn-outline-secondary mt-2">View All Payments</a>
                         </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card dashboard-card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fas fa-exclamation-triangle mr-2"></i> Emergency Requests</h5>
                    </div>
                    <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                         <?php if ($result_emergency_requests !== false && $result_emergency_requests->num_rows > 0): ?>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Patient</th>
                                        <th>Location</th>
                                        <th>Request Time</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($request = $result_emergency_requests->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($request['PatientName'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($request['Location'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php
                                                $request_time = new DateTime($request['Request_time'] ?? 'now');
                                                echo htmlspecialchars($request_time->format('M d, Y h:i A'));
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                    $status = htmlspecialchars($request['Status'] ?? 'unknown');
                                                    $badge_class = 'badge-secondary'; // Default
                                                    switch ($status) {
                                                        case 'pending':
                                                            $badge_class = 'badge-warning';
                                                            break;
                                                        case 'in_progress':
                                                             $badge_class = 'badge-info';
                                                             break;
                                                        case 'completed':
                                                            $badge_class = 'badge-success';
                                                            break;
                                                        case 'canceled':
                                                            $badge_class = 'badge-danger';
                                                            break;
                                                        default:
                                                             $badge_class = 'badge-secondary';
                                                             $status = 'Unknown';
                                                             break;
                                                    }
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst(str_replace('_', ' ', $status)); ?></span>
                                            </td>
                                            <td>
                                                <?php if (($request['Status'] ?? '') == 'pending'): ?>
                                                    <form action="claim_emergency.php" method="post" style="display: inline;">
                                                        <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request['Request_ID'] ?? ''); ?>">
                                                        <button type="submit" name="claim" class="btn btn-sm btn-success" onclick="return confirm('Claim this emergency request?');">Claim</button>
                                                    </form>
                                                 <?php elseif (($request['Status'] ?? '') == 'in_progress' && ($request['Attendant_ID'] ?? '') == $user_id): // Show complete only if claimed by THIS attendant ?>
                                                      <form action="update_emergency_status.php" method="post" style="display: inline;">
                                                            <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request['Request_ID'] ?? ''); ?>">
                                                            <button type="submit" name="complete" class="btn btn-sm btn-primary" onclick="return confirm('Mark this emergency request as completed?');">Complete</button>
                                                        </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                         <?php elseif ($result_emergency_requests !== false): ?>
                            <p class="text-muted">No emergency requests found.</p>
                        <?php else: ?>
                            <p class="text-danger">Error loading emergency requests.</p>
                        <?php endif; ?>
                        <a href="emergency_requests.php" class="btn btn-sm btn-outline-danger mt-2">View All Emergency Requests</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card dashboard-card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-comments mr-2"></i> Feedback Received</h5>
                    </div>
                    <div class="card-body">
                         <?php if ($result_received_feedback !== false && $result_received_feedback->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Rating</th>
                                            <th>Comment</th>
                                            <th>From</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $result_received_feedback->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['Rating'] ?? 'N/A'); ?>/5</td>
                                                <td><?php echo nl2br(htmlspecialchars($row['Comment'] ?? 'No comment')); ?></td>
                                                <td><?php echo htmlspecialchars($row['ReviewerName'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($row['Created_at'] ?? 'now'))); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                         <?php elseif ($result_received_feedback !== false): ?>
                            <p class="text-muted">No feedback received yet.</p>
                         <?php else: ?>
                            <p class="text-danger">Error loading feedback.</p>
                         <?php endif; ?>
                         <a href="received_feedback.php" class="btn btn-sm btn-outline-warning mt-2">View All Feedback</a>
                         </div>
                </div>
            </div>
        </div>

        <div class="card dashboard-card mt-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-bell mr-2"></i> Recent Notifications</h5>
            </div>
            <div class="card-body">
                <?php if ($result_notifications !== false && $result_notifications->num_rows > 0): ?>
                    <div class="list-group">
                        <?php while ($notification = $result_notifications->fetch_assoc()): ?>
                            <div class="list-group-item list-group-item-action <?php echo (($notification['Status'] ?? '') == 'unread') ? 'list-group-item-light' : ''; ?>">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        <?php if (($notification['Type'] ?? '') == 'booking'): ?>
                                            <i class="fas fa-calendar-check text-primary mr-1"></i>
                                        <?php elseif (($notification['Type'] ?? '') == 'emergency'): ?>
                                            <i class="fas fa-exclamation-triangle text-danger mr-1"></i>
                                        <?php elseif (($notification['Type'] ?? '') == 'payment'): ?>
                                            <i class="fas fa-money-bill text-success mr-1"></i>
                                        <?php else: ?>
                                            <i class="fas fa-info-circle text-info mr-1"></i>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($notification['Message'] ?? 'No message content'); ?>
                                    </h6>
                                    <small><?php echo htmlspecialchars(date('M d, h:i A', strtotime($notification['Created_at'] ?? 'now'))); ?></small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php elseif ($result_notifications !== false): ?>
                    <p class="text-muted">No notifications found.</p>
                <?php else: ?>
                     <p class="text-danger">Error loading notifications.</p>
                <?php endif; ?>
                 <a href="notifications.php" class="btn btn-sm btn-outline-danger mt-2">View All Notifications</a>
                 </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <p class="mb-0">© <?php echo date('Y'); ?> HomeCareBD. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>