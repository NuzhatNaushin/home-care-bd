<?php
// attendant/emergency_requests.php - Lists emergency requests for attendants

ini_set('display_errors', 1); // Temporarily show errors for debugging
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Check if user is logged in and is an attendant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'attendant') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'] ?? 'Attendant';

require_once '../dbconnect.php';

// Verify database connection
if (!$conn || $conn->connect_error) {
    error_log("Database connection failed in attendant/emergency_requests.php: " . $conn->connect_error);
    die("Database connection failed. Please try again later."); // For development
}

$error_message = '';
$success_message = $_GET['success'] ?? ''; // Get success message from URL
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

$error_message = $_GET['error'] ?? ''; // Get error message from URL
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}


// Fetch emergency requests relevant to the attendant:
// 1. Pending requests that are not yet claimed (Attendant_ID IS NULL, Status = 'pending')
// 2. Requests claimed by the current attendant (Attendant_ID = user_id, Status != 'completed' and != 'canceled')
$sql_emergency_requests_stmt = $conn->prepare("SELECT er.*, u.Name as PatientName, u.Phone as PatientPhone
                                              FROM emergency_request er
                                              JOIN users u ON er.Patient_ID = u.User_ID
                                              WHERE (er.Attendant_ID IS NULL AND er.Status = 'pending')
                                                 OR (er.Attendant_ID = ? AND er.Status != 'completed' AND er.Status != 'canceled')
                                              ORDER BY er.Request_time DESC");

if ($sql_emergency_requests_stmt) {
    $sql_emergency_requests_stmt->bind_param("i", $user_id);
    $sql_emergency_requests_stmt->execute();
    $result_emergency_requests = $sql_emergency_requests_stmt->get_result();
    $sql_emergency_requests_stmt->close();
} else {
    $error_message .= "Database error fetching emergency requests: " . $conn->error;
    $result_emergency_requests = false; // Indicate query failed
}

// Fetch unread messages count for navbar (Assuming messages table exists)
$unread_messages = 0; // Default to 0 if no messages feature or query fails
$sql_messages_count_stmt = $conn->prepare("SELECT COUNT(*) as unread FROM messages
                                          WHERE Receiver_ID = ? AND Status = 'unread'");
if ($sql_messages_count_stmt) {
    $sql_messages_count_stmt->bind_param("i", $user_id);
    $sql_messages_count_stmt->execute();
    $result_messages_count = $sql_messages_count_stmt->get_result();
    if ($result_messages_count && $row = $result_messages_count->fetch_assoc()) {
        $unread_messages = $row['unread'];
    }
    $sql_messages_count_stmt->close();
}


// Close database connection at the end of the script
if (isset($conn) && $conn) {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Requests - Attendant - HomeCareBD</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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

        .badge {
            padding: 0.5em 0.75em;
            font-size: 0.85em;
            font-weight: bold;
        }

         .badge-success { background-color: #28a745; color: white; }
         .badge-warning { background-color: #ffc107; color: #212529; }
         .badge-danger { background-color: #dc3545; color: white; }
         .badge-info { background-color: #17a2b8; color: white; }
         .badge-primary { background-color: #007bff; color: white; }
         .badge-secondary { background-color: #6c757d; color: white; }

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
                    <p class="text-light mb-0">Attendant</p>
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
                        <a class="nav-link" href="job_details.php"> <i class="fas fa-search"></i> Available Jobs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="schedule.php"> <i class="fas fa-calendar-check"></i> My Schedule
                        </a>
                    </li>
                     <li class="nav-item">
                         <a class="nav-link" href="messages.php">
                             <i class="fas fa-envelope"></i> Messages
                              <?php if ($unread_messages > 0): ?>
                                 <span class="badge badge-danger"><?php echo $unread_messages; ?></span>
                             <?php endif; ?>
                         </a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link" href="received_feedback.php"> <i class="fas fa-comments"></i> Received Feedback
                        </a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link" href="payment_history.php"> <i class="fas fa-money-bill"></i> Payment History
                        </a>
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
                    <h2>Emergency Requests</h2>
                    </div>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><?php echo nl2br(htmlspecialchars($error_message)); ?></div>
                <?php endif; ?>
                 <?php if ($success_message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>


                <div class="dashboard-card">
                    <?php if ($result_emergency_requests !== false && $result_emergency_requests->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Patient</th>
                                        <th>Location</th>
                                        <th>Request Time</th>
                                        <th>Description</th>
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
                                             <td><?php echo nl2br(htmlspecialchars($request['Description'] ?? 'N/A')); ?></td>
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
                                                     <a href="update_emergency_status.php?id=<?php echo htmlspecialchars($request['Request_ID'] ?? ''); ?>&action=complete" class="btn btn-sm btn-primary">Mark as Complete</a>
                                                <?php elseif (($request['Status'] ?? '') != 'completed' && ($request['Status'] ?? '') != 'canceled' && ($request['Attendant_ID'] ?? '') == $user_id): // Option to cancel if claimed by THIS attendant and not completed/canceled ?>
                                                      <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php elseif ($result_emergency_requests !== false): ?>
                        <div class="alert alert-info">No emergency requests found that are pending or assigned to you.</div>
                    <?php else: ?>
                        <div class="alert alert-danger">Error loading emergency requests.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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

            // Highlight Emergency Requests link if on this page
            const emergencyLink = document.querySelector('.sidebar .nav-link[href="emergency_requests.php"]');
            if (emergencyLink && currentPath === 'emergency_requests.php') {
                 emergencyLink.classList.add('active');
            }
        });
    </script>
</body>
</html>